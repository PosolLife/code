<?php
namespace Report\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Insert;
use Zend\Db\ResultSet\ResultSet;

class Report
{
    public $sessionTable = null;
    public $dbAdapter = null;

    protected $joinList = array();

    // List of Case Law fields
    protected $clFields = array(
        'outcome',
        'hearing_type',
        'hearing_subtype',
        'hearing_mode',
        'judge',
        'individual_company',
        'party_type',
        'claim_type',
        'case_tags',
        'proceeding_type',
        'proceeding_subtype',
        'proceeding_category',
        'self',
        'appeals',
        'practice_area',
        'court',
        'report_ready',
        'decision_date',
        'hearing_date',
    );

    // List of Outcome Report fields
    protected $orFields = array(
        'moving_party',
        'practice_area',
        'court',
        'party_type',
        'individual_company',
        'hearing_type',
        'proceeding_type',
        'proceeding_subtype',
        'case_tags',
        'proceeding_category',
        'judge',
        'hearing_mode',
        'hearing_subtype',
        'claim_type',
        'outcome_appeals',
        'self',
        'report_ready',
        'decision_date',
        'hearing_date',
    );

    // List of Dec Tat fields
    protected $dtFields = array(
        'individual_company',
        'party_type',
        'hearing_type',
        'proceeding_type',
        'proceeding_subtype',
        'proceeding_category',
        'outcome',
        'hearing_mode',
        'hearing_subtype',
        'claim_type',
        'appeals',
        'self',
        'case_tags',
        'practice_area',
        'court',
        'report_ready',
        'decision_date',
        'hearing_date',
        'judge',
    );

    // Tables
    protected $tables = array(
        'cj_history' => array(
            'name' => 'cj_history',
            'pr' => 'cjh',
            'inactive' => true,
            'join' => array(
                'dpa' => 'cjh.cj_history_id = dpa.cj_history_id'
            ),
        ),
        'cj_main' => array(
            'name' => 'cj_main',
            'pr' => 'cjm',
            'inactive' => true,
            'join' => array(
                'cjh' => 'cjm.cj_id = cjh.cj_id'
            ),
        ),
        'cj_type' => array(
            'name' => 'cj_type',
            'pr' => 'cjt',
            'inactive' => true,
            'join' => array(
                'cjh' => 'cjt.cj_type_id = cjh.cj_type_id'
            ),
        ),
        'dec_main' => array(
            'name' => 'dec_main',
            'pr' => 'dm',
            'inactive' => true,
            'join' => array(
                'dh' => 'dm.dec_id = dh.dec_id',
                'dpa' => 'dm.dec_id = dpa.dec_id',
                'dhd' => 'dm.dec_id = dhd.dec_id',
            ),
        ),
        'dec_hearing_party' => array(
            'name' => 'dec_hearing_party',
            'pr' => 'dhp',
            'inactive' => true,
            'join' => array(
                'pidh' => array(
                    'dhp.procs_in_hearing_id = pidh.procs_in_hearing_id',
                    'dhp.involved_party = ppt.proc_party_type_id'
                )
            ),
        ),
        'dec_hearing' => array(
            'name' => 'dec_hearing',
            'pr' => 'dh',
            'inactive' => true,
            'join' => array(
                'pidh' => 'dh.dec_hearing_id = pidh.dec_hearing_id',
                'dm' => 'dh.dec_id = dm.dec_id',
            )
        ),
        'dec_report_ready' => array(
            'name' => 'dec_report_ready',
            'pr' => 'drr',
            'inactive' => true,
            'join' => array(
                'dm' => 'drr.dec_id = dm.dec_id'
            )
        ),
        'dec_pres_auth' => array(
            'name' => 'dec_pres_auth',
            'pr' => 'dpa',
            'inactive' => true,
            'join' => array(
                'dm' => 'dpa.dec_id = dm.dec_id',
            )
        ),
        'dec_hearing_date' => array(
            'name' => 'dec_hearing_date',
            'pr' => 'dhd',
            'inactive' => true,
            'join' => array(
                'dm' => 'dhd.dec_id = dm.dec_id'
            )
        ),
        'outcome' => array(
            'name' => 'outcome',
            'pr' => 'o',
            'inactive' => true,
            'join' => array(
                'dhp' => 'o.outcome_id = dhp.outcome_id'
            )
        ),
        'proceeding_type' => array(
            'name' => 'proceeding_type',
            'pr' => 'prt',
            'inactive' => true,
            'join' => array(
                'pmpt' => 'prt.proc_type_id = pmpt.proc_main_proc_type_id'
            )
        ),
        'proceeding_subtype' => array(
            'name' => 'proceeding_subtype',
            'pr' => 'prst',
            'inactive' => true,
            'join' => array(
                'psl' => 'prst.proc_subtype_id = psl.proc_subtype_id'
            )
        ),
        'proceeding_category' => array(
            'name' => 'proceeding_category',
            'pr' => 'pc',
            'inactive' => true,
            'join' => array(
                'pm' => 'pc.proc_cat_id = pm.proc_cat_id'
            )
        ),
        'procs_in_dec_hearing' => array(
            'name' => 'procs_in_dec_hearing',
            'pr' => 'pidh',
            'inactive' => true,
            'join' => array(
                'pid' => 'pidh.procs_in_dec_id = pid.proc_in_dec_id',
                'dh' => 'pidh.dec_hearing_id = dh.dec_hearing_id'
            )
        ),
        'proc_in_dec' => array(
            'name' => 'proc_in_dec',
            'pr' => 'pid',
            'inactive' => true,
            'join' => array(
                'dm' => 'pid.dec_id = dm.dec_id'
            )
        ),
        'proceeding_main' => array(
            'name' => 'proceeding_main',
            'pr' => 'pm',
            'inactive' => true,
            'join' => array(
                'pid' => 'pm.proc_id = pid.proc_id'
            )
        ),
        'proc_subtype_list' => array(
            'name' => 'proc_subtype_list',
            'pr' => 'psl',
            'inactive' => true,
            'join' => array(
                'pmpt' => 'psl.proc_main_proc_type_id = pmpt.proc_main_proc_type_id'
            )
        ),
        'proc_subtype_para_list' => array(
            'name' => 'proc_subtype_para_list',
            'pr' => 'pspl',
            'inactive' => true,
            'join' => array(
                'pm' => 'pspl.proc_id = pm.proc_id'
            )
        ),
        'proceeding_party' => array(
            'name' => 'proceeding_party',
            'pr' => 'pp',
            'inactive' => true,
            'join' => array(
                'pm' => 'pp.proc_id = pm.proc_id'
            )
        ),
        'proceeding_party_type' => array(
            'name' => 'proceeding_party_type',
            'pr' => 'ppt',
            'inactive' => true,
            'join' => array(
                'pp' => 'ppt.proc_party_id = pp.proc_party_id'
            )
        ),
        'proceeding_dec_counsel' => array(
            'name' => 'proceeding_dec_counsel',
            'pr' => 'pdc',
            'inactive' => true,
            'join' => array(
                'ppt' => 'pdc.proc_party_type_id = ppt.proc_party_type_id'
            )
        ),
        'party_type' => array(
            'name' => 'party_type',
            'pr' => 'pt',
            'inactive' => true,
            'join' => array(
                'ppt' => 'pt.party_type_id = ppt.party_type_id'
            )
        ),
        'proc_main_practicearea' => array(
            'name' => 'proc_main_practicearea',
            'pr' => 'pmp',
            'inactive' => true,
            'join' => array(
                'pm' => 'pmp.proc_id = pm.proc_id'
            )
        ),
        'proc_main_proc_type' => array(
            'name' => 'proc_main_proc_type',
            'pr' => 'pmpt',
            'inactive' => true,
            'join' => array(
                'pm' => 'pmpt.proc_id = pm.proc_id'
            )
        ),
        'related_proceedings' => array(
            'name' => 'related_proceedings',
            'pr' => 'rp',
            'inactive' => true,
            'join' => array(
                'dh' => 'rp.rel_proceeding_id = dh.rel_proceeding_id'
            )
        ),
    );

