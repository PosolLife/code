<?php

namespace Api\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;

class ApiModel extends \Administrator\Model\BasicModel
{
    public $role_id;
    public $role_name;
    public $inactive;

    protected $testToken = 'eyJhbGciOiJIUzI1NiIsInR3cCI6IkpXVCJ9.eyJ1aWQiOiIxIiwiZGlkIjoiMjAiLCJyb2wiOiJjdXN0X2Jhc2ljIiwidXNyIjoidGVzdDFAbG9vbWFuYWx5dGljcy5jb20iLCJhY3QiOiIxIiwiZXhwIjoxNDkzMjA1Njg1LCJpYXQiOjE0OTE5MDk2ODV9.puTFpTYXfwwx6-x27bpsRNKZyQdj1Efz-7C_DUpbruk';
    protected $source = '2chair';
    protected $version = '1';
    protected $responseId = '01';
    protected $queries = array();

    protected $dateRange = array(
        'court' => false,
        'prArea' => false,
        'min_date' => '',
        'max_date' => '',
    );

    public function __construct($dbAdapter)
    {
        parent::__construct($dbAdapter);
    }

    public function getLifeTimeOfTheToken($customerId)
    {
        $sql = "
            SELECT si.period_start, si.period_end
            FROM stripe_customer sc
            LEFT JOIN stripe_invoice si 
              ON sc.stripe_customer_id = si.stripe_customer_id
              AND si.inactive = 0 
            WHERE sc.customer_id = " . (int)$customerId . " 
              AND si.period_end > NOW()              
              AND sc.inactive = 0
            ORDER BY si.stripe_invoice_id DESC 
            LIMIT 1
        ";

        return $this->runQuery($sql, true);
    }

    public function getRightOfAccessToReport($data, $customer)
    {

        $sql = "
           SELECT rc.*
            FROM app_client ac
            LEFT JOIN report_centre rc 
              ON rc.app_client_id = ac.app_client_id
              AND rc.inactive = 0
            LEFT JOIN plan_to_centre ptc 
              ON ptc.report_centre_id = rc.report_centre_id
              AND ptc.inactive = 0 
            LEFT JOIN stripe_plan sp 
              ON sp.stripe_plan_id = ptc.stripe_plan_id
            LEFT JOIN stripe_invoice si
              ON si.plan_id = sp.stripe_plan_id
              AND si.inactive = 0 
	          LEFT JOIN stripe_customer sc
	            ON sc.stripe_customer_id = si.stripe_customer_id
	            AND sc.inactive = 0 
            WHERE ac.app_client_name = '" . $data . "' 
                AND ac.inactive = 0 
            LIMIT 1
        ";
        // AND stripe_customer.customer_id = '".$customer."'
        return $this->runQuery($sql, true);
    }

    public function getReport($data)
    {

        $sql = "
           SELECT *
            FROM app_client ac
            LEFT JOIN app_client_to_centre actc 
              ON ac.app_client_id = actc.app_client_id
              AND actc.inactive = 0 
            LEFT JOIN report_centre rc 
              ON rc.report_centre_id = actc.report_centre_id
              AND rc.inactive = 0 
            WHERE
            '" . $data . "'
            AND ac.inactive = 0 
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();

        return $result->getResource()->fetch();
    }

    // Get all active customer tokens
    public function getCustomerByToken($token)
    {
        $query = '
            SELECT c.*
            FROM device_token dt
            LEFT JOIN cust_device cd
              ON cd.cust_device_id = dt.cust_device_id
            LEFT JOIN customers c
              ON c.customer_id = cd.customer_id
              AND c.del_account = 0
            WHERE dt.token = "' . $token . '"
                AND dt.inactive = 0
        ';

        return $this->runQuery($query, true);
    }

    public function getSettings()
    {
        $query = '
            SELECT CONCAT(s.state_name, "-", ct.court_acr) AS province, ct.court_name, 
              ct.court_id, pa.practice_area_name, pa.practice_area_id, dr.min_date, 
              dr.max_date, dr.start_date, dr.end_date, dr.coverage_notes
            FROM app_client ac
            LEFT JOIN report_centre rc 
              ON rc.app_client_id = ac.app_client_id
              AND rc.inactive = 0
            LEFT JOIN report_centre_date_range dr 
              ON dr.report_centre_id = rc.report_centre_id
              AND dr.inactive = 0 
            LEFT JOIN court_type ct 
              ON ct.court_id = dr.court_id
              AND ct.inactive = 0
            LEFT JOIN state s 
              ON s.state_id = ct.state_id
              AND s.inactive = 0
            LEFT JOIN practice_area pa 
              ON pa.practice_area_id = dr.practice_area_id
              AND pa.inactive = 0
            WHERE ac.app_client_name = "2chair"             
              AND ac.inactive = 0
        ';

        $rows = $this->runQuery($query);

        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'province' => $row['province'],
                'court' => $row['court_name'],
                'court_id' => $row['court_id'],
                'practice' => $row['practice_area_name'],
                'practice_id' => $row['practice_area_id'],
                'min_date' => $row['min_date'],
                'max_date' => $row['max_date'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'coverage' => $row['coverage_notes'],
            );
        }