    public function __construct($dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    // Case Law
    public function getCaseLaw($settings)
    {
        $where = $this->getCommonReportConditions($settings['data'], $this->clFields);

        $query = $this->getQueryForCaseLaw($where, $settings);

        // Query Log
        $this->saveQuery($settings);

        return $this->runQuery($query, false, __FUNCTION__);
    }

    // Outcome Report
    public function getOutcomeReport($settings)
    {
        $where = $this->getCommonReportConditions($settings['data'], $this->orFields);

        // All parties on the same side of the proceedings share the same outcome
        if ($settings['data']->hearing_decision == 1) {
            $allOrAny = ' WHERE A.outcome_count/B.party_type_count = 1';
        } else {
            $allOrAny = '';
        }

        // Calculate the data for each outcome name Decisions
        $query = $this->getQueryForOutcome($where, $allOrAny);

        // Execute query
        $result = $this->runQuery($query, false, __FUNCTION__ . ': Outcome Report');

        // Query Log
        $this->saveQuery($settings);

        // Total To calculate the percent of Decisions table
        $query = $this->getQueryForOutcomeTotal($where, $allOrAny);

        // Execute query
        $total = $this->runQuery($query, true, __FUNCTION__ . ': Total Outcome Report');


        return array(
            'result' => $result,
            'total' => $total,
        );
    }

    // Outcome decisions (Outcome Report new window)
    public function getOutcomeDecisions($settings)
    {
        $where = $this->getCommonReportConditions($settings['data'], $this->orFields);

        $query = $this->getQueryForOutcomeDecisions($where, $settings);

        // Execute query
        $result = $this->runQuery($query, false, __FUNCTION__);

        // Query Log
        $this->saveQuery($settings);


        return $result;
    }

    // Dec Tat
    public function getDecTat($settings)
    {
        $where = $this->getCommonReportConditions($settings['data'], $this->dtFields);

        $this->addTableToJoinList([
            'dec_main',
            'dec_pres_auth',
            'cj_main',
            'cj_history',
            'proc_in_dec',
            'proceeding_main',
            'proc_main_proc_type',
        ]);

        // Query Log
        $this->saveQuery($settings);

        if ($settings['type'] == 2) { 
			// All judges
            $query = $this->getQueryForAllJudges($where, $settings);

            // Execute query
            $result = $this->runQuery($query, false, __FUNCTION__ . ' (all judges)');

            $ranges = $this->getDecTatRanges($where);

            return array('result' => $result, 'ranges' => $ranges);

        } else { 
			// Single judge
            $query = $this->getQueryForSpecificJudge($where, $settings);

            // Execute query
            $result = $this->runQuery($query, false, __FUNCTION__ . ' (specific judge)');

            if (!isset($settings['avg']) || !$settings['avg']) {
                return array('result' => $result);
            }

            // --------------------

            $query = $this->getQueryForAvgSpecificJudge($where);

            // Execute query
            $avgRes = $this->runQuery($query, true, __FUNCTION__ . ' (specific judge totals)');

            return array(
                'result' => $result,
                'average' => $avgRes['average'],
                'dec_num' => $avgRes['dec_num']
            );
        }
    }

    // Get Slave Reports available to a specific Stripe plan
    public function getPlanReports($planId, $browser = false)
    {
        $and = $browser ? " AND ac.app_client_name = 'browser'" : '';

        $query = "
            SELECT sr.*, mr.title AS default_title, mr.box_color AS default_box_color,
              mr.img AS default_img, mr.img_style AS default_img_style, 
              mr.description AS default_description, mr.report_prefix,
              r2c.report_centre_id, r2c.disable, r2c.disable_text
            FROM report_to_centre r2c
            INNER JOIN report_centre rc
              ON rc.report_centre_id = r2c.report_centre_id
              AND rc.inactive = 0
            INNER JOIN app_client ac
              ON ac.app_client_id = rc.app_client_id
              AND ac.inactive = 0
            INNER JOIN slave_report sr
              ON sr.slave_report_id = r2c.slave_report_id
              AND sr.inactive = 0
            INNER JOIN master_report mr
              ON mr.master_report_id = sr.master_report_id
              AND mr.inactive = 0
            INNER JOIN plan_to_centre p2c
              ON p2c.report_centre_id = r2c.report_centre_id
              AND p2c.inactive = 0
            WHERE p2c.stripe_plan_id = " . (int)$planId . "
              AND r2c.inactive = 0
              " . $and . "
            ORDER BY r2c.report_centre_id, r2c.display_order
        ";

        $rows = $this->runQuery($query, false, __FUNCTION__);

        return $this->processSlaveReports($rows);
    }

    // Get Report Centre with no_login = 1
    public function getNoLoginReports($browser = false)
    {
        $and = $browser ? " AND ac.app_client_name = 'browser'" : '';

        $query = "
            SELECT sr.*, mr.title AS default_title, mr.box_color AS default_box_color,
              mr.img AS default_img, mr.img_style AS default_img_style, 
              mr.description AS default_description, mr.report_prefix,
              r2c.disable, r2c.disable_text
            FROM report_centre rc
            INNER JOIN app_client ac
              ON ac.app_client_id = rc.app_client_id
              AND ac.inactive = 0
            INNER JOIN report_to_centre r2c
              ON r2c.report_centre_id = rc.report_centre_id
              AND r2c.inactive = 0            
            INNER JOIN slave_report sr
              ON sr.slave_report_id = r2c.slave_report_id
              AND sr.inactive = 0
            INNER JOIN master_report mr
              ON mr.master_report_id = sr.master_report_id
              AND mr.inactive = 0              
            WHERE rc.inactive = 0
              AND rc.no_login = 1
              " . $and . "
            ORDER BY r2c.display_order
        ";

        $rows = $this->runQuery($query, false, __FUNCTION__);

        return $this->processSlaveReports($rows);
    }

    public function getReportCentreReports($id)
    {
        $query = "
            SELECT sr.*, mr.title AS default_title, mr.box_color AS default_box_color,
              mr.img AS default_img, mr.img_style AS default_img_style, 
              mr.description AS default_description, mr.report_prefix,
              r2c.report_centre_id, r2c.disable, r2c.disable_text
            FROM report_to_centre r2c
            INNER JOIN report_centre rc
              ON rc.report_centre_id = r2c.report_centre_id
              AND rc.inactive = 0
            INNER JOIN slave_report sr
              ON sr.slave_report_id = r2c.slave_report_id
              AND sr.inactive = 0
            INNER JOIN master_report mr
              ON mr.master_report_id = sr.master_report_id
              AND mr.inactive = 0
            WHERE rc.report_centre_id = " . (int)$id . "
              AND r2c.inactive = 0
            ORDER BY r2c.report_centre_id, r2c.display_order
        ";

        $rows = $this->runQuery($query, false, __FUNCTION__);

        return $this->processSlaveReports($rows);
    }

    public function getReportCentre($id = false, $browser = false)
    {
        $and = $browser ? " AND ac.app_client_name = 'browser'" : '';
        $id = $id ? 'AND rc.report_centre_id = ' . (int)$id : 'AND rc.no_login = 1';

        $query = "
            SELECT rc.* FROM report_centre rc
            INNER JOIN app_client ac
              ON ac.app_client_id = rc.app_client_id
              AND ac.inactive = 0
            WHERE rc.inactive = 0
              " . $id . "
              " . $and . "
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    public function getReportCentreByStripePlan($stripePlanId, $browser = false)
    {
        $and = $browser ? " AND ac.app_client_name = 'browser'" : '';

        $query = "
            SELECT DISTINCT r2c.report_centre_id
            FROM report_to_centre r2c            
            INNER JOIN plan_to_centre p2c
              ON p2c.report_centre_id = r2c.report_centre_id
              AND p2c.inactive = 0
            INNER JOIN report_centre rc
              ON rc.report_centre_id = r2c.report_centre_id
              AND rc.inactive = 0
            INNER JOIN app_client ac
              ON ac.app_client_id = rc.app_client_id
              AND ac.inactive = 0
            WHERE p2c.stripe_plan_id = " . (int)$stripePlanId . "
              AND r2c.inactive = 0
              " . $and . "
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    public function processSlaveReports($rows)
    {
        $reports = array();

        foreach ($rows as $row) {
            $reports[$row['slave_report_id']] = array(
              'id' => $row['slave_report_id'],
              'type' => 'slave',
              'report_prefix' => $row['report_prefix'],
              'name' => $row['name'],
              'disable' => $row['disable'],
              'disable_text' => $row['disable_text'],
              'box_color' => $row['box_color'] ? $row['box_color'] : $row['default_box_color'],
              'img' => $row['img'] ? $row['img'] : $row['default_img'],
              'img_style' => $row['img_style'] ? $row['img_style'] : $row['default_img_style'],
              'title' => $row['title'] ? $row['title'] : $row['default_title'],
              'description' => $row['description'] ? $row['description'] : $row['default_description'],
            );
        }

        return $reports;
    }

    // Get Master Reports and process them
    public function getMasterReports()
    {
        $query = "
            SELECT * FROM master_report
            WHERE inactive = 0
        ";

        $masterReports = array();
        $rows = $this->getQueryResult($query, true);

        foreach ($rows as $row) {
            $masterReports[$row['master_report_id']] = array(
              'id' => $row['master_report_id'],
              'type' => 'master',
              'report_prefix' => $row['report_prefix'],
              'name' => $row['name'],
              'box_color' => $row['box_color'],
              'img' => $row['img'],
              'img_style' => $row['img_style'],
              'title' => $row['title'],
              'description' => $row['description'],
            );
        }

        return $masterReports;
    }

    // Get Slave Reports and process them
    public function getSlaveReports()
    {
        $query = "
            SELECT sr.*, mr.title AS default_title, mr.box_color AS default_box_color,
              mr.img AS default_img, mr.img_style AS default_img_style, 
              mr.description AS default_description, mr.report_prefix
            FROM slave_report sr
            LEFT JOIN master_report mr
              ON mr.master_report_id = sr.master_report_id
              AND mr.inactive = 0            
            WHERE sr.inactive = 0
        ";

        $slaveReports = array();
        $rows = $this->runQuery($query, false, __FUNCTION__);

        foreach ($rows as $row) {
            $slaveReports[$row['slave_report_id']] = array(
                'id' => $row['slave_report_id'],
                'type' => 'slave',
                'report_prefix' => $row['report_prefix'],
                'name' => $row['name'],
                'disable' => $row['disable'],
                'disable_text' => $row['disable_text'],
                'box_color' => $row['box_color'] ? $row['box_color'] : $row['default_box_color'],
                'img' => $row['img'] ? $row['img'] : $row['default_img'],
                'img_style' => $row['img_style'] ? $row['img_style'] : $row['default_img_style'],
                'title' => $row['title'] ? $row['title'] : $row['default_title'],
                'description' => $row['description'] ? $row['description'] : $row['default_description'],
            );
        }

        return $slaveReports;
    }


    // -------------------

    // Query for Case Law
    protected function getQueryForCaseLaw($where, $settings)
    {
        if (isset($settings['offset']) && isset($settings['limit'])) {
           $limit = " LIMIT " . $settings['offset'] . ", " . $settings['limit'];
        } else {
            $limit = '';
        }

        if (isset($settings['sort']) && isset($settings['order'])) {
            if ($settings['sort'] == 'decision-name') {
                $order = 'ORDER BY dm.decname ' . $settings['order'];
                $order .= ', dm.dec_date ASC, dm.citation_no ASC';
            } else { 
				// decision-date
                $order = 'ORDER BY dm.dec_date ' . $settings['order'];
                $order .= ', dm.decname ASC, dm.citation_no ASC';
            }
        } else {
            $order = 'ORDER BY dm.dec_date DESC, dm.decname ASC, dm.citation_no ASC';
        }

        $this->addTableToJoinList([
            'dec_report_ready',
            'dec_pres_auth',
            'proc_in_dec',
            'proceeding_main',
            'proc_main_practicearea',
            'proc_main_proc_type',
            'proceeding_type',
            'proceeding_subtype',
            'proc_subtype_list',
            'cj_type',
            'cj_main',
            'cj_history',
        ]);

        if (isset($settings['total'])) {
            $query = "SELECT COUNT(DISTINCT dm.dec_id) as total";
        } else {
            $query = "
                SELECT dm.offline_document_name, dm.decname, dm.dec_date, dm.dec_id,
                    pmp.practice_area_id, dm.court_type_id, dm.citation_no, 
                    prt.proc_type_name, prst.proc_subtype_name,
                    GROUP_CONCAT(
                      DISTINCT CONCAT (
                        COALESCE(cjt.cj_type_name, ''), ' ', 
                        COALESCE(cjm.cj_fn, ''), ' ', 
                        COALESCE(cjm.cj_mn, ''), ' ', 
                        COALESCE(cjm.cj_ln, '')	
                      ) SEPARATOR ', '
                    ) as judges 
            ";
        }

        $query .= "                   
            FROM dec_main AS dm
            " . $this->getJoin('dec_report_ready', 'dm') . "
            " . $this->getJoin('proc_in_dec', 'dm') . "
            " . $this->getJoin('proceeding_main', 'pid') . "
            " . $this->getJoin('proc_main_proc_type', 'pm') . "
            " . $this->getJoin('proc_subtype_list', 'pmpt') . "
            " . $this->getJoin('proceeding_subtype', 'psl') . "
            " . $this->getJoin('proc_subtype_para_list', 'pm') . "
            " . $this->getJoin('proceeding_party', 'pm') . "
            " . $this->getJoin('proc_main_practicearea', 'pm') . "
            " . $this->getJoin('proceeding_party_type', 'pp') . "
            " . $this->getJoin('proceeding_dec_counsel', 'ppt') . "
            " . $this->getJoin('party_type', 'ppt') . "
            " . $this->getJoin('dec_hearing', 'dm') . "
            " . $this->getJoin('related_proceedings', 'dh') . "
            " . $this->getJoin('dec_pres_auth', 'dm') . "
            " . $this->getJoin('cj_history', 'dpa') . "
            " . $this->getJoin('cj_main', 'cjh') . "
            " . $this->getJoin('cj_type', 'cjh') . "
            " . $this->getJoin('dec_hearing_date', 'dm') . "
            " . $this->getJoin('procs_in_dec_hearing', 'dh') . "
            " . $this->getJoin('dec_hearing_party', 'pidh') . "
            " . $this->getJoin('proceeding_category', 'pm') . "
            " . $this->getJoin('proceeding_type', 'pmpt') . "
            " . $this->getJoin('outcome', 'dhp') . "
            WHERE 1 = 1
            " . $where . "
        ";

        if (!isset($settings['total'])) {
            $query .= "            
            GROUP BY dm.dec_id
            " . $order . $limit;
        }

        return $query;
    }

    // Outcome Report joins
    protected function getOutcomeJoins()
    {
        $this->addTableToJoinList([
            'dec_main',
            'dec_hearing',
            'dec_hearing_party',
            'proceeding_party',
            'proceeding_party_type',
            'procs_in_dec_hearing',
            'outcome',
            'proceeding_main',
        ]);

        return "            
            " . $this->getJoin('proceeding_main', 'pid', true) . "
            " . $this->getJoin('procs_in_dec_hearing', 'pid', true) . "
            " . $this->getJoin('dec_hearing', 'pidh', true) . "
            " . $this->getJoin('proceeding_party', 'pm', true) . "
            " . $this->getJoin('proc_main_practicearea', 'pm', true) . "
            " . $this->getJoin('proceeding_party_type', 'pp', true) . "
            " . $this->getJoin('dec_hearing_party', 'pidh', true) . "
            " . $this->getJoin('dec_main', 'dh', true) . "            
            " . $this->getJoin('party_type', 'ppt', true) . "
            " . $this->getJoin('outcome', 'dhp', true) . "
            " . $this->getJoin('dec_report_ready', 'dm') . "            
            " . $this->getJoin('proc_main_proc_type', 'pm') . "
            " . $this->getJoin('proceeding_type', 'pmpt') . "
            " . $this->getJoin('proc_subtype_list', 'pmpt') . "
            " . $this->getJoin('proceeding_subtype', 'psl') . "
            " . $this->getJoin('proc_subtype_para_list', 'pm') . "
            " . $this->getJoin('proceeding_category', 'pm') . "
            " . $this->getJoin('dec_pres_auth', 'dm') . "
            " . $this->getJoin('cj_history', 'dpa') . "
            " . $this->getJoin('cj_main', 'cjh') . "
            " . $this->getJoin('cj_type', 'cjh') . "
            " . $this->getJoin('dec_hearing_date', 'dm') . "
            " . $this->getJoin('related_proceedings', 'dh') . "
            " . $this->getJoin('proceeding_dec_counsel', 'ppt') . "
        ";
    }

    // Query for Outcome Report
    protected function getQueryForOutcome($where, $allOrAny)
    {
        $joins = $this->getOutcomeJoins();

        return "
            SELECT C.outcome, C.outcomeid, SUM(weight) AS total,
              COUNT(DISTINCT(C.hearid)) AS numberHearing, COUNT(DISTINCT(C.decid)) AS numberDecisions
            FROM (
              SELECT A.decid AS decid, A.hearid, A.outcomeid, A.outcome, 
                A.outcome_count/B.party_type_count AS weight
              FROM (
                (SELECT dm.dec_id AS decid, 
                    dh.dec_hearing_id AS hearid,
                    o.outcome_name AS outcome, 
                    dhp.outcome_id AS outcomeid,
                    COUNT(dhp.outcome_id) AS outcome_count
                  FROM proc_in_dec pid
                  " . $joins . "
                  WHERE 1 = 1
                  " . $where . "
                  GROUP BY dm.dec_id, dh.dec_hearing_id, ppt.party_type_id
                ) AS A
                JOIN (
                  SELECT dm.dec_id AS decid, 
                    dh.dec_hearing_id AS hearid, 
                    COUNT(ppt.party_type_id) AS party_type_count
                  FROM proc_in_dec pid                  
                  " . $joins . "
                  WHERE 1 = 1
                  " . $where . "
                  GROUP BY dm.dec_id, dh.dec_hearing_id
                ) AS B 
                ON A.decid = B.decid
                AND A.hearid = B.hearid
              )
              " . $allOrAny . "
            ) AS C
            GROUP BY C.outcome DESC
        ";
    }

    // Query for specific outcome decisions (Outcome Report)
    protected function getQueryForOutcomeDecisions($where, $settings)
    {
        $this->addTableToJoinList([
            'proceeding_main',
            'proceeding_type',
            'proceeding_subtype',
            'proc_main_proc_type',
            'proc_subtype_list',
            'dec_pres_auth',
            'cj_history',
            'cj_main',
            'cj_type',
        ]);
        
        $joins = $this->getOutcomeJoins();

        if ($settings['data']->hearing_decision == 1) {
            $allOrAny = " AND A.outcome_count/B.party_type_count = 1";
        } else {
            $allOrAny = "";
        }

        if (isset($settings['offset']) && isset($settings['limit'])) {
            $limit = " LIMIT " . $settings['offset'] . ", " . $settings['limit'];
        } else {
            $limit = '';
        }

        if (isset($settings['sort']) && isset($settings['order'])) {
            if ($settings['sort'] == 'decision-name') {
                $order = 'ORDER BY decname ' . $settings['order'];
                $order .= ', dec_date ASC, citation_no ASC';
            } else { 
				// decision-date
                $order = 'ORDER BY dec_date ' . $settings['order'];
                $order .= ', decname ASC, citation_no ASC';
            }
        } else {
            $order = 'ORDER BY dec_date DESC, decname ASC, citation_no ASC';
        }

        return "
            SELECT DISTINCT(A.dec_id), A.decname, A.dec_date, A.citation_no, A.offline_document_name,
              A.proc_type_name, A.proc_subtype_name, A.judges
            FROM (
              (SELECT dm.dec_id, 
                  dh.dec_hearing_id AS hearid,
                  o.outcome_id AS outcome_id, 
                  COUNT(dhp.outcome_id) AS outcome_count, 
                  dm.decname, 
                  dm.dec_date, 
                  dm.citation_no, 
                  dm.offline_document_name, 
                  prt.proc_type_name, 
                  prst.proc_subtype_name,
                  GROUP_CONCAT(
                    DISTINCT CONCAT (
                      COALESCE(cjt.cj_type_name, ''), ' ', 
                      COALESCE(cjm.cj_fn, ''), ' ', 
                      COALESCE(cjm.cj_mn, ''), ' ', 
                      COALESCE(cjm.cj_ln, '')	
                    ) SEPARATOR ', '
                  ) as judges   
                FROM proc_in_dec pid                
                " . $joins . "
                WHERE dm.dec_id != 1 
                " . $where . "
                GROUP BY dm.dec_id, dh.dec_hearing_id, ppt.party_type_id
              ) AS A
              JOIN (
                SELECT dm.dec_id, 
                    dh.dec_hearing_id AS hearid,
                    COUNT(ppt.party_type_id) AS party_type_count
                FROM proc_in_dec pid
                 " . $joins . "
                WHERE dm.dec_id != 1 
                " . $where . "
                GROUP BY dm.dec_id, dh.dec_hearing_id
              ) AS B 
              ON A.dec_id = B.dec_id
              AND A.hearid = B.hearid
            )
            WHERE A.outcome_id = " . (int)$settings['data']['outcome_id'] . "
            " . $allOrAny . "
            GROUP BY dec_id
            " . $order . "
            " . $limit . "
        ";
    }

    protected function findOutcomeDecisions($where, $settings)
    {
        $joins = $this->getOutcomeJoins();

        $query = "
            SELECT dm.dec_id, 
              dh.dec_hearing_id AS hearid,
              o.outcome_id AS outcome_id, 
              COUNT(dhp.outcome_id) AS outcome_count, 
              dm.decname, 
              dm.dec_date, 
              dm.citation_no, 
              dm.offline_document_name, 
              prt.proc_type_name, 
              prst.proc_subtype_name,
              GROUP_CONCAT(
                DISTINCT CONCAT (
                  COALESCE(cjt.cj_type_name, ''), ' ', 
                  COALESCE(cjm.cj_fn, ''), ' ', 
                  COALESCE(cjm.cj_mn, ''), ' ', 
                  COALESCE(cjm.cj_ln, '')	
                ) SEPARATOR ', '
              ) as judges   
            FROM proc_in_dec pid                
            " . $joins . "
            WHERE dm.dec_id != 1
              AND o.outcome_id = " . (int)$settings['data']['outcome_id'] . "
              " . $where . "
            GROUP BY dm.dec_id, dh.dec_hearing_id, ppt.party_type_id
            ORDER BY dec_date DESC, decname ASC, citation_no ASC
            LIMIT 100
        ";

        $outcomeResult = $this->runQuery($query, false, __FUNCTION__ . ' (outcome_count)');

        $query = "
            SELECT dm.dec_id, 
                dh.dec_hearing_id AS hearid,
                COUNT(ppt.party_type_id) AS party_type_count
            FROM proc_in_dec pid
             " . $joins . "
            WHERE dm.dec_id != 1 
            " . $where . "
            GROUP BY dm.dec_id, dh.dec_hearing_id
            ORDER BY dec_date DESC, decname ASC, citation_no ASC
            LIMIT 100
        ";

        $partyTypeResult = $this->runQuery($query, false, __FUNCTION__ . ' (party_type_count)');

        $return = array();
        $outcome = array();
        $partyType = array();
        $decisions = array();
        $hearings = array();

        foreach ($outcomeResult as $row) {
            $ids = $row['dec_id'] . ' => ' . $row['hearid'];
            $outcome[] = array(
                'dec_id' => $row['dec_id'],
                'hearid' => $row['hearid'],
                'ids' => $ids,
                'count' => $row['outcome_count'],
                'decname' => $row['decname'],
                'dec_date' => $row['dec_date'],
                'citation_no' => $row['citation_no'],
                'offline_document_name' => $row['offline_document_name'],
                'proc_type_name' => $row['proc_type_name'],
                'proc_subtype_name' => $row['proc_subtype_name'],
                'judges' => $row['judges'],
            );
        }

        foreach ($partyTypeResult as $row) {
            $ids = $row['dec_id'] . ' => ' . $row['hearid'];
            $partyType[$ids] = $row['party_type_count'];
        }

        foreach ($outcome as $row) {
            if (isset($partyType[$row['ids']])) {
                $partyTypeCount = $partyType[$row['ids']];
                $result = $row['count'] / $partyTypeCount;

                if ($settings['data']->hearing_decision != 1 || $result == 1) {
                    $return[] = $row;
                    $decisions[$row['dec_id']] = true;
                    $hearings[$row['hearid']] = true;
                }
            }
        }

        return $return;
    }

    // Query for Total Outcomes (Outcome Report)
    protected function getQueryForOutcomeTotal($where, $allOrAny)
    {
        $joins = $this->getOutcomeJoins();

        return "
            SELECT COUNT(DISTINCT(C.decid)) as dectotal, 
              COUNT(DISTINCT(C.hearid)) as heartotal, SUM(weight) as total
            FROM (
              SELECT A.decid AS decid, A.hearid, A.outcome_count/B.party_type_count AS weight
              FROM (
                (SELECT dm.dec_id AS decid, 
                    dh.dec_hearing_id AS hearid,
                    dhp.outcome_id AS outcomeid, 
                    ppt.party_type_id AS ptid, 
                    o.outcome_name AS outcome, 
                    COUNT(dhp.outcome_id) AS outcome_count
                  FROM proc_in_dec pid
                  " . $joins . "
                  WHERE 1 = 1
                  " . $where . "  
                  GROUP BY dm.dec_id, dh.dec_hearing_id, ppt.party_type_id
                ) AS A
                INNER JOIN (
                  SELECT dm.dec_id AS decid, 
                    dh.dec_hearing_id AS hearid, 
                    dhp.outcome_id AS outcomeid, 
                    ppt.party_type_id AS ptid, 
                    o.outcome_name AS outcome, 
                    COUNT(ppt.party_type_id) AS party_type_count
                  FROM proc_in_dec pid
                  " . $joins . "
                  WHERE 1 = 1
                  " . $where . "
                  GROUP BY dm.dec_id, dh.dec_hearing_id
                ) AS B
                ON A.decid = B.decid
                AND A.hearid = B.hearid
              )
              " . $allOrAny . "
            ) AS C        
        ";
    }

    protected function calculateOutcomeTotal($where, $allOrAny)
    {
        $joins = $this->getOutcomeJoins();

        $query = "
          SELECT dm.dec_id AS decid, 
            dh.dec_hearing_id AS hearid,
            dhp.outcome_id AS outcomeid, 
            ppt.party_type_id AS ptid, 
            o.outcome_name AS outcome, 
            COUNT(dhp.outcome_id) AS outcome_count
          FROM proc_in_dec pid
          " . $joins . "
          WHERE 1 = 1
          " . $where . "  
          GROUP BY dm.dec_id, dh.dec_hearing_id, ppt.party_type_id
        ";

        $outcomeResult = $this->runQuery($query, false, __FUNCTION__ . ' (outcome_count)');

        $query = "
          SELECT dm.dec_id AS decid, 
            dh.dec_hearing_id AS hearid, 
            dhp.outcome_id AS outcomeid, 
            ppt.party_type_id AS ptid, 
            o.outcome_name AS outcome, 
            COUNT(ppt.party_type_id) AS party_type_count
          FROM proc_in_dec pid
          " . $joins . "
          WHERE 1 = 1
          " . $where . "
          GROUP BY dm.dec_id, dh.dec_hearing_id
        ";

        $partyTypeResult = $this->runQuery($query, false, __FUNCTION__ . ' (outcome_count)');

        // ---------------

        $outcome = array();
        $partyType = array();
        $decisions = array();
        $hearings = array();

        foreach ($outcomeResult as $row) {
            $ids = $row['decid'] . ' => ' . $row['hearid'];
            $outcome[] = array(
                'decid' => $row['decid'],
                'hearid' => $row['hearid'],
                'ids' => $ids,
                'count' => $row['outcome_count'],
            );
        }

        foreach ($partyTypeResult as $row) {
            $ids = $row['decid'] . ' => ' . $row['hearid'];
            $partyType[$ids] = $row['party_type_count'];
        }

        $total = 0;
        foreach ($outcome as $row) {
            if (isset($partyType[$row['ids']])) {
                $partyTypeCount = $partyType[$row['ids']];
                $result = $row['count'] / $partyTypeCount;

                if (!$allOrAny || $result == 1) {
                    $total += $result;
                    $decisions[$row['decid']] = true;
                    $hearings[$row['hearid']] = true;
                }
            }
        }

        return array(
            'dectotal' => count($decisions),
            'heartotal' => count($hearings),
            'total' => $total,
        );
    }

    // Query for all judges (Dec Tat)
    protected function getQueryForAllJudges($where, $settings)
    {
        if (isset($settings['offset']) && isset($settings['limit'])) {
            $limit = " LIMIT " . $settings['offset'] . ", " . $settings['limit'];
        } else {
            $limit = '';
        }

        if (isset($settings['sort']) && isset($settings['order'])) {
            if ($settings['sort'] == 'judge-name') {
                $order = 'ORDER BY judge_name ' . $settings['order'];
            } elseif ($settings['sort'] == 'decision-number') {
                $order = 'ORDER BY decision_count ' . $settings['order'];
            } else { 
				// response-time
                $order = 'ORDER BY average ' . $settings['order'];
            }
        } else {
            $order = 'ORDER BY average';
        }

        return "
            SELECT A.cjid, CONCAT (A.cjln, ', ', A.cjfn, ' ', A.cjmn) AS judge_name,
                COUNT(A.decid) AS decision_count,
                ROUND(AVG(DATEDIFF(decdate, heardate)), 2) AS average
            FROM (
                SELECT dm.dec_id AS decid, dm.offline_document_name, dm.decname,
                  dm.dec_date AS decdate, MAX(dhd.hearing_date_end) AS heardate,
                  cjm.cj_id AS cjid, cjm.cj_fn AS cjfn, cjm.cj_mn AS cjmn, cjm.cj_ln AS cjln
                FROM dec_hearing_date dhd                
                " . $this->getJoin('dec_main', 'dhd', true) . "
                " . $this->getJoin('dec_report_ready', 'dm') . "
                " . $this->getJoin('dec_pres_auth', 'dm', true) . "
                " . $this->getJoin('cj_history', 'dpa', true) . "
                " . $this->getJoin('cj_main', 'cjh', true) . "
                " . $this->getJoin('proc_in_dec', 'dm') . "
                " . $this->getJoin('proceeding_main', 'pid') . "
                " . $this->getJoin('proceeding_party', 'pm') . "
                " . $this->getJoin('proceeding_party_type', 'pp') . "
                " . $this->getJoin('party_type', 'ppt') . "                
                " . $this->getJoin('proc_main_proc_type', 'pm') . "
                " . $this->getJoin('proceeding_type', 'pmpt') . "
                " . $this->getJoin('proc_subtype_list', 'pmpt') . "
                " . $this->getJoin('proceeding_category', 'pm') . "
                " . $this->getJoin('proc_main_practicearea', 'pm') . "
                " . $this->getJoin('proceeding_dec_counsel', 'ppt') . "
                " . $this->getJoin('dec_hearing', 'dm') . "
                " . $this->getJoin('related_proceedings', 'dh') . "
                " . $this->getJoin('procs_in_dec_hearing', 'dh') . "
                " . $this->getJoin('dec_hearing_party', 'pidh') . "
                " . $this->getJoin('outcome', 'dhp') . "
                " . $this->getJoin('proc_subtype_para_list', 'pm') . "
                WHERE 1 = 1
                " . $where . "
                GROUP BY dm.dec_id                
            ) AS A
            GROUP BY A.cjid
            HAVING COUNT(A.decid) > 10
            " . $order . "
            " . $limit . "
        ;";
    }

    // Query for specific judge (Dec Tat)
    protected function getQueryForSpecificJudge($where, $settings)
    {
        if (isset($settings['offset']) && isset($settings['limit'])) {
            $limit = " LIMIT " . $settings['offset'] . ", " . $settings['limit'];
        } else {
            $limit = '';
        }

        if (isset($settings['sort']) && isset($settings['order'])) {
            if ($settings['sort'] == 'decision-name') {
                $order = 'ORDER BY dm.decname ' . $settings['order'];
                $order .= ', decdate ASC, dm.citation_no ASC';
            } elseif ($settings['sort'] == 'turnaround-time') {
                $order = 'ORDER BY dectat ' . $settings['order'];
                $order .= ', dm.decname ASC, dm.citation_no ASC';
            } else { 
				// decision-date
                $order = 'ORDER BY decdate ' . $settings['order'];
                $order .= ', dm.decname ASC, dm.citation_no ASC';
            }
        } else {
            $order = 'ORDER BY decdate DESC, dm.decname ASC, dm.citation_no ASC';
        }

        $this->addTableToJoinList([
            'proceeding_main',
            'proc_main_proc_type',
            'proceeding_type',
            'proceeding_subtype',
            'proc_subtype_list',
            'cj_type',
        ]);

        return "
            SELECT dm.dec_id AS decid, dm.offline_document_name, dm.decname, dm.citation_no,
                    dm.dec_date AS decdate, MAX(dhd.hearing_date_end) AS heardate,
                    DATEDIFF(dm.dec_date ,MAX(dhd.hearing_date_end)) AS dectat,
                    cjm.cj_id AS cjid, cjm.cj_fn AS cjfn, cjm.cj_mn AS cjmn, cjm.cj_ln AS cjln,
                    prt.proc_type_name, prst.proc_subtype_name,
                    GROUP_CONCAT(
                      DISTINCT CONCAT (
                        COALESCE(cjt.cj_type_name, ''), ' ', 
                        COALESCE(cjm.cj_fn, ''), ' ', 
                        COALESCE(cjm.cj_mn, ''), ' ', 
                        COALESCE(cjm.cj_ln, '')	
                      ) SEPARATOR ', '
                    ) as judges 
                FROM dec_hearing_date dhd
                " . $this->getJoin('dec_main', 'dhd', true) . "                
                " . $this->getJoin('dec_pres_auth', 'dm', true) . "
                " . $this->getJoin('cj_history', 'dpa', true) . "
                " . $this->getJoin('cj_main', 'cjh', true) . "
                " . $this->getJoin('cj_type', 'cjh') . "
                " . $this->getJoin('dec_report_ready', 'dm') . "
                " . $this->getJoin('proc_in_dec', 'dm') . "
                " . $this->getJoin('proceeding_main', 'pid') . "
                " . $this->getJoin('proceeding_party', 'pm') . "
                " . $this->getJoin('proceeding_party_type', 'pp') . "
                " . $this->getJoin('party_type', 'ppt') . "                
                " . $this->getJoin('proc_main_proc_type', 'pm') . "
                " . $this->getJoin('proceeding_type', 'pmpt') . "
                " . $this->getJoin('proc_subtype_list', 'pmpt') . "
                " . $this->getJoin('proceeding_subtype', 'psl') . "
                " . $this->getJoin('proceeding_category', 'pm') . "
                " . $this->getJoin('proc_main_practicearea', 'pm') . "
                " . $this->getJoin('dec_hearing', 'dm') . "
                " . $this->getJoin('procs_in_dec_hearing', 'dh') . "
                " . $this->getJoin('dec_hearing_party', 'pidh') . "
                " . $this->getJoin('outcome', 'dhp') . "
                " . $this->getJoin('related_proceedings', 'dh') . "
                " . $this->getJoin('proceeding_dec_counsel', 'ppt') . "
                " . $this->getJoin('proc_subtype_para_list', 'pm') . "
                WHERE 1 = 1
                " . $where . "
                GROUP BY dm.dec_id
                " . $order . "
                " . $limit . "
        ;";
    }

    // Query for specific judge (average stat Dec Tat)
    protected function getQueryForAvgSpecificJudge($where)
    {
        return "
            SELECT ROUND(AVG(A.dectat), 2) AS average, COUNT(A.decid) AS dec_num
            FROM (
                SELECT dm.dec_id AS decid, dm.offline_document_name, dm.decname,
                    dm.dec_date AS decdate, dhd.hearing_date_end AS heardate,
                    DATEDIFF(dm.dec_date, MAX(dhd.hearing_date_end)) AS dectat,
                    cjm.cj_id AS cjid, cjm.cj_fn AS cjfn, cjm.cj_mn AS cjmn, cjm.cj_ln AS cjln                        
                FROM dec_hearing_date dhd
                " . $this->getJoin('dec_main', 'dhd', true) . "                
                " . $this->getJoin('dec_pres_auth', 'dm', true) . "
                " . $this->getJoin('cj_history', 'dpa', true) . "
                " . $this->getJoin('cj_main', 'cjh', true) . "
                " . $this->getJoin('dec_report_ready', 'dm') . "
                " . $this->getJoin('proc_in_dec', 'dm') . "
                " . $this->getJoin('proceeding_main', 'pid') . "
                " . $this->getJoin('proceeding_party', 'pm') . "
                " . $this->getJoin('proceeding_party_type', 'pp') . "
                " . $this->getJoin('party_type', 'ppt') . "                
                " . $this->getJoin('proc_main_proc_type', 'pm') . "
                " . $this->getJoin('proceeding_type', 'pmpt') . "
                " . $this->getJoin('proc_subtype_list', 'pmpt') . "
                " . $this->getJoin('proceeding_category', 'pm') . "
                " . $this->getJoin('proc_main_practicearea', 'pm') . "
                " . $this->getJoin('dec_hearing', 'dm') . "
                " . $this->getJoin('procs_in_dec_hearing', 'dh') . "
                " . $this->getJoin('dec_hearing_party', 'pidh') . "
                " . $this->getJoin('outcome', 'dhp') . "
                " . $this->getJoin('related_proceedings', 'dh') . "
                " . $this->getJoin('proceeding_dec_counsel', 'ppt') . "
                " . $this->getJoin('proc_subtype_para_list', 'pm') . "
                WHERE 1 = 1
                " . $where . "
                GROUP BY dm.dec_id
            ) AS A
        ;";
    }

    // Get conditions for "WHERE" statement (All reports)
    protected function getCommonReportConditions($data, $fields)
    {
        $where = '';
        $court = '';
        $prArea = '';
        $decDate = '';
        $reportReady = '';

        foreach ($data as $item => $value) {
            if (in_array($item, $fields) && $value != '') {

                if (is_array($value)) {
                    $value = implode(',', $value);

                } elseif (is_string($value)) {
                    // Check values 
                    $value = explode(',', $value);
                    $value = array_map(function($v) {
                        return (int)trim($v);
                    }, $value);
                    $value = implode(',', $value);

                } else {
                    $value = (int)$value;
                }

                switch ($item) {
                    // Court
                    case 'court':
                        $court = "AND dm.court_type_id = " . (int)$value . " -- court" . PHP_EOL;
                        $where .= $court;
                        break;

                    // Practice area
                    case 'practice_area':
                        $this->addTableToJoinList(['proceeding_main', 'proc_main_practicearea']);

                        $where .= "AND pmp.practice_area_id = " . (int)$value . " -- practice_area" . PHP_EOL;
                        break;

                    // proceeding_type (Case Category)
                    case 'proceeding_type':
                        $this->addTableToJoinList(['proceeding_main', 'proc_main_proc_type']);

                        $prArea = "AND pm.proc_type_id IN (" . $value . ") -- proceeding_type (Case Category)" . PHP_EOL;
                        $where .= $prArea;
                        break;

                    // proceeding_subtype (Case Category)
                    case 'proceeding_subtype':
                        $this->addTableToJoinList(['proc_subtype_list', 'proc_main_proc_type', 'proceeding_main']);

                        $where .= "AND psl.proc_subtype_id IN (" . $value . ") -- proceeding_subtype (Case Category)" . PHP_EOL;
                        break;

                    // claim_type
                    case 'claim_type':
                        $this->addTableToJoinList(['dec_hearing']);

                        $where .= "AND dh.rel_proceeding_id IN (" . $value . ") -- claim_type" . PHP_EOL;
                        break;

                    // hearing_type (Hearings)
                    case 'hearing_type':
                        $this->addTableToJoinList(['dec_hearing']);

                        $where .= "AND dh.hearing_type IN (" . $value . ") -- hearing_type (Hearings)" . PHP_EOL;
                        break;

                    // hearing_subtype (Hearings)
                    case 'hearing_subtype':
                        $this->addTableToJoinList(['dec_hearing']);

                        $where .= "AND dh.hearing_subtype IN (" . $value . ") -- hearing_subtype (Hearings)" . PHP_EOL;
                        break;

                    // hearing_mode (Mode of Hearing)
                    case 'hearing_mode':
                        $this->addTableToJoinList(['dec_hearing_date']);

                        $where .= "AND dhd.mode_of_hearing_id IN (" . $value . ") -- hearing_mode (Mode of Hearing)" . PHP_EOL;
                        break;

                    // party_type
                    case 'party_type':
                        if ($value != 0) {
                            $this->addTableToJoinList(['party_type', 'proceeding_party_type', 'proceeding_party']);

                            $where .= "AND pt.party_type_id IN (" . $value . ") -- party_type" . PHP_EOL;
                        }

                        break;

                    // individual_company
                    case 'individual_company':
                        if ($value != 2) {
                            $this->addTableToJoinList(['proceeding_party']);

                            if ($value == 1) {
                                $where .= "AND pp.is_company = 1 -- individual_company" . PHP_EOL;
                            } else {
                                $where .= "AND pp.is_company = 0 -- individual_company" . PHP_EOL;
                            }
                        }

                        break;

                    // judge
                    case 'judge':
                        if ($value != 0) {
                            $this->addTableToJoinList(['cj_history', 'dec_pres_auth']);

                            $where .= "AND cjh.cj_id IN (" . $value . ") -- judge" . PHP_EOL;
                        }

                        break;

                    // case_tags
                    case 'case_tags':
                        $this->addTableToJoinList(['proc_subtype_para_list']);

                        $where .= "AND pspl.proc_st_para_id IN (" . $value . ") -- case_tags" . PHP_EOL;
                        break;

                    // outcome
                    case 'outcome':
                        $this->addTableToJoinList([
                            'outcome',
                            'dec_hearing',
                            'dec_hearing_party',
                            'proceeding_party',
                            'proceeding_party_type',
                            'procs_in_dec_hearing'
                        ]);

                        $where .= "AND o.outcome_id IN (" . $value . ") -- outcome" . PHP_EOL;
                        break;

                    // moving_party
                    case 'moving_party':
                        if ($value != 2) {
                            $this->addTableToJoinList([
                                'dec_hearing_party',
                                'proceeding_party_type',
                                'procs_in_dec_hearing'
                            ]);

                            if ($value == 1) {
                                $where .= "AND dhp.init_res_party = 1 -- moving_party" . PHP_EOL;
                            } else {
                                $where .= "AND dhp.init_res_party = 0 -- moving_party" . PHP_EOL;
                            }
                        }

                        break;

                    // proceeding_category (Proceeding Type)
                    case 'proceeding_category':
                        $this->addTableToJoinList(['proceeding_main']);

                        $where .= "AND pm.proc_cat_id IN (
                            SELECT proceeding_category.proc_cat_id FROM proceeding_category
                            WHERE proceeding_category.link_proc_cat IN (
                                SELECT proceeding_category.link_proc_cat
                                FROM proceeding_category
                                WHERE proceeding_category.proc_cat_id IN (" . $value . ")
                            )
                        ) -- proceeding_category (Proceeding Type)" . PHP_EOL;

                        break;
                }
            }
        }

        // Case Law specific setting
        if (in_array('report_ready', $fields)) {
            $this->addTableToJoinList(['dec_report_ready']);

            $reportReady = "AND drr.report_ready = 1 AND drr.report_subtype_id = 2" . PHP_EOL;
            $where .= $reportReady;
        }

        // decision_date
        if (in_array('decision_date', $fields) && ($data->start_date && $data->end_date)) {
            $decDate = "AND dm.dec_date BETWEEN '" . $data->start_date . "' AND '" . $data->end_date . "' -- decision_date" . PHP_EOL;
            $where .= $decDate;
        }

        // hearing_date
        if (in_array('hearing_date', $fields) && ($data->start_hear && $data->end_hear)) {
            $this->addTableToJoinList(['dec_hearing_date']);

            $where .= "AND (dhd.hearing_date_start BETWEEN '" . $data->start_hear . "' AND '" . $data->end_hear . "'" . PHP_EOL;
            $where .= "OR dhd.hearing_date_end BETWEEN '" . $data->start_hear . "' AND '" . $data->end_hear . "') -- hearing_date" . PHP_EOL;
        }

        // self
        if (in_array('self', $fields)) {
            if ($data->self != 2) {
                $this->addTableToJoinList([
                    'proceeding_dec_counsel',
                    'proceeding_party',
                    'proceeding_party_type'
                ]);

                if ($data->self == 1) {
                    $where .= "AND pdc.self_rep = 1 -- self (Self-Reps Only)" . PHP_EOL;
                } elseif ($data->self === 0) {
                    $where .= "AND pdc.self_rep = 0
                    AND dm.dec_id IN (
                        SELECT dm.dec_id FROM dec_main AS dm
                        LEFT JOIN dec_report_ready AS drr
                            ON dm.dec_id = drr.dec_id
                        LEFT JOIN proc_in_dec AS pid
                            ON dm.dec_id = pid.dec_id
                        LEFT JOIN proceeding_main AS pm
                            ON pm.proc_id = pid.proc_id
                        LEFT JOIN proceeding_party AS pp
                            ON pp.proc_id = pm.proc_id
                        LEFT JOIN proceeding_party_type AS ppt
                            ON ppt.proc_party_id = pp.proc_party_id
                        LEFT JOIN proceeding_dec_counsel AS pdc
                            ON pdc.proc_party_type_id = ppt.proc_party_type_id
                        WHERE 1 = 1
                        " . $court . $prArea . $reportReady . $decDate . "
                        GROUP BY dec_id
                        HAVING COUNT(DISTINCT(pdc.self_rep)) = 1
                ) -- self (Exclude Self-Reps)" . PHP_EOL;
                }
            }
        }

        // appeals
        if (in_array('appeals', $fields)) {
            if ($data->appeals != 2) {
                $this->addTableToJoinList(['dec_hearing']);

                if ($data->appeals == 1) {
                    $where .= "AND dh.is_appeal = 1 -- appeals (Appeals Only)" . PHP_EOL;
                } elseif ($data->appeals === 0) {
                    $where .= "
                        AND dh.is_appeal = 0
                        AND dm.dec_id IN (
                        SELECT dm.dec_id FROM dec_main AS dm
                        LEFT JOIN dec_report_ready AS drr
                            ON dm.dec_id = drr.dec_id
                        LEFT JOIN dec_hearing AS dh
                            ON dh.dec_id = dm.dec_id
                        WHERE 1 = 1
                        " . $court . $prArea . $reportReady . $decDate . "
                        GROUP BY dec_id
                        HAVING COUNT(DISTINCT(dh.is_appeal)) = 1
                    ) -- appeals (Exclude Appeals)" . PHP_EOL;
                }
            }
        }

        // appeals (Outcome Report doesn't needs additional subquery)
        if (in_array('outcome_appeals', $fields)) {
            if ($data->appeals != 2) {
                $this->addTableToJoinList(['dec_hearing']);

                if ($data->appeals == 1) {
                    $where .= "AND dh.is_appeal = 1 -- appeals (Appeals Only)" . PHP_EOL;
                } elseif ($data->appeals == 0) {
                    $where .= "AND dh.is_appeal = 0 -- self (Exclude Appeals)" . PHP_EOL;
                }
            }
        }

        return $where;
    }

    // --------------------

    // Get the number of judges with an average turnaround time and split them by ranges
    public function getDecTatRanges($where)
    {
        $query = "
            SELECT COUNT(CASE WHEN B.average <= 25 THEN 1 END) as cnt0_25, -- 0 - 25
              COUNT(CASE WHEN B.average >= 25.01 AND B.average <= 50 THEN 1 END) as cnt25_50, -- 25.01 - 50
              COUNT(CASE WHEN B.average >= 50.01 AND B.average <= 75 THEN 1 END) as cnt50_75, -- 50.01 - 75
              COUNT(CASE WHEN B.average >= 75.01 AND B.average <= 100 THEN 1 END) as cnt75_100, -- 75.01 - 100
              COUNT(CASE WHEN B.average > 100 THEN 1 END) as cnt100 -- 100+        
            FROM (            
              SELECT 
                A.cjid, ROUND(
                  AVG(
                    DATEDIFF(decdate, heardate)
                  ), 2
                ) AS average  
              FROM (
                SELECT dm.dec_id AS decid, dm.dec_date AS decdate, 
                  MAX(dhd.hearing_date_end) AS heardate, cjm.cj_id AS cjid      
                FROM dec_hearing_date dhd               
                " . $this->getJoin('dec_main', 'dhd', true) . "
                " . $this->getJoin('dec_pres_auth', 'dm', true) . "
                " . $this->getJoin('cj_history', 'dpa', true) . "
                " . $this->getJoin('cj_main', 'cjh', true) . "
                " . $this->getJoin('dec_report_ready', 'dm') . "
                " . $this->getJoin('proc_in_dec', 'dm') . "
                " . $this->getJoin('proceeding_main', 'pid') . "
                " . $this->getJoin('proceeding_party', 'pm') . "
                " . $this->getJoin('proceeding_party_type', 'pp') . "
                " . $this->getJoin('party_type', 'ppt') . "                
                " . $this->getJoin('proc_main_proc_type', 'pm') . "
                " . $this->getJoin('proceeding_type', 'pmpt') . "
                " . $this->getJoin('proc_subtype_list', 'pmpt') . "
                " . $this->getJoin('proceeding_category', 'pm') . "
                " . $this->getJoin('proc_main_practicearea', 'pm') . "
                " . $this->getJoin('proceeding_dec_counsel', 'ppt') . "
                " . $this->getJoin('dec_hearing', 'dm') . "
                " . $this->getJoin('related_proceedings', 'dh') . "
                " . $this->getJoin('procs_in_dec_hearing', 'dh') . "
                " . $this->getJoin('dec_hearing_party', 'pidh') . "
                " . $this->getJoin('outcome', 'dhp') . "
                " . $this->getJoin('proc_subtype_para_list', 'pm') . "
                WHERE 1 = 1
                " . $where . "
                GROUP BY dm.dec_id
              ) AS A 
              GROUP BY A.cjid 
              HAVING COUNT(A.decid) > 10
            ) AS B
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    // --------------------

    public function getDecisionDateRange()
    {
        $query = "
            SELECT MIN(dec_date) AS min_date, MAX(dec_date) AS max_date
            FROM dec_main
            WHERE inactive = 0
              AND dec_date != '0000-00-00'
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    public function getHearingAverageTat($settings)
    {
        if ($settings['subtype']) {
            $id = "AND dh.hearing_subtype = " . (int)$settings['id'];
        } else {
            $id = "AND dh.hearing_type = " . (int)$settings['id'];
        }

        $query = "
            SELECT COUNT(A.hear_id) AS hearing_count, 
              ROUND(AVG(DATEDIFF(decdate, heardate)), 2) AS average
            FROM (
                SELECT 
                dh.dec_hearing_id as hear_id,
                  dm.dec_date AS decdate, 
                  MAX(dhd.hearing_date_end) AS heardate
                FROM dec_hearing_date dhd 
                INNER JOIN dec_main AS dm 
                  ON dm.dec_id = dhd.dec_id 
                  AND dm.inactive = 0 
                INNER JOIN dec_pres_auth AS dpa 
                  ON dpa.dec_id = dm.dec_id 
                  AND dpa.inactive = 0 
                LEFT JOIN proc_in_dec AS pid 
                  ON pid.dec_id = dm.dec_id 
                  AND pid.inactive = 0 
                INNER JOIN procs_in_dec_hearing AS pidh 
                  ON pidh.procs_in_dec_id = pid.proc_in_dec_id 
                  AND pidh.inactive = 0 
                INNER JOIN dec_hearing AS dh 
                  ON dh.dec_hearing_id = pidh.dec_hearing_id 
                  AND dh.inactive = 0
                LEFT JOIN proceeding_main AS pm 
                  ON pm.proc_id = pid.proc_id 
                  AND pm.inactive = 0                  
                LEFT JOIN proc_main_practicearea AS pmp
                  ON pmp.proc_id = pm.proc_id 
                  AND pmp.inactive = 0                  
                WHERE dm.court_type_id = " . (int)$settings['court'] . "
                    AND pmp.practice_area_id = " . (int)$settings['practice_area'] . "
                    AND dm.dec_date BETWEEN '" . $settings['start'] . "' AND NOW()
                    " . $id . "
                GROUP BY 
                  dh.dec_hearing_id
            ) AS A;
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    public function getHearingDecisions($settings)
    {
        if ($settings['subtype']) {
            $id = "AND dh.hearing_subtype = " . (int)$settings['id'];
        } else {
            $id = "AND dh.hearing_type = " . (int)$settings['id'];
        }

        $query = "
            SELECT dm.dec_id,
              dm.decname, 
              dm.dec_date, 
              dm.citation_no, 
              dm.offline_document_name, 
              prt.proc_type_name, 
              prst.proc_subtype_name,
              DATEDIFF(dm.dec_date, MAX(dhd.hearing_date_end)) AS dectat,
              GROUP_CONCAT(
                DISTINCT CONCAT (
                  COALESCE(cjt.cj_type_name, ''), ' ', 
                  COALESCE(cjm.cj_fn, ''), ' ', 
                  COALESCE(cjm.cj_mn, ''), ' ', 
                  COALESCE(cjm.cj_ln, '')	
                ) SEPARATOR ', '
              ) as judges,
              GROUP_CONCAT(
                CONCAT (
                  COALESCE(o.outcome_name, ''), '=>',
                  COALESCE(pt.party_type_id, '')
                ) SEPARATOR ','
            ) as parties            
            FROM proc_in_dec pid 
            INNER JOIN proceeding_main AS pm 
              ON pm.proc_id = pid.proc_id 
              AND pm.inactive = 0
            INNER JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id 
              AND pmp.inactive = 0
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
            INNER JOIN party_type AS pt 
              ON pt.party_type_id = ppt.party_type_id 
              AND pt.inactive = 0 
            INNER JOIN outcome AS o 
              ON o.outcome_id = dhp.outcome_id 
              AND o.inactive = 0
            LEFT JOIN proc_main_proc_type AS pmpt 
              ON pmpt.proc_id = pm.proc_id 
              AND pmpt.inactive = 0
            LEFT JOIN proceeding_type AS prt 
              ON prt.proc_type_id = pmpt.proc_main_proc_type_id 
              AND prt.inactive = 0
            LEFT JOIN proc_subtype_list AS psl 
              ON psl.proc_main_proc_type_id = pmpt.proc_main_proc_type_id 
              AND psl.inactive = 0
            LEFT JOIN proceeding_subtype AS prst 
              ON prst.proc_subtype_id = psl.proc_subtype_id 
              AND prst.inactive = 0 
            LEFT JOIN dec_pres_auth AS dpa 
              ON dpa.dec_id = dm.dec_id 
              AND dpa.inactive = 0 
            LEFT JOIN cj_history AS cjh 
              ON cjh.cj_history_id = dpa.cj_history_id 
              AND cjh.inactive = 0 
            LEFT JOIN cj_main AS cjm 
              ON cjm.cj_id = cjh.cj_id 
              AND cjm.inactive = 0 
            LEFT JOIN cj_type AS cjt 
              ON cjt.cj_type_id = cjh.cj_type_id 
              AND cjt.inactive = 0 
            LEFT JOIN dec_hearing_date AS dhd 
              ON dhd.dec_id = dm.dec_id 
              AND dhd.inactive = 0 
            WHERE pid.inactive = 0
                AND dhp.init_res_party = 1
                AND dm.court_type_id = " . (int)$settings['court'] . "
                AND pmp.practice_area_id = " . (int)$settings['practice_area'] . "
                AND dm.dec_date BETWEEN '" . $settings['start'] . "' AND NOW()
                " . $id . "
            GROUP BY dm.dec_id
            ORDER BY dm.dec_date DESC
        ";

        return $this->runQuery($query, false, __FUNCTION__);
    }

    public function getOutcomeBreakdowns($settings)
    {
        if ($settings['subtype']) {
            $id = "AND dh.hearing_subtype = " . (int)$settings['id'];
        } else {
            $id = "AND dh.hearing_type = " . (int)$settings['id'];
        }

        $query = "
            SELECT C.outcome,
              SUM(weight) AS total
            FROM (
                SELECT A.outcome,                                  
                  A.outcome_count / B.party_type_count AS weight 
                FROM (
                    (
                      SELECT dm.dec_id AS decid, 
                        dh.dec_hearing_id AS hearid,
                        o.outcome_name AS outcome,
                        COUNT(dhp.outcome_id) AS outcome_count 
                      FROM proc_in_dec pid 
                      INNER JOIN proceeding_main AS pm 
                        ON pm.proc_id = pid.proc_id 
                        AND pm.inactive = 0
                      INNER JOIN proc_main_practicearea AS pmp
                        ON pmp.proc_id = pm.proc_id 
                        AND pmp.inactive = 0
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
                        AND dm.inactive = 0 -- party_type
                      INNER JOIN outcome AS o 
                        ON o.outcome_id = dhp.outcome_id 
                        AND o.inactive = 0           
                      WHERE pid.inactive = 0
                        AND dm.court_type_id = " . (int)$settings['court'] . "
                        AND pmp.practice_area_id = " . (int)$settings['practice_area'] . "
                        AND dm.dec_date BETWEEN '" . $settings['start'] . "' AND NOW()
                        " . $id . "
                      GROUP BY dm.dec_id, dh.dec_hearing_id, ppt.party_type_id
                    ) AS A 
                    JOIN (
                      SELECT dm.dec_id AS decid, 
                        dh.dec_hearing_id AS hearid,
                        COUNT(ppt.party_type_id) AS party_type_count 
                      FROM proc_in_dec pid 
                      INNER JOIN proceeding_main AS pm 
                        ON pm.proc_id = pid.proc_id 
                        AND pm.inactive = 0
                      INNER JOIN proc_main_practicearea AS pmp
                        ON pmp.proc_id = pm.proc_id 
                        AND pmp.inactive = 0
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
                        AND dm.inactive = 0 -- party_type
                      INNER JOIN outcome AS o 
                        ON o.outcome_id = dhp.outcome_id 
                        AND o.inactive = 0
                    WHERE pid.inactive = 0
                        AND dm.court_type_id = " . (int)$settings['court'] . "
                        AND pmp.practice_area_id = " . (int)$settings['practice_area'] . "
                        AND dm.dec_date BETWEEN '" . $settings['start'] . "' AND NOW()
                        " . $id . "
                    GROUP BY dm.dec_id, dh.dec_hearing_id
                    ) AS B 
                      ON A.decid = B.decid 
                      AND A.hearid = B.hearid
                  )
              ) AS C 
            GROUP BY C.outcome DESC
        ";

        return $this->runQuery($query, false, __FUNCTION__);
    }

    public function getOutcomeTotals($settings)
    {
        if ($settings['subtype']) {
            $id = "AND dh.hearing_subtype = " . (int)$settings['id'];
        } else {
            $id = "AND dh.hearing_type = " . (int)$settings['id'];
        }

        $query = "
            SELECT SUM(weight) as total 
            FROM (
                SELECT A.decid AS decid, 
                  A.hearid, 
                  A.outcome_count / B.party_type_count AS weight 
                FROM (
                    (
                      SELECT dm.dec_id AS decid, 
                        dh.dec_hearing_id AS hearid,
                        COUNT(dhp.outcome_id) AS outcome_count 
                      FROM proc_in_dec pid 
                      INNER JOIN proceeding_main AS pm 
                        ON pm.proc_id = pid.proc_id 
                        AND pm.inactive = 0
                      INNER JOIN proc_main_practicearea AS pmp
                        ON pmp.proc_id = pm.proc_id 
                        AND pmp.inactive = 0
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
                        AND dm.inactive = 0 -- party_type
                      INNER JOIN outcome AS o 
                        ON o.outcome_id = dhp.outcome_id 
                        AND o.inactive = 0
                      WHERE pid.inactive = 0
                        AND dm.court_type_id = " . (int)$settings['court'] . "
                        AND pmp.practice_area_id = " . (int)$settings['practice_area'] . "
                        AND dm.dec_date BETWEEN '" . $settings['start'] . "' AND NOW()
                        " . $id . "
                      GROUP BY dm.dec_id, dh.dec_hearing_id, ppt.party_type_id
                    ) AS A 
                    INNER JOIN (
                      SELECT dm.dec_id AS decid, 
                        dh.dec_hearing_id AS hearid,
                        COUNT(ppt.party_type_id) AS party_type_count 
                      FROM proc_in_dec pid 
                      INNER JOIN proceeding_main AS pm 
                        ON pm.proc_id = pid.proc_id 
                        AND pm.inactive = 0
                      INNER JOIN proc_main_practicearea AS pmp
                        ON pmp.proc_id = pm.proc_id 
                        AND pmp.inactive = 0
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
                      WHERE pid.inactive = 0
                        AND dm.court_type_id = " . (int)$settings['court'] . "
                        AND pmp.practice_area_id = " . (int)$settings['practice_area'] . "
                        AND dm.dec_date BETWEEN '" . $settings['start'] . "' AND NOW()
                        " . $id . "
                      GROUP BY 
                        dm.dec_id, 
                        dh.dec_hearing_id
                    ) AS B 
                      ON A.decid = B.decid 
                      AND A.hearid = B.hearid
                  )
              ) AS C
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    public function getJudgesWithMostNumberOfHearings($settings)
    {
        if ($settings['subtype']) {
            $id = "AND dh.hearing_subtype = " . (int)$settings['id'];
        } else {
            $id = "AND dh.hearing_type = " . (int)$settings['id'];
        }

        $query = "
            SELECT cjm.cj_id,
              COUNT(DISTINCT dh.dec_hearing_id) AS hearing_count,
              COUNT(DISTINCT dm.dec_id) AS decision_count, -- shown in the table
              CONCAT (
                COALESCE(cjt.cj_type_name, ''), 
                ' ', 
                COALESCE(cjm.cj_fn, ''), 
                ' ', 
                COALESCE(cjm.cj_mn, ''), 
                ' ', 
                COALESCE(cjm.cj_ln, '')
              ) AS judge
            FROM proc_in_dec pid 
            INNER JOIN proceeding_main AS pm 
              ON pm.proc_id = pid.proc_id 
              AND pm.inactive = 0
            INNER JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id 
              AND pmp.inactive = 0
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
            INNER JOIN party_type AS pt 
              ON pt.party_type_id = ppt.party_type_id 
              AND pt.inactive = 0 
            INNER JOIN outcome AS o 
              ON o.outcome_id = dhp.outcome_id 
              AND o.inactive = 0
            LEFT JOIN proc_main_proc_type AS pmpt 
              ON pmpt.proc_id = pm.proc_id 
              AND pmpt.inactive = 0
            LEFT JOIN proceeding_type AS prt 
              ON prt.proc_type_id = pmpt.proc_main_proc_type_id 
              AND prt.inactive = 0
            LEFT JOIN proc_subtype_list AS psl 
              ON psl.proc_main_proc_type_id = pmpt.proc_main_proc_type_id 
              AND psl.inactive = 0
            LEFT JOIN proceeding_subtype AS prst 
              ON prst.proc_subtype_id = psl.proc_subtype_id 
              AND prst.inactive = 0 
            LEFT JOIN dec_pres_auth AS dpa 
              ON dpa.dec_id = dm.dec_id 
              AND dpa.inactive = 0 
            LEFT JOIN cj_history AS cjh 
              ON cjh.cj_history_id = dpa.cj_history_id 
              AND cjh.inactive = 0 
            LEFT JOIN cj_main AS cjm 
              ON cjm.cj_id = cjh.cj_id 
              AND cjm.inactive = 0 
            LEFT JOIN cj_type AS cjt 
              ON cjt.cj_type_id = cjh.cj_type_id 
              AND cjt.inactive = 0 
            LEFT JOIN dec_hearing_date AS dhd 
              ON dhd.dec_id = dm.dec_id 
              AND dhd.inactive = 0 
            WHERE pid.inactive = 0 
                AND cjm.cj_id IS NOT NULL
                AND dm.court_type_id = " . (int)$settings['court'] . "
                AND pmp.practice_area_id = " . (int)$settings['practice_area'] . "
                AND dm.dec_date BETWEEN '" . $settings['start'] . "' AND NOW()
                " . $id . "
            GROUP BY cjh.cj_id 
            ORDER BY decision_count DESC, cjm.cj_fn ASC
            LIMIT 10
        ";

        return $this->runQuery($query, false, __FUNCTION__);
    }

    // --------------------

    public function getHearingDateRange($data)
    {
        $query = "
            SELECT MIN(dhd.hearing_date_start) AS min, MAX(dhd.hearing_date_end) AS max
            FROM dec_hearing_date dhd
            INNER JOIN dec_main dm
              ON dm.dec_id = dhd.dec_id
              AND dm.inactive = 0
            INNER JOIN proc_in_dec pid
              ON pid.dec_id = dm.dec_id
              AND pid.inactive = 0            
            INNER JOIN proceeding_main AS pm 
              ON pid.proc_id = pm.proc_id 
              AND pm.inactive = 0
            INNER JOIN proc_main_practicearea AS pmp
              ON pmp.proc_id = pm.proc_id 
              AND pmp.inactive = 0
            WHERE dm.court_type_id = " . (int)$data->court . "
              AND pmp.practice_area_id = " . (int)$data->practice_area . "
              AND dm.dec_date BETWEEN '" . $data->start_date . "' AND '" . $data->end_date . "'
        ";

        return $this->runQuery($query, true);
    }

    public function getReportSettingsByReport($master, $reportId)
    {
        if ($master == 1) {
            $where = 'master_report_id = ' . (int)$reportId;
        } else {
            $where = 'slave_report_id = ' . (int)$reportId;
        }

        $query = "
            SELECT dr.*, s.state_id, s.state_name, ct.court_name, pa.practice_area_name
            FROM report_date_range dr
            LEFT JOIN court_type ct
              ON ct.court_id = dr.court_id
            LEFT JOIN state s
              ON s.state_id = ct.state_id
            LEFT JOIN practice_area pa
              ON pa.practice_area_id = dr.practice_area_id
            WHERE " . $where . "
                AND dr.inactive = 0
            ORDER BY s.state_name, ct.court_name
        ";

        return $this->runQuery($query, false);
    }

    public function getReportSettingsByReportCentre($reportCentreId)
    {
        $query = "
            SELECT dr.*, s.state_id, s.state_name, ct.court_name, pa.practice_area_name
            FROM report_centre_date_range dr
            LEFT JOIN court_type ct
              ON ct.court_id = dr.court_id
            LEFT JOIN state s
              ON s.state_id = ct.state_id
            LEFT JOIN practice_area pa
              ON pa.practice_area_id = dr.practice_area_id
            WHERE report_centre_id = " . (int)$reportCentreId . "
                AND dr.inactive = 0
            ORDER BY s.state_name, ct.court_name
        ";

        return $this->runQuery($query, false);
    }

    public function getDefaultGlobalReportSettings()
    {
        $query = "
            SELECT dr.*
            FROM report_date_range dr
            LEFT JOIN court_type ct
              ON ct.court_id = dr.court_id
            WHERE dr.master_report_id = 1
              AND dr.inactive = 0
            LIMIT 1
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    // Get the position on the basis of the last decision
    public function getJudge($judgeId)
    {
        $query = "             
            SELECT cjm.*, dpa.cj_history_id, dpa.dec_id, dm.dec_date, 
              CONCAT (cjt.cj_type_name, ' ', cj_fn, ' ', cj_mn, ' ', cj_ln) as judgeName
            FROM cj_main cjm
            INNER JOIN cj_history cjh
              ON cjh.cj_id = cjm.cj_id
            INNER JOIN dec_pres_auth dpa
              ON cjh.cj_history_id = dpa.cj_history_id
            INNER JOIN cj_type cjt
              ON cjh.cj_type_id = cjt.cj_type_id
            INNER JOIN dec_main dm
              ON dm.dec_id = dpa.dec_id
            WHERE cjh.cj_id = " . (int) $judgeId . "
            ORDER BY dm.dec_date DESC
            LIMIT 1
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    // Get report settings
    public function getReportSettings($params, $sub = false)
    {
        $reportCentreId = isset($params['report_centre_id']) ? $params['report_centre_id'] : false;
        $master = isset($params['master']) ? $params['master'] : false;
        $reportId = isset($params['report_id']) ? $params['report_id'] : false;
        $courtId = isset($params['court']) ? $params['court'] : false;
        $practiceAreaId = isset($params['practice_area']) ? $params['practice_area'] : false;

        if ($reportCentreId) {
            $reportSettings = $this->getReportSettingsByReportCentre($reportCentreId);
        } else {
            $reportSettings = $this->getReportSettingsByReport($master, $reportId);

            // If there are no results for the slave report - try to use the settings from the master
            if (!$master && !$reportSettings) {
                $reportSettings = $this->getReportSettingsByReport(1, $reportId);
            }
        }

        // -------------------

        $defaultRange = false;
        foreach ($reportSettings as $row) {
            if (isset($row['default_range']) && $row['default_range'] == 1) {
                $defaultRange = $row;
            }
        }

        if ($defaultRange) {
            $courtId = $defaultRange['court_id'];
            $practiceAreaId = $defaultRange['practice_area_id'];
        }

        // -------------------

        $practiceArea = array();
        $dates = array();
        $reportDateRange = false;

        $stateCourts = array();

        // Global report settings
        foreach ($reportSettings as $row) {
            if (!isset($stateCourts[$row['state_id']])) {
                $stateCourts[$row['state_id']] = array(
                    'name' => $row['state_name'],
                    'courts' => array()
                );
            }

            $stateCourts[$row['state_id']]['courts'][$row['court_id']] = $row['court_name'];

            // Practice area for court
            if ($courtId && ($row['court_id'] == $courtId)) {
                $practiceArea[$row['practice_area_id']] = $row['practice_area_name'];

                // Dates for practice area
                if ($practiceAreaId && $row['practice_area_id'] == $practiceAreaId
                    && ($row['court_id'] == $courtId)) {

                    if (empty($dates)) {
                        if (isset($row['report_date_range_id'])) {
                            $reportDateRange = $row['report_date_range_id'];
                        }

                        $dates = array(
                            'min_date' => $row['min_date'],
                            'max_date' => $row['max_date'],
                            'start_date' => $this->checkForValidDate($row['start_date'])
                                ? $row['start_date'] : $row['min_date'],
                            'end_date' => $this->checkForValidDate($row['end_date'])
                                ? $row['end_date'] : $row['max_date'],
                        );
                    }
                }
            }
        }

        // -------------------

        // Filter practice area options by the selected options in the report settings
        $field = $this->getReportField('practice-area', $reportId, $master);

        $practiceArea = $this->filterFieldOptions($practiceArea, $field);

        // Sort an array in alphabetical order and maintain index association
        asort($practiceArea);

        if (isset($params['sort'])) {
            $sort = array();
            foreach ($practiceArea as $k => $v) {
                $sort[] = array('id' => $k, 'name' => $v);
            }

            $practiceArea = $sort;
        }

        // -------------------

        if ($defaultRange) {
            $dates = array(
                'min_date' => $defaultRange['min_date'],
                'max_date' => $defaultRange['max_date'],
                'start_date' => $this->checkForValidDate($defaultRange['start_date'])
                    ? $defaultRange['start_date'] : $defaultRange['min_date'],
                'end_date' => $this->checkForValidDate($defaultRange['end_date'])
                    ? $defaultRange['end_date'] : $defaultRange['max_date'],
            );

        } elseif (count($practiceArea) == 1 && empty($dates) && !$sub) {
            // Get dates if practice area have only 1 option

            if (isset($params['sort'])) {
                $params['practice_area'] = $practiceArea[0]['id'];
            } else {
                $params['practice_area'] = key($practiceArea);
            }

            $result = $this->getReportSettings($params, true);
            $dates = $result['dates'];
        }

        // -------------------

        return array(
            'state_courts' => $stateCourts,
            'report_centre_id' => $reportCentreId,
            'report_date_range_id' => $reportDateRange,
            'master' => $master,
            'report_id' => $reportId,
            'practice_area' => $practiceArea,
            'dates' => $dates,
            'court_id' => $defaultRange ? $defaultRange['court_id'] : $courtId,
            'practice_area_id' => $defaultRange ? $defaultRange['practice_area_id'] : $practiceAreaId,
        );
    }

    public function getStateCourts($courts = array())
    {
        if ($courts) {
            $where = 'AND ct.court_id IN (' . implode(',', $courts) . ')';
        } else {
            $where = '';
        }

        $query = "
            SELECT s.state_id, s.state_name, ct.court_id, ct.court_name
            FROM court_type ct
            LEFT JOIN state s
              ON s.state_id = ct.state_id
            WHERE ct.inactive = 0
              AND ct.court_name != ''
              AND s.state_name != ''
            " . $where . "
            ORDER BY s.state_name, ct.court_name
        ";

        $result = $this->runQuery($query, false, __FUNCTION__);

        $stateCourts = array();
        foreach ($result as $row) {
            if (!isset($stateCourts[$row['state_id']])) {
                $stateCourts[$row['state_id']] = array(
                    'name' => $row['state_name'],
                    'courts' => array()
                );
            }

            $stateCourts[$row['state_id']]['courts'][$row['court_id']] = $row['court_name'];
        }

        return $stateCourts;
    }

    // Filter report options by the selected options in the report settings
    public function getReportField($fieldName, $reportId, $master)
    {
        if ($reportId) {
            if ($master) {
                $query = "
                    SELECT mf.*
                    FROM master_field mf
                    INNER JOIN basic_field bf
                      ON bf.basic_field_id = mf.basic_field_id
                      AND bf.inactive = 0
                    WHERE mf.master_report_id = " . (int)$reportId . "
                      AND bf.system_name = '" . $fieldName . "'
                      AND mf.inactive = 0
                ";
            } else {
                $query = "
                    SELECT sf.* 
                    FROM slave_field sf
                    INNER JOIN master_field mf
                      ON mf.master_field_id = sf.master_field_id
                      AND mf.inactive = 0
                    INNER JOIN basic_field bf
                      ON bf.basic_field_id = mf.basic_field_id
                      AND bf.inactive = 0
                    WHERE sf.slave_report_id = " . (int)$reportId . "
                      AND bf.system_name = '" . $fieldName . "'
                      AND sf.inactive = 0
                ";
            }

            return $this->runQuery($query, true);
        }

        return false;
    }

    public function filterFieldOptions($options, $field)
    {
        if ($field) {
            if (strlen($field['options'])) {
                $filterOptions = json_decode($field['options']);

                if ($filterOptions) {
                    return array_filter($options,
                        function ($key) use ($filterOptions) {
                            return in_array($key, $filterOptions);
                        }, ARRAY_FILTER_USE_KEY
                    );
                }
            }
        }

        return $options;
    }

    public function getDecisionDetails($id)
    {
        $query = "
            SELECT dm.offline_document_name, dm.decname, dm.dec_date, dm.dec_id,
              pmp.practice_area_id, dm.court_type_id, dm.citation_no,     
              prt.proc_type_name AS case_fact,
              GROUP_CONCAT(
                DISTINCT CONCAT (
                  COALESCE(prst.proc_subtype_name, '')
                ) SEPARATOR ', '
              ) as case_fact_extend,
              GROUP_CONCAT(
                DISTINCT CONCAT (
                  COALESCE(psp.proc_st_para, '')
                ) SEPARATOR ', '
              ) as additional_fact,
              pc.proc_cat_name AS proceeding_type,
              pm.court_file_no AS court_file,
              ct.court_name,
              MAX(dhd.hearing_date_end) AS heardate,    
              DATEDIFF(dm.dec_date, MAX(dhd.hearing_date_end)) AS dec_tat,
              GROUP_CONCAT(
                DISTINCT CONCAT (
                  COALESCE(cjt.cj_type_name, ''), 
                  ' ', 
                  COALESCE(cjm.cj_fn, ''), 
                  ' ', 
                  COALESCE(cjm.cj_mn, ''), 
                  ' ', 
                  COALESCE(cjm.cj_ln, '')
                ) SEPARATOR ', '
              ) as judges 
            FROM dec_main dm
            LEFT JOIN dec_hearing_date AS dhd 
              ON dhd.dec_id = dm.dec_id
              AND dm.inactive = 0
            LEFT JOIN dec_hearing AS dh 
              ON dh.dec_id = dm.dec_id
              AND dh.inactive = 0 
            LEFT JOIN procs_in_dec_hearing AS pidh 
              ON pidh.dec_hearing_id = dh.dec_hearing_id
              AND pidh.inactive = 0 
            LEFT JOIN proc_in_dec AS pid 
              ON pid.proc_in_dec_id = pidh.procs_in_dec_id
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
            LEFT JOIN proceeding_type AS prt 
              ON prt.proc_type_id = pmpt.proc_main_proc_type_id 
              AND prt.inactive = 0  
            LEFT JOIN proc_subtype_list AS psl 
              ON psl.proc_main_proc_type_id = pmpt.proc_main_proc_type_id 
              AND psl.inactive = 0 
            LEFT JOIN proceeding_subtype AS prst 
              ON prst.proc_subtype_id = psl.proc_subtype_id 
              AND prst.inactive = 0 
            LEFT JOIN proc_subtype_para_list AS pspl 
              ON pspl.proc_id = pm.proc_id 
              AND pspl.inactive = 0 
            LEFT JOIN proceeding_subtype_para psp
              ON psp.proc_st_para_id = pspl.proc_st_para_id 
            LEFT JOIN proceeding_category pc
              ON pc.proc_cat_id = pm.proc_cat_id
              AND pc.inactive = 0
            LEFT JOIN dec_pres_auth AS dpa 
              ON dpa.dec_id = dm.dec_id 
              AND dpa.inactive = 0 
            LEFT JOIN cj_history AS cjh 
              ON cjh.cj_history_id = dpa.cj_history_id 
              AND cjh.inactive = 0 
            LEFT JOIN cj_main AS cjm 
              ON cjm.cj_id = cjh.cj_id 
              AND cjm.inactive = 0 
            LEFT JOIN cj_type AS cjt 
              ON cjt.cj_type_id = cjh.cj_type_id 
              AND cjt.inactive = 0
            LEFT JOIN court_type ct
              ON ct.court_id = dm.court_type_id
              AND ct.inactive = 0
            WHERE dm.dec_id = " . (int)$id . "
                AND dm.inactive = 0
            GROUP BY dm.dec_id
        ";

        return $this->runQuery($query, true, __FUNCTION__);
    }

    // Hearings
    public function getDecisionHearings($id)
    {
        $query = "
            SELECT dh.dec_hearing_id, dh.has_jury, 
              dh.is_appeal, hs.hearing_sub_name
            FROM dec_hearing dh
            LEFT JOIN hearing_sub AS hs
              ON hs.hearing_sub_id = dh.hearing_subtype
              AND hs.inactive = 0
            WHERE dh.dec_id = " . (int)$id . "
                AND dh.inactive = 0
        ";

        return $this->runQuery($query, false, __FUNCTION__);
    }

    public function getDecisionHearingDates($id)
    {
        $query = "
            SELECT dhd.hearing_date_start, dhd.hearing_date_end
            FROM dec_hearing_date AS dhd
            WHERE dhd.dec_id = " . (int)$id . "
                AND dhd.inactive = 0
        ";

        return $this->runQuery($query, false, __FUNCTION__);
    }

    public function getDecisionHearingParties($id)
    {
        $query = "
            SELECT pt.party_type_name, pp.party_name_ot,
              o.outcome_name, pidh.dec_hearing_id, dhp.init_res_party
            FROM proc_in_dec pid
            INNER JOIN proceeding_main AS pm 
              ON pm.proc_id = pid.proc_id 
              AND pm.inactive = 0 
            INNER JOIN procs_in_dec_hearing AS pidh 
              ON pidh.procs_in_dec_id = pid.proc_in_dec_id 
              AND pidh.inactive = 0 
            INNER JOIN proceeding_party AS pp 
              ON pp.proc_id = pm.proc_id 
              AND pp.inactive = 0 
            INNER JOIN proceeding_party_type AS ppt 
              ON ppt.proc_party_id = pp.proc_party_id 
              AND ppt.inactive = 0 
            INNER JOIN party_type AS pt 
              ON pt.party_type_id = ppt.party_type_id 
              AND pt.inactive = 0 
            INNER JOIN dec_hearing_party AS dhp 
              ON dhp.procs_in_hearing_id = pidh.procs_in_hearing_id 
              AND dhp.involved_party = ppt.proc_party_type_id 
              AND dhp.inactive = 0
            INNER JOIN outcome AS o 
              ON o.outcome_id = dhp.outcome_id 
              AND o.inactive = 0   
            WHERE pid.dec_id = " . (int)$id . "
              AND pid.inactive = 0
        ";

        return $this->runQuery($query, false, __FUNCTION__);
    }

    // Parties in decision
    public function getDecisionParties($id)
    {
        $query = "
            SELECT pt.party_type_name, pp.party_name_ot, pdc.self_rep,
              GROUP_CONCAT(
                DISTINCT CONCAT (
                  COALESCE(cjm.cj_fn, ''), 
                  ' ', 
                  COALESCE(cjm.cj_mn, ''), 
                  ' ', 
                  COALESCE(cjm.cj_ln, '')
                ) SEPARATOR ', '
              ) AS counsel
            FROM proc_in_dec AS pid     
            LEFT JOIN proceeding_main AS pm 
              ON pm.proc_id = pid.proc_id 
              AND pm.inactive = 0 
            LEFT JOIN proceeding_party AS pp 
              ON pp.proc_id = pm.proc_id 
              AND pp.inactive = 0 
            LEFT JOIN proceeding_party_type AS ppt 
              ON ppt.proc_party_id = pp.proc_party_id 
              AND ppt.inactive = 0 
            LEFT JOIN party_type AS pt 
              ON pt.party_type_id = ppt.party_type_id 
              AND pt.inactive = 0 
            LEFT JOIN proceeding_dec_counsel AS pdc 
              ON pdc.proc_party_type_id = ppt.proc_party_type_id 
              AND pdc.inactive = 0      
            LEFT JOIN cj_history cjh
              ON cjh.cj_history_id = pdc.cj_history_id
              AND cjh.inactive = 0
            LEFT JOIN cj_main cjm
              ON cjm.cj_id = cjh.cj_id
              AND cjm.inactive = 0    
            WHERE pid.dec_id = " . (int)$id . "
                AND pid.inactive = 0
            GROUP BY pp.proc_party_id
        ";

        return $this->runQuery($query, false, __FUNCTION__);
    }

    public function getCourt($id)
    {
        $query = "
            SELECT * FROM court_type
            WHERE inactive = 0
              AND court_id = " . (int)$id . "
        ";

        return $this->getQueryResult($query, true, true);
    }

    // Checks whether the date is valid
    public function checkForValidDate($str)
    {
        if (!$str) return false;

        $arr = explode('-', $str);

        // YYYY-MM-DD => MM-DD-YYYY
        return checkdate($arr[1], $arr[2], $arr[0]);
    }

    public function getUserIp()
    {
        $localIp = $_SERVER['REMOTE_ADDR'];
        $proxy = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;

        return $proxy ? $proxy : $localIp;
    }

    // Save query launched by the user without regard to the total results and pagination
    public function saveQuery($settings)
    {
        $userId = isset($settings['user']) ? $settings['user'] : false;
        $reportId = isset($settings['report_id']) ? $settings['report_id'] : false;
        $deviceId = isset($settings['device_id']) ? $settings['device_id'] : false;
        $data = $settings['data'];

        if ($userId && $deviceId && $reportId && isset($settings['log'])) {
            $session = $this->getCustomerSession($userId, $deviceId);

            if ($session) {
                $fields = array();
                foreach ($data as $k => $v) {
                    if ($v != '') {
                        $fields[$k] = $v;
                    }
                }

                $query = "
                    INSERT INTO query_log(
                      cust_sess_log_id, slave_report_id, where_statement, query_time, inactive
                    ) VALUES (
                      '" . $session->cust_sess_log_id . "', 
                      '" . $reportId . "', 
                      '" . json_encode($fields) . "', 
                      now(), 
                      '0'
                    );
                ";
            }

            $this->getQueryResult($query);
        }
    }

    protected function printQuery($query, $description, $line, $function, $runtime = false)
    {
        if (APP_ENV != 'production') {
            $sqlFormatter = new \Report\Service\SqlFormatter;
            $query = $sqlFormatter::format($query, false);

            $string = '<!-- ' . $description;

            if ($runtime && is_array($runtime)) {
                $runtime = round($runtime[1] - $runtime[0], 3);
                $string .= '(' . $runtime . 's)';
            }

            $string .= '. Line: ' . $line;
            $string .= ', function: ' . $function . PHP_EOL;
            $string .= $query . ' -->' . PHP_EOL;

            echo $string;
        }
    }

    protected function runQuery($query, $singleRow = false, $print = false)
    {
        try {
            if ($print && APP_ENV != 'production') {
                $start = microtime(true);
            }

            $statement = $this->dbAdapter->query($query);
            $results = $statement->execute();

            if ($singleRow) {
                $result = $results->getResource()->fetch();
            } else {
                $result = $results->getResource()->fetchAll();
            }

            if ($print && APP_ENV != 'production') {
                $end = microtime(true);

                $sqlFormatter = new \Report\Service\SqlFormatter;
                $query = $sqlFormatter::format($query, false);
                $runtime = round($end - $start, 3);

                $string = '<!-- ' . $print;
                $string .= '(' . $runtime . 's)' . PHP_EOL;
                $string .= $query . ' -->' . PHP_EOL . PHP_EOL;

                echo $string;
            }

            return $result;

        }  catch (\Exception $e) {
            if (APP_ENV != 'production') {
                $string = '<!-- Error - ' . $e->getMessage() . PHP_EOL;
                $string .= 'in the query: ' . $query . ' -->' . PHP_EOL;
                echo $string . PHP_EOL;
            }

            return false;
        }
    }

    protected function getQueryResult($query, $returnArray = null, $singleRow = false)
    {
        try {
            if ($returnArray) {
                $statement = $this->dbAdapter->query($query);

                $results = $statement->execute();

                if ($singleRow) {
                    return $results->getResource()->fetch();
                }

                return $results->getResource()->fetchAll();
            } else {
                $results = $this->dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);

                if ($results) {
                    return 'success';
                } else {
                    return 'fail';
                }
            }
        }  catch (\Exception $e) {
            if (APP_ENV != 'production') {
                $string = '<!-- Error - ' . $e->getMessage() . PHP_EOL;
                $string .= 'in the query: ' . $query . ' -->' . PHP_EOL;
                echo $string . PHP_EOL;
            }

            return false;
        }
    }

    // Get column join
    protected function getJoin($tableName, $on, $inner = false)
    {
        if (!isset($this->joinList[$tableName])) {
            return ' -- ' . $tableName;
        }

        if (isset($this->tables[$tableName])) {
            $table = $this->tables[$tableName];

            if (isset($table['join'][$on])) {
                $joinOn = $table['join'][$on];

                $join = $inner ? 'INNER' : 'LEFT';
                $join .= ' JOIN ' . $table['name'] . ' AS ' . $table['pr'] . PHP_EOL;

                if (is_array($joinOn)) {
                    $join .= ' ON ' . implode(PHP_EOL . ' AND ', $joinOn);
                } else {
                    $join .= ' ON ' . $joinOn;
                }

                // Is "inactive" column present in the table ?
                if ($table['inactive']) {
                    $join .=  PHP_EOL . ' AND ' . $table['pr'] . '.inactive = 0';
                }

                return $join;

            } else {
                return ' -- JOIN [' . $on . '] IN TABLE [' . $tableName . '] NOT FOUND';
            }

        } else {
            return ' -- TABLE [' . $tableName . '] NOT FOUND';
        }
    }

    protected function addTableToJoinList($tableName)
    {
        if (is_array($tableName)) {
            foreach ($tableName as $name) {
                $this->joinList[$name] = true;
            }

        } elseif (is_string($tableName)) {
            $this->joinList[$tableName] = true;
        }
    }

    protected function getCustomerSession($userId, $deviceId)
    {
        return $this->getSessionTable()->getLastSession($userId, $deviceId);
    }

    protected function getSessionTable()
    {
        if (!$this->sessionTable) {
            $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new \Customer\Model\SessionLog());
            $tableGateway = new \Zend\Db\TableGateway\TableGateway('cust_sess_log', $this->dbAdapter, null, $resultSetPrototype);
            $sessionTable = new \Customer\Model\SessionLogTable($tableGateway);

            $this->sessionTable = $sessionTable;
        }

        return $this->sessionTable;
    }
}