        return array(
            'result' => $result,
            'query' => $result,
        );
    }

    public function getJudSearch($data)
    {
        $and = $this->getApiDateRange($data);
        $and .= $this->getApiPracticeArea($data);

        if ($data->search && is_string($data->search)) {
            $and .= ' AND (cjm.cj_ln LIKE "' . (string)$data->search;
            $and .= '%" OR cjm.cj_fn LIKE "' . (string)$data->search;
            $and .= '%" OR cjm.cj_mn LIKE "' . (string)$data->search . '%")';
        }

        if ($data->court_id && (int)$data->court_id > 0) {
            $and .= ' AND dm.court_type_id IN (' . (int)$data->court_id . ') ';
        }

        $query = "
            SELECT DISTINCT
              CONCAT (
                COALESCE(cjt.cj_type_acr, ''), ' ',
                COALESCE(cjm.cj_fn, ''), ' ',
                COALESCE(cjm.cj_mn, ''), ' ',
                COALESCE(cjm.cj_ln, ''),                 
                ' (', COALESCE(ct.court_acr, ''), ')'
              ) AS judge, cjm.cj_id, ct.court_id
            FROM app_client ac
            LEFT JOIN report_centre rc
              ON rc.app_client_id = ac.app_client_id
              AND rc.inactive = 0
            LEFT JOIN report_centre_date_range dr
              ON dr.report_centre_id = rc.report_centre_id
              AND dr.inactive = 0
            LEFT JOIN court_type ct
              ON dr.court_id = ct.court_id
              AND ct.inactive = 0
            INNER JOIN dec_main dm 
              ON dm.court_type_id = ct.court_id 
              AND dm.inactive = 0
            INNER JOIN proc_in_dec pid
              ON pid.dec_id = dm.dec_id
              AND pid.inactive = 0             
            INNER JOIN proceeding_main AS pm 
              ON pid.proc_id = pm.proc_id 
              AND pm.inactive = 0
            INNER JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id
              AND pmp.practice_area_id = dr.practice_area_id
              AND pmp.inactive = 0              
            INNER JOIN dec_pres_auth dpa
              ON dpa.dec_id = dm.dec_id
              AND dpa.inactive = 0
            INNER JOIN cj_history cjh
              ON cjh.cj_history_id = dpa.cj_history_id
              AND cjh.inactive = 0
            INNER JOIN cj_main cjm
              ON cjm.cj_id = cjh.cj_id
              AND cjm.inactive = 0
            INNER JOIN cj_type cjt
              ON cjt.cj_type_id = cjh.cj_type_id
              AND cjt.inactive = 0
            WHERE ac.app_client_name = '2chair' 
              AND ac.inactive = 0
            " . $and . "
            ORDER BY cjm.cj_fn, cjm.cj_ln ASC
        ";

        $rows = $this->runQuery($query);

        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'id' => $row['cj_id'],
                'court' => $row['court_id'],
                'name' => $row['judge'],
            );
        }

        return $result;
    }

    public function getHearSubSearch($data)
    {
        $and = '';

        if ((int)$data->court_id > 0) {
            $and .= ' AND ht.court_id = ' . (int)$data->court_id;
        }
        /*if (!is_null($data->practice_id) && (int)$data->practice_id > 0) {
            $and .= ' AND pmp.practice_area_id = ' . (int)$data->practice_id;
        }*/
        if ((int)$data->hearing_type > 0) {
            $and .= ' AND hs.hearing_type_id = ' . (int)$data->hearing_type;
        }
        if ($data->search && is_string($data->search)) {
            $and .= ' AND hs.hearing_sub_name LIKE "%' . (string)$data->search . '%" ';
            $and .= ' AND ht.hearing_type_pop_search = 1';
        }

        $query = "
            SELECT hs.hearing_sub_name, hs.hearing_sub_id, hs.hearing_type_id
            FROM hearing_sub hs
            INNER JOIN hearing_type ht 
              ON ht.hearing_type_id = hs.hearing_type_id 
              AND ht.inactive = 0
            WHERE hs.inactive = 0
            " . $and . "
            GROUP BY hs.hearing_sub_id
            LIMIT 21
        ";

        $rows = $this->runQuery($query);

        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'id' => $row['hearing_sub_id'],
                'type' => $row['hearing_type_id'],
                'name' => $row['hearing_sub_name'],
            );
        }

        return $result;
    }

    public function getPopHearTypeList($data)
    {
        $and = '';

        if ((int)$data->court_id > 0) {
            $and .= ' AND ht.court_id = ' . (int)$data->court_id;
        }

        $and .= $this->getApiPracticeArea($data);

        $query = "
            SELECT ht.hearing_name, ht.hearing_type_id, 
              ht.hearing_type_pop, ht.hearing_type_pop_search
            FROM hearing_type ht
            LEFT JOIN dec_hearing dh
              ON dh.hearing_type = ht.hearing_type_id
              AND dh.inactive = 0            
            LEFT JOIN dec_main dm
              ON dm.dec_id = dh.dec_id
              AND dm.inactive = 0
            LEFT JOIN proc_in_dec pid
              ON pid.dec_id = dm.dec_id
              AND pid.inactive = 0             
            LEFT JOIN proceeding_main AS pm 
              ON pid.proc_id = pm.proc_id 
              AND pm.inactive = 0
            LEFT JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id
              AND pmp.inactive = 0
            WHERE ht.hearing_type_id != 1 
              AND ht.hearing_type_pop = 1   
              AND ht.inactive = 0 
              " . $and . "
            GROUP BY ht.hearing_type_id
            ORDER BY ht.hearing_name
        ";

        $rows = $this->runQuery($query);

        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'id' => $row['hearing_type_id'],
                'name' => $row['hearing_name'],
                'pop_search' => $row['hearing_type_pop_search'],
            );
        }

        return $result;
    }

    public function getCaseTypeList($data)
    {
        $and = '';

        $prArea = (int)$data->practice_id;
        if ($prArea > 0) {
            $and .= ' AND practice_area_id = "' . $data->practice_id . '" ';
        }

        $query = "
            SELECT proc_type_name, proc_type_id
            FROM proceeding_type
            WHERE inactive = 0 
              AND proc_type_id != 1 
              " . $and . "
            ORDER BY proc_type_name
        ";

        $rows = $this->runQuery($query);

        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'id' => $row['proc_type_id'],
                'name' => $row['proc_type_name'],
            );
        }

        return $result;
    }

    public function getAverage($data)
    {
        $conditions = $this->getReportConditions($data);

        $query = "
          SELECT COUNT(A.decid) AS decision_count,
            ROUND(AVG(DATEDIFF(decdate, heardate)), 2) AS average
          FROM (
            SELECT dm.dec_id AS decid, dm.dec_date AS decdate,
              MAX(dhd.hearing_date_end) AS heardate
            FROM dec_hearing_date dhd
            INNER JOIN dec_main AS dm
              ON dm.dec_id = dhd.dec_id
              AND dm.inactive = 0
            LEFT JOIN dec_report_ready AS drr
              ON drr.dec_id = dm.dec_id
              AND drr.inactive = 0
            INNER JOIN dec_pres_auth AS dpa
              ON dpa.dec_id = dm.dec_id
              AND dpa.inactive = 0
            INNER JOIN cj_history AS cjh
              ON cjh.cj_history_id = dpa.cj_history_id
              AND cjh.inactive = 0
            INNER JOIN cj_main AS cjm
              ON cjm.cj_id = cjh.cj_id
              AND cjm.inactive = 0
            LEFT JOIN proc_in_dec AS pid
              ON pid.dec_id = dm.dec_id
              AND pid.inactive = 0
            LEFT JOIN proceeding_main AS pm
              ON pm.proc_id = pid.proc_id
              AND pm.inactive = 0
            LEFT JOIN proc_main_proc_type AS pmpt 
              ON pmpt.proc_id = pm.proc_id 
              AND pmpt.inactive = 0
            LEFT JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id
              AND pmp.inactive = 0
            LEFT JOIN proceeding_type AS prt
              ON prt.proc_type_id = pm.proc_type_id
              AND prt.inactive = 0              
            LEFT JOIN dec_hearing AS dh
              ON dh.dec_id = dm.dec_id
              AND dh.inactive = 0
            LEFT JOIN procs_in_dec_hearing AS pidh
              ON pidh.dec_hearing_id = dh.dec_hearing_id
              AND pidh.inactive = 0 
            WHERE drr.report_ready = 1
                AND dhd.inactive = 0
                AND drr.report_subtype_id = 2
                " . $conditions . "
            GROUP BY dm.dec_id
          ) AS A 
        ";

        return $this->runQuery($query, true);
    }

    public function getDecTatList($data)
    {
        $conditions = $this->getReportConditions($data);

        $query = "
            SELECT dm.dec_id AS decid,
              DATEDIFF(dm.dec_date, MAX(dhd.hearing_date_end)) AS dectat
            FROM dec_hearing_date dhd 
            INNER JOIN dec_main AS dm 
              ON dm.dec_id = dhd.dec_id 
              AND dm.inactive = 0 
            INNER JOIN dec_pres_auth AS dpa 
              ON dpa.dec_id = dm.dec_id 
              AND dpa.inactive = 0 
            INNER JOIN cj_history AS cjh 
              ON cjh.cj_history_id = dpa.cj_history_id 
              AND cjh.inactive = 0 
            INNER JOIN cj_main AS cjm 
              ON cjm.cj_id = cjh.cj_id 
              AND cjm.inactive = 0
            LEFT JOIN dec_report_ready AS drr 
              ON drr.dec_id = dm.dec_id 
              AND drr.inactive = 0 
            LEFT JOIN proc_in_dec AS pid 
              ON pid.dec_id = dm.dec_id 
              AND pid.inactive = 0 
            LEFT JOIN proceeding_main AS pm 
              ON pm.proc_id = pid.proc_id 
              AND pm.inactive = 0              
            LEFT JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id
              AND pmp.inactive = 0
            LEFT JOIN proc_main_proc_type AS pmpt 
              ON pmpt.proc_id = pm.proc_id 
              AND pmpt.inactive = 0
            LEFT JOIN dec_hearing AS dh 
              ON dh.dec_id = dm.dec_id 
              AND dh.inactive = 0
            WHERE drr.report_ready = 1
                AND dhd.inactive = 0
                AND drr.report_subtype_id = 2
                " . $conditions . "
            GROUP BY dm.dec_id 
            ORDER BY dectat ASC
        ";

        return $this->runQuery($query);
    }

    public function decCntListRep($data)
    {
        $and = $this->getApiDateRange($data);

        if ((int)$data->court_id > 0) {
            $and .= ' AND ct.court_id = ' . (int)$data->court_id;
        }

        $and .= $this->getApiPracticeArea($data);

        if ((int)$data->hearing_type > 0) {
            $and .= ' AND dh.hearing_type = ' . (int)$data->hearing_type;
        }
        if ((int)$data->hearing_subtype > 0) {
            $and .= ' AND dh.hearing_subtype = ' . (int)$data->hearing_subtype;
        }
        if ((int)$data->judge_id > 0) {
            $and .= ' AND cjm.cj_id = ' . (int)$data->judge_id;
        }
        if ((int)$data->proc_type > 0) {
            $and .= ' AND pmpt.proc_type_id = ' . (int)$data->proc_type;
        }

        $query = "
            SELECT dm.decname, dm.offline_document_name
            FROM court_type ct
            INNER JOIN dec_main dm
              ON dm.court_type_id = ct.court_id
              AND dm.inactive = 0
            INNER JOIN dec_pres_auth dpa
              ON dpa.dec_id = dm.dec_id
              AND dpa.inactive = 0
            INNER JOIN cj_history cjh
              ON cjh.cj_history_id = dpa.cj_history_id
              AND cjh.inactive = 0
            INNER JOIN cj_main cjm
              ON cjm.cj_id = cjh.cj_id
              AND cjm.inactive = 0
            INNER JOIN cj_type cjt
              ON cjt.cj_type_id = cjh.cj_type_id
              AND cjt.inactive = 0
            INNER JOIN dec_hearing dh
              ON dh.dec_id = dm.dec_id
              AND dh.inactive = 0
            INNER JOIN proc_in_dec pid
              ON pid.dec_id = dm.dec_id
              AND pid.inactive = 0
            INNER JOIN proceeding_main pm
              ON pm.proc_id = pid.proc_id
              AND pm.inactive = 0
            INNER JOIN proc_main_proc_type AS pmpt 
              ON pmpt.proc_id = pm.proc_id 
              AND pmpt.inactive = 0
            INNER JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id
              AND pmp.inactive = 0
            WHERE ct.inactive = 0
            " . $and . "
            ORDER BY dm.dec_date DESC, dm.decname ASC, dm.citation_no ASC
            LIMIT 20
        ";

        $rows = $this->runQuery($query);

        return $this->processDecisions($rows);
    }

    public function decOutListRep($data)
    {
        $and = $this->getApiDateRange($data);

        if ((int)$data->court_id > 0) {
            $and .= ' AND dm.court_type_id = ' . (int)$data->court_id;
        } else {
            $and .= ' -- court_id was not passed';
        }

        $and .= $this->getApiPracticeArea($data);

        if ((int)$data->hearing_type > 0) {
            $and .= ' AND dh.hearing_type = ' . (int)$data->hearing_type;
        } else {
            $and .= ' -- hearing_type was not passed';
        }

        if ((int)$data->hearing_subtype > 0) {
            $and .= ' AND dh.hearing_subtype = ' . (int)$data->hearing_subtype;
        } else {
            $and .= ' -- hearing_subtype was not passed';
        }

        if ((int)$data->judge_id > 0) {
            $and .= ' AND cjm.cj_id = ' . (int)$data->judge_id;
        } else {
            $and .= ' -- judge_id was not passed';
        }

        if ((int)$data->proc_type > 0) {
            $and .= ' AND pmpt.proc_type_id = ' . (int)$data->proc_type;
        } else {
            $and .= ' -- proc_type was not passed';
        }

        if (!is_null($data->init_res_party)) {
            $and .= ' AND dhp.init_res_party = ' . (int)$data->init_res_party;
        } else {
            $and .= ' AND dhp.init_res_party = 1 -- init_res_party was not passed (used default value)';
        }

        return array(
            'win' => $this->processDecisions(
                $this->runQuery(
                    $this->getDecOutcomeQuery($and, 'Win')
                )
            ),
            'loss' => $this->processDecisions(
                $this->runQuery(
                    $this->getDecOutcomeQuery($and, 'Loss')
                )
            ),
            'other' => $this->processDecisions(
                $this->runQuery(
                    $this->getDecOutcomeQuery($and)
                )
            ),
        );
    }

    public function getDecisionList($data)
    {
        $conditions = $this->getReportConditions($data);

        $query = "
            SELECT dm.offline_document_name, dm.decname
            FROM dec_main AS dm 
            LEFT JOIN dec_report_ready AS drr 
              ON drr.dec_id = dm.dec_id 
              AND drr.inactive = 0 
            LEFT JOIN proc_in_dec AS pid 
              ON pid.dec_id = dm.dec_id 
              AND pid.inactive = 0 
            LEFT JOIN proceeding_main AS pm 
              ON pm.proc_id = pid.proc_id 
              AND pm.inactive = 0
            LEFT JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id
              AND pmp.inactive = 0
            LEFT JOIN proc_main_proc_type AS pmpt 
              ON pmpt.proc_id = pm.proc_id 
              AND pmpt.inactive = 0
            LEFT JOIN dec_hearing AS dh 
              ON dh.dec_id = dm.dec_id 
              AND dh.inactive = 0 
            LEFT JOIN dec_pres_auth AS dpa 
              ON dpa.dec_id = dm.dec_id 
              AND dpa.inactive = 0 
            LEFT JOIN cj_history AS cjh 
              ON cjh.cj_history_id = dpa.cj_history_id 
              AND cjh.inactive = 0 
            LEFT JOIN cj_main AS cjm 
              ON cjm.cj_id = cjh.cj_id 
              AND cjm.inactive = 0               
            WHERE dm.inactive = 0 
              AND drr.report_ready = 1
              AND drr.report_subtype_id = 2
              " . $conditions . "
            GROUP BY dm.dec_id 
            ORDER BY dm.dec_date DESC, dm.decname ASC, dm.citation_no ASC 
            LIMIT 0, 20
        ";

        return $this->runQuery($query);
    }

    public function getDecTotal($data)
    {
        $conditions = $this->getReportConditions($data);

        $query = "
            SELECT COUNT(DISTINCT(dm.dec_id)) AS total_count
            FROM dec_main AS dm 
            LEFT JOIN dec_report_ready AS drr 
              ON drr.dec_id = dm.dec_id 
              AND drr.inactive = 0 
            LEFT JOIN proc_in_dec AS pid 
              ON pid.dec_id = dm.dec_id 
              AND pid.inactive = 0 
            LEFT JOIN proceeding_main AS pm 
              ON pm.proc_id = pid.proc_id 
              AND pm.inactive = 0
            LEFT JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id
              AND pmp.inactive = 0
            LEFT JOIN proc_main_proc_type AS pmpt 
              ON pmpt.proc_id = pm.proc_id 
              AND pmpt.inactive = 0
            LEFT JOIN dec_hearing AS dh 
              ON dh.dec_id = dm.dec_id 
              AND dh.inactive = 0 
            LEFT JOIN dec_pres_auth AS dpa 
              ON dpa.dec_id = dm.dec_id 
              AND dpa.inactive = 0 
            LEFT JOIN cj_history AS cjh 
              ON cjh.cj_history_id = dpa.cj_history_id 
              AND cjh.inactive = 0 
            LEFT JOIN cj_main AS cjm 
              ON cjm.cj_id = cjh.cj_id 
              AND cjm.inactive = 0              
            WHERE drr.report_ready = 1
              AND drr.report_subtype_id = 2
              AND dm.inactive = 0
              " . $conditions . "
        ";

        $result = $this->runQuery($query, true);

        return $result['total_count'];
    }

    public function getHearingOutcome($data, $party)
    {
        $conditions = $this->getReportConditions($data);

        $query = "
            SELECT C.outcome, C.outcomeid, SUM(wtdoutcome) AS weighteddoutcome, 
              COUNT(DISTINCT(C.hearid)) AS numberHearing, 
              COUNT(DISTINCT(C.decid)) AS numberDecisions 
            FROM 
              (
                SELECT A.decid AS decid, A.hearid, A.outcomeid, 
                  A.outcome, A.partyoutcount / B.partytypecount AS wtdoutcome 
                FROM (
                (
                  SELECT dm.dec_id AS decid, dh.dec_hearing_id AS hearid, 
                    o.outcome_name AS outcome, dhp.outcome_id AS outcomeid, 
                    ppt.party_type_id AS ptid, o.outcome_id AS outcid, 
                    COUNT(dhp.outcome_id) AS partyoutcount 
                  FROM proc_in_dec pid 
                    INNER JOIN proceeding_main AS pm 
                      ON pm.proc_id = pid.proc_id 
                      AND pm.inactive = 0
                    INNER JOIN proc_main_practicearea AS pmp
                      ON pmp.proc_id = pm.proc_id
                      AND pmp.inactive = 0
                    INNER JOIN proc_main_proc_type AS pmpt 
                      ON pmpt.proc_id = pm.proc_id 
                      AND pmpt.inactive = 0
                    INNER JOIN procs_in_dec_hearing AS pidh 
                      ON pidh.procs_in_dec_id = pid.proc_in_dec_id 
                      AND pidh.inactive = 0 
                    INNER JOIN dec_hearing AS dh 
                      ON dh.dec_hearing_id = pidh.dec_hearing_id 
                      AND dh.inactive = 0 
                    INNER JOIN proceeding_party AS pp 
                      ON pp.proc_id = pm.proc_id 
                      AND pp.inactive = 0 
                    INNER JOIN proceeding_party_type AS ppt 
                      ON ppt.proc_party_id = pp.proc_party_id 
                      AND ppt.inactive = 0 
                    INNER JOIN dec_hearing_party AS dhp 
                      ON dhp.procs_in_hearing_id = pidh.procs_in_hearing_id 
                      AND dhp.involved_party = ppt.proc_party_type_id 
                      AND dhp.inactive = 0 
                    INNER JOIN dec_main AS dm 
                      ON dm.dec_id = dh.dec_id 
                      AND dm.inactive = 0 
                    INNER JOIN outcome AS o 
                      ON o.outcome_id = dhp.outcome_id 
                      AND o.inactive = 0 
                    LEFT JOIN dec_report_ready AS drr 
                      ON drr.dec_id = dm.dec_id 
                      AND drr.inactive = 0 
                    LEFT JOIN dec_pres_auth AS dpa 
                      ON dpa.dec_id = dm.dec_id 
                      AND dpa.inactive = 0 
                    LEFT JOIN cj_history AS cjh 
                      ON cjh.cj_history_id = dpa.cj_history_id 
                      AND cjh.inactive = 0 
                    LEFT JOIN cj_main AS cjm 
                      ON cjm.cj_id = cjh.cj_id 
                      AND cjm.inactive = 0
                  WHERE drr.report_ready = 1 
                      AND drr.report_subtype_id = 2
                      " . $conditions . "                          
                      AND dhp.init_res_party = " . (int)$party . "
                      AND pid.inactive = 0
                  GROUP BY 1, 2, 5
                ) AS A 
                JOIN (
                  SELECT dm.dec_id AS decid, dh.dec_hearing_id AS hearid, 
                    o.outcome_name AS outcome, dhp.outcome_id AS outcomeid, 
                    ppt.party_type_id AS ptid, o.outcome_id AS outcid, 
                    COUNT(ppt.party_type_id) AS partytypecount 
                  FROM proc_in_dec pid 
                    INNER JOIN proceeding_main AS pm 
                      ON pm.proc_id = pid.proc_id 
                      AND pm.inactive = 0
                    INNER JOIN proc_main_practicearea AS pmp
                      ON pmp.proc_id = pm.proc_id
                      AND pmp.inactive = 0
                    INNER JOIN proc_main_proc_type AS pmpt 
                      ON pmpt.proc_id = pm.proc_id 
                      AND pmpt.inactive = 0
                    INNER JOIN procs_in_dec_hearing AS pidh 
                      ON pidh.procs_in_dec_id = pid.proc_in_dec_id 
                      AND pidh.inactive = 0 
                    INNER JOIN dec_hearing AS dh 
                      ON dh.dec_hearing_id = pidh.dec_hearing_id 
                      AND dh.inactive = 0 
                    INNER JOIN proceeding_party AS pp 
                      ON pp.proc_id = pm.proc_id 
                      AND pp.inactive = 0 
                    INNER JOIN proceeding_party_type AS ppt 
                      ON ppt.proc_party_id = pp.proc_party_id 
                      AND ppt.inactive = 0 
                    INNER JOIN dec_hearing_party AS dhp 
                      ON dhp.procs_in_hearing_id = pidh.procs_in_hearing_id 
                      AND dhp.involved_party = ppt.proc_party_type_id 
                      AND dhp.inactive = 0 
                    INNER JOIN dec_main AS dm 
                      ON dm.dec_id = dh.dec_id 
                      AND dm.inactive = 0
                    INNER JOIN outcome AS o 
                      ON o.outcome_id = dhp.outcome_id 
                      AND o.inactive = 0 
                    LEFT JOIN dec_report_ready AS drr 
                      ON drr.dec_id = dm.dec_id 
                      AND drr.inactive = 0
                    LEFT JOIN dec_pres_auth AS dpa 
                      ON dpa.dec_id = dm.dec_id 
                      AND dpa.inactive = 0 
                    LEFT JOIN cj_history AS cjh 
                      ON cjh.cj_history_id = dpa.cj_history_id 
                      AND cjh.inactive = 0 
                    LEFT JOIN cj_main AS cjm 
                      ON cjm.cj_id = cjh.cj_id 
                      AND cjm.inactive = 0  
                    WHERE drr.report_ready = 1 
                      AND drr.report_subtype_id = 2
                      " . $conditions . "                          
                      AND dhp.init_res_party = " . (int)$party . "
                      AND pid.inactive = 0
                  GROUP BY 1, 2
                ) AS B 
                  ON A.decid = B.decid 
                  AND A.hearid = B.hearid
                )
              ) AS C 
            GROUP BY C.outcome DESC;
        ";

        return $this->runQuery($query);
    }

    public function getPopularCourtHearings($court)
    {
        $query = "
            SELECT * FROM hearing_type
            WHERE court_id = " . (int)$court . "
                AND hearing_type_pop = 1
                AND inactive = 0
        ";

        return $this->runQuery($query);
    }

    public function getHearingType($id)
    {
        $query = "
            SELECT * FROM hearing_type
            WHERE hearing_type_id = " . (int)$id . "
                AND inactive = 0
        ";

        return $this->runQuery($query, true);
    }

    protected function getReportConditions($data)
    {
        $where = $this->getApiDateRange($data);

        if ($data->court_id && (int)$data->court_id > 0) {
            $where .= ' AND dm.court_type_id IN (' . (int)$data->court_id . ') ';
        }

        $where .= $this->getApiPracticeArea($data);

        if ($data->judge_id && (int)$data->judge_id) {
            $where .= ' AND cjm.cj_id  IN (' . (int)$data->judge_id . ') ';
        }
        if ($data->hearing_type && (int)$data->hearing_type) {
            $where .= ' AND dh.hearing_type IN (' . (int)$data->hearing_type . ') ';
        }
        if ($data->hearing_subtype && (int)$data->hearing_subtype) {
            $where .= ' AND dh.hearing_subtype IN (' . (int)$data->hearing_subtype . ') ';
        }
        if ($data->proc_type && (int)$data->proc_type) {
            $where .= ' AND pmpt.proc_type_id IN (' . (int)$data->proc_type . ') ';
        }

        return $where;
    }

    protected function getDecOutcomeQuery($where, $outcome = false)
    {
        if ($outcome) {
            $outcome = " AND o.outcome_name = '" . $outcome . "'";
        } else {
            $outcome = " AND (o.outcome_name != 'Loss' AND o.outcome_name != 'Win')";
        }

        $query = "
            SELECT dm.decname, dm.offline_document_name
            FROM dec_main dm              
            LEFT JOIN proc_in_dec pid
              ON pid.dec_id = dm.dec_id
              AND pid.inactive = 0
            LEFT JOIN proceeding_main pm
              ON pm.proc_id = pid.proc_id  
              AND pm.inactive = 0
            INNER JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id
              AND pmp.inactive = 0
            INNER JOIN proc_main_proc_type AS pmpt 
              ON pmpt.proc_id = pm.proc_id 
              AND pmpt.inactive = 0
            INNER JOIN dec_pres_auth dpa
              ON dpa.dec_id = dm.dec_id
              AND dpa.inactive = 0
            INNER JOIN cj_history cjh
              ON cjh.cj_history_id = dpa.cj_history_id
              AND cjh.inactive = 0
            INNER JOIN cj_main cjm 
              ON cjm.cj_id = cjh.cj_id
              AND cjm.inactive = 0
            INNER JOIN cj_type cjt
              ON cjt.cj_type_id = cjh.cj_type_id
              AND cjt.inactive = 0
            INNER JOIN dec_hearing dh 
              ON dh.dec_id = dm.dec_id
              AND dh.inactive = 0              
            INNER JOIN procs_in_dec_hearing AS pidh 
              ON pidh.procs_in_dec_id = pid.proc_in_dec_id 
              AND pidh.inactive = 0 
            INNER JOIN proceeding_party AS pp 
              ON pp.proc_id = pm.proc_id 
              AND pp.inactive = 0 
            INNER JOIN proceeding_party_type AS ppt 
              ON ppt.proc_party_id = pp.proc_party_id 
              AND ppt.inactive = 0
            INNER JOIN dec_hearing_party AS dhp 
              ON dhp.procs_in_hearing_id = pidh.procs_in_hearing_id 
              AND dhp.involved_party = ppt.proc_party_type_id 
              AND dhp.inactive = 0              
            INNER JOIN outcome o 
              ON o.outcome_id = dhp.outcome_id
              AND o.inactive = 0
            WHERE dm.inactive = 0
            " . $outcome . "                
            " . $where . "
            GROUP BY dm.dec_id
            ORDER BY dm.dec_date DESC, dm.decname ASC, dm.citation_no ASC
            LIMIT 20
        ";

        return $query;
    }

    // -----------------------

    public function getConRep($data)
    {
        if ($data->hearing_type) {
            $hearings = array();
        } else {
            $hearings = $this->getPopularCourtHearings($data->court_id);
        }

        return array(
            'tat' => $this->getBlockTat($data),
            'list' => $this->getBlockList($data),
            'total' => $this->getBlockDecTotal($data, $hearings),
            'outcome' => $this->getBlockOutcome($data, $hearings),
        );
    }

    protected function getBlockTat($data)
    {
        $total = $this->getAverage($data);
        $decTatDecisions = $this->getDecTatList($data);
        $median = $this->calculateMedian($decTatDecisions);

        return array(
            'avg' => array( // total number of decisions?
                'label' => 'Average TAT',
                'shape' => 'rect',
                'result' => $total['average'],
            ),
            'median' => array(
                'label' => 'Median TAT',
                'shape' => 'rect',
                'result' => $median,
            ),
        );
    }

    protected function getBlockList($data)
    {
        $rows = $this->getDecisionList($data);

        return $this->processDecisions($rows);
    }

    protected function processDecisions($decisions)
    {
        $result = array();

        foreach ($decisions as $row) {
            $result[] = array(
                'name' => $row['decname'],
                'link' => $this->getDocumentPath($row['offline_document_name']),
            );
        }

        return $result;
    }

    protected function getDocumentPath($name)
    {
        return 'https://s3.amazonaws.com/' . APP_BUCKED . "/" . $name;
    }

    protected function getBlockDecTotal($data, $hearings)
    {
        $result = array();
        $generalData = array(
            'event' => 6,
            'shape' => 'circle',
            'color' => null,
            'label' => '# of motions published',
            'result' => null,
            'date_start' => $data->date_start,
            'date_end' => $data->date_end,
            'hearing_type' => null,
            'hearing_subtype' => null,
            'court_id' => $data->court_id,
            'judge_id' => $data->judge_id,
            'practice_id' => $data->practice_id,
            'proc_type' => $data->proc_type,
        );

        if ($data->hearing_type) {
            $hearing = $this->getHearingType($data->hearing_type);
            // Run for the selected hearing type
            $generalData['hearing_type'] = $data->hearing_type;
            $generalData['hearing_subtype'] = $data->hearing_subtype;
            $generalData['label'] = '# of ' . $hearing['hearing_name'] . ' decisions published';

            // Run query
            $generalData['result'] = $this->getDecTotal($data);

            $result[] = $generalData;

        } else {
            // Run for all hearing types for the selected court with hearing_type_pop = 1

            foreach ($hearings as $row) {
                $generalData['hearing_type'] = $row['hearing_type_id'];
                $generalData['label'] = '# of ' . $row['hearing_name'] . ' decisions published';

                // Run query
                $copy = clone $data;
                $copy->hearing_type = $row['hearing_type_id'];
                $generalData['result'] = $this->getDecTotal($copy);

                $result[] = $generalData;
            }
        }

        return $result;
    }

    protected function getBlockOutcome($data, $hearings)
    {
        $result = array();
        $generalData = array(
            'event' => 7,
            'shape' => 'pie',
            'label' => null,
            'result' => null,
            'init_res' => null,
            'date_start' => $data->date_start,
            'date_end' => $data->date_end,
            'hearing_type' => null,
            'hearing_subtype' => null,
            'court_id' => $data->court_id,
            'judge_id' => $data->judge_id,
            'practice_id' => $data->practice_id,
            'proc_type' => $data->proc_type,
        );

        if ($data->hearing_type) {
            // Run for the selected hearing type

            $generalData['hearing_type'] = $data->hearing_type;
            $generalData['hearing_subtype'] = $data->hearing_subtype;

            $hearingType = $this->getHearingType($data->hearing_type);

            // Run query
            $movingOutcome = $this->getHearingOutcome($data, 1);
            $respondingOutcome = $this->getHearingOutcome($data, 0);

            $moving = $generalData;
            $responding = $generalData;

            $moving['init_res'] = 1;
            $moving['label'] = 'Moving Party ' . $hearingType['hearing_name'] . ' Outcomes';
            $moving['result'] = $this->calculateOutcome($movingOutcome);

            $responding['init_res'] = 0;
            $responding['label'] = 'Responding Party ' . $hearingType['hearing_name'] . ' Outcomes';
            $responding['result'] = $this->calculateOutcome($respondingOutcome);

            $result[] = array(
                'moving' => $moving,
                'responding' => $responding,
            );

        } else {
            // Run for all hearing types for the selected court with hearing_type_pop = 1

            foreach ($hearings as $row) {
                $data->hearing_type = $row['hearing_type_id'];
                $generalData['hearing_type'] = $row['hearing_type_id'];

                // Run query
                $movingOutcome = $this->getHearingOutcome($data, 1);
                $respondingOutcome = $this->getHearingOutcome($data, 0);

                $moving = $generalData;
                $responding = $generalData;

                $moving['init_res'] = 1;
                $moving['label'] = 'Moving Party ' . $row['hearing_name'] . ' Outcomes';
                $moving['result'] = $this->calculateOutcome($movingOutcome);

                $responding['init_res'] = 0;
                $responding['label'] = 'Responding Party ' . $row['hearing_name'] . ' Outcomes';
                $responding['result'] = $this->calculateOutcome($respondingOutcome);

                $result[] = array(
                    'moving' => $moving,
                    'responding' => $responding,
                );
            }
        }

        return $result;
    }

    protected function calculateOutcome($outcomes)
    {
        if (!$outcomes) {
            return array(
                'win' => 0,
                'loss' => 0,
                'other' => 0,
            );
        }

        $win = 0;
        $loss = 0;
        $other = 0;
        $total = 0;

        foreach ($outcomes as $row) {
            $total += $row['weighteddoutcome'];

            if ($row['outcome'] == 'Win') {
                $win = $row['weighteddoutcome'];
            } elseif ($row['outcome'] == 'Loss') {
                $loss = $row['weighteddoutcome'];
            } else {
                $other += $row['weighteddoutcome'];
            }
        }

        return array(
            'win' => number_format(round(($win / $total) * 100, 2), 2, '.', ''),
            'loss' => number_format(round(($loss / $total) * 100, 2), 2, '.', ''),
            'other' => number_format(round(($other / $total) * 100, 2), 2, '.', ''),
        );
    }

    public function setApiDateRange($court)
    {
        $courtId = (int)$court;
        $and = '';

        if ($courtId > 0) {
            $and = ' AND dr.court_id = "' . $courtId . '" ';
        }

        $query = "
            SELECT dr.court_id, dr.min_date, dr.max_date, dr.practice_area_id
            FROM app_client ac
            LEFT JOIN report_centre rc
              ON rc.app_client_id = ac.app_client_id
              AND rc.inactive = 0
            LEFT JOIN report_centre_date_range dr
              ON dr.report_centre_id = rc.report_centre_id
              AND dr.inactive = 0
            WHERE ac.app_client_name = '2chair'             
              AND ac.inactive = 0
              " . $and . "
        ";

        $range = $this->runQuery($query, true);

        if ($range) {
            $this->dateRange = array(
                'court' => (int)$range['court_id'],
                'prArea' => (int)$range['practice_area_id'],
                'min_date' => $range['min_date'],
                'max_date' => $range['max_date'],
            );
        }

        return $this->dateRange;
    }

    public function logApiCall($call, $data, $customerId)
    {
        $query = "
            INSERT INTO api_log(
              customer_id, name, params
            ) VALUES (
              '" . (int)$customerId . "',
              '" . $call . "',
              '" . json_encode($data) . "'
            );
        ";

        $statement = $this->dbAdapter->query($query);
        $statement->execute();
    }

    public function getApiDateRange($data)
    {
        $date = '';

        if ($data->date_start && $data->date_end) {
            $start = date('Y-m-d', strtotime($data->date_start));
            $end = date('Y-m-d', strtotime($data->date_end));

            $date = ' AND dm.dec_date BETWEEN "' . $start . '" AND "' . $end . '" ';

        } else {
            $courtId = (int)$data->court_id;

            // Find the date range for the report center
            if ($this->dateRange['court'] !== $courtId) {
                $range = $this->setApiDateRange($courtId);

                if ($range) {
                    $date = ' AND dm.dec_date BETWEEN "' . $range['min_date'] . '" AND "' . $range['max_date'] . '" ';
                }

            } else {
                $date = ' AND dm.dec_date BETWEEN "' . $this->dateRange['min_date'];
                $date .= '" AND "' . $this->dateRange['max_date'] . '" ';
            }
        }

        return $date;
    }

    public function getApiPracticeArea($data)
    {
        if ((int)$data->practice_id > 0) {
            return ' AND pmp.practice_area_id = "' . (int)$data->practice_id . '" ';
        } elseif ($this->dateRange['prArea']) {
            return ' AND pmp.practice_area_id = "' . $this->dateRange['prArea'] . '" ';
        } else {
            $this->setApiDateRange($data->court_id);
            if ($this->dateRange['prArea']) {
                return ' AND pmp.practice_area_id = "' . $this->dateRange['prArea'] . '" ';
            }
        }

        return '';
    }
    
    public function getDateRange()
    {
        return $this->dateRange;
    }

    // -----------------------

    public function getConRepTestData($testParams)
    {
        if ($testParams == 1) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => null,
                'judge_id' => null,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
                'date_start' => null,
                'date_end' => null,
            ));

        } elseif ($testParams == 2) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => 2,
                'date_start' => '20100101',
                'date_end' => '20161231',
                'judge_id' => 489,
                'hearing_type' => 4,
                'hearing_subtype' => 608,
                'proc_type' => 10,
            ));

        } elseif ($testParams == 3) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => null,
                'date_start' => null,
                'date_end' => null,
                'judge_id' => 355,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
            ));

        } elseif ($testParams == 4) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => 2,
                'date_start' => null,
                'date_end' => null,
                'judge_id' => 355,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
            ));

        } elseif ($testParams == 5) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => 2,
                'date_start' => null,
                'date_end' => null,
                'judge_id' => 355,
                'hearing_type' => 2,
                'hearing_subtype' => null,
                'proc_type' => null,
            ));

        } elseif ($testParams == 3) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => null,
                'date_start' => '2010-01-01',
                'date_end' => '2017-02-03',
                'judge_id' => 355,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
            ));

        } elseif ($testParams == 4) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => 2,
                'date_start' => '2010-01-01',
                'date_end' => '2017-02-03',
                'judge_id' => 355,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
            ));

        } elseif ($testParams == 5) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => 2,
                'date_start' => '2010-01-01',
                'date_end' => '2017-02-03',
                'judge_id' => 355,
                'hearing_type' => 2,
                'hearing_subtype' => null,
                'proc_type' => null,
            ));

        } elseif ($testParams == 11) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '',
                'practice_id' => '',
                'date_start' => '',
                'date_end' => '',
                'judge_id' => '',
                'hearing_type' => '',
                'hearing_subtype' => '',
                'proc_type' => '',
            ));

        } elseif ($testParams == 12) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => null,
                'practice_id' => null,
                'date_start' => null,
                'date_end' => null,
                'judge_id' => null,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
            ));

        } elseif ($testParams == 16) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => 0,
                'practice_id' => 0,
                'date_start' => 0,
                'date_end' => 0,
                'judge_id' => 0,
                'hearing_type' => 0,
                'hearing_subtype' => 0,
                'proc_type' => 0,
            ));

        } else {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '5',
                'court_id' => '2',
                'practice_id' => 2,
                'date_start' => '20160101',
                'date_end' => '20161231',
                'judge_id' => 55,
                'hearing_type' => 4,
                'hearing_subtype' => 608,
                'proc_type' => 10,
            ));
        }
    }

    public function getCaseTypeListTestData($testParams)
    {
        if ($testParams == 1) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '4',
                'practice_id' => null,
            ));

        } elseif ($testParams == 2) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '4',
                'practice_id' => 2,
            ));

        } elseif ($testParams == 11) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '4',
                'practice_id' => '',
            ));

        } elseif ($testParams == 12) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '4',
                'practice_id' => null,
            ));

        } elseif ($testParams == 16) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => 0,
                'practice_id' => 0,
            ));

        } else {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '4',
                'practice_id' => 5,
            ));
        }
    }

    public function getPopHearTypeListTestData($testParams)
    {
        if ($testParams == 1) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '2',
                'court_id' => '2',
                'practice_id' => null,
            ));

        } elseif ($testParams == 2) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '2',
                'court_id' => '2',
                'practice_id' => '2',
            ));

        } elseif ($testParams == 11) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '2',
                'court_id' => '',
                'practice_id' => '',
            ));

        } elseif ($testParams == 12) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '2',
                'court_id' => null,
                'practice_id' => null,
            ));

        } elseif ($testParams == 15) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '2',
                'court_id' => '2',
                'practice_id' => '',
            ));

        } elseif ($testParams == 16) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => 0,
                'court_id' => 0,
                'practice_id' => 0,
            ));

        } else {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '2',
                'court_id' => '3',
                'practice_id' => '6',
            ));
        }
    }

    public function getHearSubSearchTestData($testParams)
    {
        if ($testParams == 1) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '3',
                'court_id' => '2',
                'practice_id' => null,
                'hearing_type' => null,
                'search' => null,
            ));

        } elseif ($testParams == 2) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '3',
                'court_id' => '2',
                'practice_id' => '2',
                'hearing_type' => '2',
                'search' => 'Motion',
            ));

        } elseif ($testParams == 11) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '3',
                'court_id' => '',
                'practice_id' => '',
                'hearing_type' => '',
                'search' => '',
            ));

        } elseif ($testParams == 12) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '3',
                'court_id' => null,
                'practice_id' => null,
                'hearing_type' => null,
                'search' => null,
            ));

        } elseif ($testParams == 13) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '3',
                'court_id' => null,
                'practice_id' => null,
                'hearing_type' => null,
                'search' => 'tri',
            ));

        } elseif ($testParams == 14) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '3',
                'court_id' => null,
                'practice_id' => null,
                'hearing_type' => 7,
                'search' => 'tri',
            ));

        } elseif ($testParams == 16) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '3',
                'court_id' => 0,
                'practice_id' => 0,
                'hearing_type' => 0,
                'search' => 0,
            ));

        } else {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '3',
                'court_id' => '2',
                'practice_id' => '2',
                'hearing_type' => '5',
                'search' => 'Motion',
            ));
        }
    }

    public function getJudSearchTestData($testParams)
    {
        if ($testParams == 1) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '1',
                'court_id' => null,
                'practice_id' => null,
                'date_start' => null,
                'date_end' => null,
                'search' => null,
            ));

        } elseif ($testParams == 2) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '1',
                'court_id' => '2',
                'practice_id' => '2',
                'date_start' => '20100101',
                'date_end' => '20161231',
                'search' => 'Hilda',
            ));

        } elseif ($testParams == 10) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '1',
                'court_id' => '',
                'practice_id' => '',
                'date_start' => '',
                'date_end' => '',
                'search' => 'bel',
            ));

        } elseif ($testParams == 11) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '1',
                'court_id' => '',
                'practice_id' => '',
                'date_start' => '',
                'date_end' => '',
                'search' => '',
            ));

        } elseif ($testParams == 12) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '1',
                'court_id' => null,
                'practice_id' => null,
                'date_start' => null,
                'date_end' => null,
                'search' => null,
            ));

        } elseif ($testParams == 16) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '1',
                'court_id' => 0,
                'practice_id' => 0,
                'date_start' => 0,
                'date_end' => 0,
                'search' => 0,
            ));

        } else {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '1',
                'court_id' => '3',
                'practice_id' => '4',
                'date_start' => '20100101',
                'date_end' => '20161231',
                'search' => 'Myers',
            ));
        }
    }

    public function getDecCntListRepTestData($testParams)
    {
        if ($testParams == 1) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '6',
                'court_id' => '2',
                'judge_id' => null,
                'practice_id' => null,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
                'date_start' => null,
                'date_end' => null,
            ));

        } elseif ($testParams == 2) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '6',
                'court_id' => '2',
                'judge_id' => '489',
                'practice_id' => '2',
                'hearing_type' => '3',
                'hearing_subtype' => '3',
                'proc_type' => '2',
                'date_start' => '20100101',
                'date_end' => '20161231',
            ));

        } elseif ($testParams == 11) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '6',
                'court_id' => '',
                'judge_id' => '',
                'practice_id' => '',
                'hearing_type' => '',
                'hearing_subtype' => '',
                'proc_type' => '',
                'date_start' => '',
                'date_end' => '',
            ));

        } elseif ($testParams == 12) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '6',
                'court_id' => null,
                'judge_id' => null,
                'practice_id' => null,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
                'date_start' => null,
                'date_end' => null,
            ));

        } elseif ($testParams == 16) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '6',
                'court_id' => 0,
                'judge_id' => 0,
                'practice_id' => 0,
                'hearing_type' => 0,
                'hearing_subtype' => 0,
                'proc_type' => 0,
                'date_start' => 0,
                'date_end' => 0,
            ));

        } else {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '6',
                'court_id' => '3',
                'judge_id' => '489',
                'practice_id' => '5',
                'hearing_type' => '3',
                'hearing_subtype' => '3',
                'proc_type' => '2',
                'date_start' => '20100101',
                'date_end' => '20161231',
            ));
        }
    }

    public function getDecOutListRepTestData($testParams)
    {
        if ($testParams == 1) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '7',
                'court_id' => '2',
                'judge_id' => null,
                'practice_id' => null,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
                'date_start' => null,
                'date_end' => null,
                'init_res_party' => 1,
            ));

        } elseif ($testParams == 2) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '7',
                'court_id' => '2',
                'judge_id' => '489',
                'practice_id' => '2',
                'hearing_type' => '3',
                'hearing_subtype' => '3',
                'proc_type' => '2',
                'date_start' => '20100101',
                'date_end' => '20161231',
                'init_res_party' => 0,
            ));

        } elseif ($testParams == 8) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '7',
                'court_id' => '7',
                'judge_id' => '34',
                'practice_id' => '2',
                'hearing_type' => '15',
                'hearing_subtype' => null,
                'proc_type' => null,
                'date_start' => '20100101',
                'date_end' => '20170412',
                'init_res_party' => 0,
            ));

        } elseif ($testParams == 11) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '7',
                'court_id' => '',
                'judge_id' => '',
                'practice_id' => '',
                'hearing_type' => '',
                'hearing_subtype' => '',
                'proc_type' => '',
                'date_start' => '',
                'date_end' => '',
                'init_res_party' => 0,
            ));

        } elseif ($testParams == 12) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '7',
                'court_id' => null,
                'judge_id' => null,
                'practice_id' => null,
                'hearing_type' => null,
                'hearing_subtype' => null,
                'proc_type' => null,
                'date_start' => null,
                'date_end' => null,
                'init_res_party' => 0,
            ));

        } elseif ($testParams == 16) {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '7',
                'court_id' => 0,
                'judge_id' => 0,
                'practice_id' => 0,
                'hearing_type' => 0,
                'hearing_subtype' => 0,
                'proc_type' => 0,
                'date_start' => 0,
                'date_end' => 0,
                'init_res_party' => 0,
            ));

        } else {
            return new \Zend\Stdlib\Parameters(array(
                'api_key' => $this->testToken,
                'source' => $this->source,
                'version' => $this->version,
                'response_id' => $this->responseId,
                'report' => 'RR', // RR, QR
                'report_event' => '7',
                'court_id' => '3',
                'judge_id' => '489',
                'practice_id' => '5',
                'hearing_type' => '3',
                'hearing_subtype' => '3',
                'proc_type' => '3',
                'date_start' => '20100101',
                'date_end' => '20161231',
                'init_res_party' => 1,
            ));
        }
    }

    public function getSettingsTestData()
    {
        return new \Zend\Stdlib\Parameters(array(
            'api_key' => $this->testToken,
            'source' => $this->source,
            'version' => $this->version,
            'response_id' => $this->responseId,
        ));
    }

    public function getLoginTestData()
    {
        return new \Zend\Stdlib\Parameters(array(
            'email' => 'test1@loomanalytics.com',
            'password' => 'test_reg3@ok.com',
            'source' => $this->source,
            'version' => $this->version,
            'response_id' => $this->responseId,
        ));
    }

    public function getLogoutTestData()
    {
        return new \Zend\Stdlib\Parameters(array(
            'api_key' => $this->testToken,
            'source' => $this->source,
            'version' => $this->version,
            'response_id' => $this->responseId,
        ));
    }
}













