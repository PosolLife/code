<?php
namespace General\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;

use Zend\Db\Sql\Sql\Insert;

class DataHandling
{
    public $dbAdapter = null;

    public function __construct($dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }

    
    public function getAllAnnotations($userId, $docName)
    {
        $query = "SELECT * FROM annotation
                      WHERE userId = $userId AND doc_name = '$docName'";
        $res = $this->getQueryResult($query, true);
        $returnArr = array();
        foreach ($res as $val) {
            $returnArr[] = array('id' => $val['id'],
                'quote' => $val['quote'],
                'ranges' => array(
                    array(
                        'end' => $val['endTag'],
                        'endOffset' => $val['endOffset'],
                        'start' => $val['startTag'],
                        'startOffset' => $val['startOffset']
                    )
                ),
                'text' => $val['text']
            );
        }
        return json_encode($returnArr);


    }

    public function setAnnotation($userID, $docName, $json, $rangers)
    {
        $start = $rangers["start"];
        $end = $rangers['end'];
        $startOffset = $rangers['startOffset'];
        $endOffset = $rangers['endOffset'];
        $text = $json['text'];
        $quote = $json['quote'];
        $query = "INSERT INTO
                  annotation(userId,doc_name, startTag, endTag, startOffset, endOffset,text,quote)
                  VALUES ($userID,'$docName','$start','$end','$startOffset','$endOffset','$text','$quote')";
        $this->getQueryResult($query);
        $id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

        $json['id'] = $id;
        return json_encode($json);
    }

    public function deleteAnnotation($json)
    {
        $id = $json['id'];
        $query = "DELETE FROM annotation WHERE
                   id = $id
";
        $this->getQueryResult($query);
    }

    public function updateAnnotation($json, $rangers)
    {
        $id = $json['id'];
        $text = $json['text'];
        $query = "UPDATE annotation SET
                    text='$text'
                     WHERE
                    id = $id";

        $this->getQueryResult($query);
    }


    public function getCountry($countryID = null, $status = null)
    {

        $where = "";
        if (!empty($countryID)) {
            $where = "WHERE country_id = '" . $countryID . "' ";
            if ($status == "active") {
                $where = $where . " AND inactive= '0' ";
            }
        }

        $statement = $this->dbAdapter->query("SELECT * FROM country
        									  WHERE inactive=0 AND  name != NULL OR name != '' $where
        		                              ORDER BY name");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    public function getCallToBar()
    {
        $statement = $this->dbAdapter->query("
            SELECT * FROM cj_main
            WHERE inactive = 0
            GROUP BY call_to_bar
            ORDER BY call_to_bar DESC
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function universityDropDown()
    {
        $statement = $this->dbAdapter->query("
            SELECT * FROM law_university
            WHERE inactive = 0
            ORDER BY law_school_name DESC
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function getState($countryId = null, $status = null)
    {
        $where = "";

        if (!is_null($countryId)) {
            $where = "WHERE country_id='" . $countryId . "'";

            if ($status == "active") {
                $where .= " AND inactive= '0' ";
            } elseif ($status == "inactive") {
                $where .= " AND inactive= '1' ";
            }
        }

        $statement = $this->dbAdapter->query("
            SELECT *, inactive inactive_state
            FROM state
            " . $where . "
            ORDER BY state_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getStateCity($countryId = null, $status = null)
    {
        $where = "";

        if (!empty($countryId)) {
            $where = "WHERE country_id='" . $countryId . "'";

            if ($status == "active") {
                $where = $where . " AND inactive= '0' ";
            } elseif ($status == "inactive") {
                $where = $where . " AND inactive= '1' ";
            } else {
                $status = null;
            }

        }

        $statement = $this->dbAdapter->query("SELECT *, inactive inactive_state FROM state " . $where . " ORDER BY state_name");
        $results = $statement->execute();


        return $results->getResource()->fetchAll();
    }

    public function getCity($stateId = null, $status)
    {

        if (!empty($stateId)) {
            $where = "WHERE state_id='" . $stateId . "'";

            if ($status == "active") {
                $where = $where . " AND inactive= '0' ";
            } elseif ($status == "inactive") {
                $where = $where . " AND inactive= '1' ";
            } else {
                $status = null;
            }


        }

        $statement = $this->dbAdapter->query("SELECT * FROM city " . $where . " ORDER BY city_name");
        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getCourtType($countryId = null, $stateId = null)
    {
        $where = "";

        if (!is_null($stateId)) {
            $where = "AND country_id='" . $countryId . "' AND state_id='" . $stateId . "'";
        }

        $query = "
            SELECT * FROM court_type
            WHERE  court_name != NULL OR court_name != ''
            AND inactive = 0
            " . $where . "
            ORDER BY court_name
        ";

        $statement = $this->dbAdapter->query($query);
        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getCjMainType($name, $cjMainId = null)
    {
        $where = "";
        if (!empty($name)) {
            $where = ' AND cj_ln LIKE "%'. $name .'%" ';         
        }
        $query = "SELECT * FROM cj_main  WHERE  cj_id != 1 AND cj_ln != ''  AND cj_fn != ''  AND inactive = 0 $where  ORDER BY cj_id";
        
        $statement = $this->dbAdapter->query($query);
        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }
    public function getCjMainTypeAll($cjMainId)
    {
        if(!empty($cjMainId)){
            $where = "AND cjm.cj_id = ".$cjMainId;
        }else{
            $where = "";
        }
        $query = "SELECT cjtt.cj_title_name, cjm.cj_fn,cjm.cj_mn,cjm.cj_ln, cjm.cj_id,cjh.cj_type_id, cjh.cj_history_id, cjt.cj_type_name, lf.law_firm_name, ct.court_name
                    FROM cj_main cjm

				    LEFT JOIN cj_history cjh
				    ON cjm.cj_id = cjh.cj_id
				    LEFT OUTER JOIN cj_title cjtt
				    ON cjtt.cj_title_id = cjh.title_id
				    LEFT OUTER JOIN cj_type cjt
				    ON cjh.cj_type_id = cjt.cj_type_id
				    LEFT OUTER  JOIN law_firm lf
				    ON cjh.law_firm_id = lf.law_firm_id
				    LEFT OUTER JOIN court_type ct
				    ON cjh.court_id = ct.court_id
				    WHERE cjm.cj_id != 1 $where
				    GROUP BY cj_history_id";
        $statement = $this->dbAdapter->query($query);
        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getCourtRegion($courtRegionId)
    {
        $where = "";

        if (!empty($courtRegionId)) {
            $where = "WHERE court_id='" . $courtRegionId . "' ";
        }

        $statement = $this->dbAdapter->query("
            SELECT * FROM court_type ct
            INNER JOIN country c
              ON ct.country_id = c.country_id
            INNER JOIN state s
              ON ct.state_id = s.state_id
            " . $where . "
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    public function getCourtMunList($courtTypeId)
    {
        $where = "";

        if (!empty($courtTypeId)) {
            $where = " AND court_id = '" . $courtTypeId . "'  ";
        }

        $statement = $this->dbAdapter->query("
            SELECT * FROM court_mun
            WHERE inactive = 0
              AND court_mun_id != 1
             " . $where . "
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getLawFirm()
    {
        $where = "";
        $groupBy = "";
        $orderBy = "WHERE  name != NULL OR name != '' ";

        $statement = $this->dbAdapter->query("SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
	    		FROM country c
	    		LEFT JOIN state s
	    		ON c.country_id = s.country_id
	    		$where
	    		$groupBy
	    		$orderBy

	    		");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $arr = array();
        foreach ($countries as $key => $value) {

            $where = "WHERE country_id = '" . $value['country_id'] . "' AND state_id = '" . $value['state_id'] . "'  AND inactive = 0 ";

            $statement = $this->dbAdapter->query("SELECT * FROM law_firm
	    					$where
	    				  	");

            $results = $statement->execute();
            $lawFirms = $results->getResource()->fetchAll();


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'Law_Firms_List' => $lawFirms


            );

        }


        return $arr;
    }

    public function getLawCatalog()
    {

        $statement = $this->dbAdapter->query("
	    			SELECT * FROM cj_history

	    		");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getCourtRegionList($courtId = null)
    {
        $where = "WHERE inactive = 0";

        if (!is_null($courtId)) {
            $where = " AND court_id = '" . $courtId . "'";
        }

        $statement = $this->dbAdapter->query("
            SELECT * FROM court_region
            " . $where . "
            ORDER BY reg_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getProceedingCategory($idCourt = null, $options = null)
    {
        if ($options && isset($options['options']) && count($options['options'])) {
            $where = 'AND proc_cat_id IN (' . implode(',', $options['options']) . ')';
        } else {
            $where = '';
        }

        if (!empty($idCourt)) {
            $query = "
                SELECT * FROM proceeding_category
                WHERE proc_cat_name IS NOT NULL
                  AND inactive = 0 
                  AND court_id = " . (int)$idCourt . "
                " . $where . "
                ORDER BY proc_cat_name
            ";
        } else {
            $query = "
                SELECT pc.*, ct.court_name 
                FROM proceeding_category pc
                LEFT JOIN court_type ct
                  ON ct.court_id = pc.court_id
                WHERE pc.proc_cat_name IS NOT NULL
                  AND pc.inactive = 0
                " . $where . "
                ORDER BY ct.court_name, pc.proc_cat_name
            ";
        }

        $statement = $this->dbAdapter->query($query);

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getProceedingTypeList($practiceAreaId = null)
    {
        $where = "WHERE inactive = 0";

        if ($practiceAreaId != null) {
            $where .= " AND practice_area_id = " . $practiceAreaId;
        }

        $statement = $this->dbAdapter->query("
            SELECT * FROM proceeding_type
            " . $where . "
            ORDER BY proc_type_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getPracticeArea($courtid)
    {
        $statement = $this->dbAdapter->query("
            SELECT * FROM practice_area
            WHERE practice_area_name IS NOT NULL
              AND inactive = 0
              AND practice_area_id != 1
            ORDER BY practice_area_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getProceedingList($data = null)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0 ";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1 ";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT proc_type_id, proc_type_name, practice_area_id, inactive
                                 FROM proceeding_type WHERE proc_type_name != NULL OR proc_type_name != '' {$where}
       ORDER BY proc_type_name ");
        $results = $statement->execute();
        $pt_list = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT practice_area_id, practice_area_name
                                 FROM practice_area WHERE practice_area_name != NULL OR practice_area_name != ''
        ORDER BY practice_area_name ");

        $results = $statement->execute();
        $pa_list = $results->getResource()->fetchAll();

        $count = ['pt_list' => count($pt_list), 'pa_list' => count($pa_list)];

        $sorted_list = array();

        for ($i = 0; $i < $count['pa_list']; $i++) {

            $sorted_list[$i]['practice_area_name'] = $pa_list[$i]['practice_area_name'];
            $sorted_list[$i]['practice_area_id'] = $pa_list[$i]['practice_area_id'];
            $temp_arr = array();

            for ($j = 0; $j < $count['pt_list']; $j++) {

                if ($pt_list[$j]['practice_area_id'] == $pa_list[$i]['practice_area_id']) {
                    $temp_arr[$j]['proc_type_name'] = $pt_list[$j]['proc_type_name'];
                    $temp_arr[$j]['pat_id'] = $pt_list[$j]['proc_type_id'];
                    $temp_arr[$j]['inactive'] = $pt_list[$j]['inactive'];
                }
            }

            $sorted_list[$i]['proceeding'] = $temp_arr;

        }

        return $sorted_list;
    }

    public function getProcOtherList()
    {
        $query = "SELECT proc_type_ot, proc_id FROM proceeding_main WHERE proc_type_ot != NULL OR proc_type_ot != ''";

        $statement = $this->dbAdapter->query($query);

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getProcSubtypeOtherList()
    {
        $query = "SELECT proc_subtype_ot, proc_subtype_list_id, proc_id FROM proc_subtype_list WHERE proc_subtype_ot != NULL OR proc_subtype_ot != '' AND inactive = 0";

        $statement = $this->dbAdapter->query($query);

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getProcSubtypeList($data = null)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $query = "SELECT pa.*, pt.*, pa.practice_area_id as pa_id
        FROM practice_area pa LEFT JOIN proceeding_type pt ON pa.practice_area_id = pt.practice_area_id WHERE
        (pa.practice_area_name != NULL OR pa.practice_area_name != '') AND (pt.proc_type_name != NULL OR pt.proc_type_name != '') ORDER BY practice_area_name, proc_type_name ";

        $statement = $this->dbAdapter->query($query);

        $results = $statement->execute();
        $proc_list = $results->getResource()->fetchAll();

        $sorted_list = array();
        $j = 0;
        $last_id = array();
        $count = count($proc_list);

        for ($i = 0; $i < $count; $i++) {

            if (!in_array($proc_list[$i]['pa_id'], $last_id)) {

                $sorted_list[$j]['practice_area_id'] = $proc_list[$i]['pa_id'];
                $sorted_list[$j]['practice_area_name'] = $proc_list[$i]['practice_area_name'];

                $temp_arr = array();

                for ($u = 0; $u < $count; $u++) {

                    if (!empty($proc_list[$u]['practice_area_id']) && $proc_list[$u]['practice_area_id'] == $proc_list[$i]['pa_id']) {

                        $temp_arr[$u]['pat_id'] = $proc_list[$u]['proc_type_id'];
                        $temp_arr[$u]['proc_type_name'] = $proc_list[$u]['proc_type_name'];

                        $sorted_list[$j]['proceeding'] = $temp_arr;
                    }
                }
            }

            $last_id[] = $proc_list[$i]['pa_id'];
            $j++;
        }

        $query = "SELECT * FROM proceeding_subtype WHERE proc_subtype_name != NULL OR proc_subtype_name != '' {$where} ORDER BY proc_subtype_name";
        $statement = $this->dbAdapter->query($query);
        $results = $statement->execute();
        $sb = $results->getResource()->fetchAll();

        for ($i = 2; $i < count($sorted_list); $i++) {

            for ($u = 2; $u < count($sorted_list[$i]['proceeding']); $u++) {

                $temp_arr = array();

                $k = 2;

                for ($j = 2; $j < count($sb); $j++) {

                    if ($sorted_list[$i]['proceeding'][$u]['pat_id'] == $sb[$j]['proc_type_id']) {
                        $temp_arr[$k]['proc_subtype_id'] = $sb[$j]['proc_subtype_id'];
                        $temp_arr[$k]['proc_subtype_name'] = $sb[$j]['proc_subtype_name'];
                        $temp_arr[$k]['inactive'] = $sb[$j]['inactive'];

                        $k++;
                    }

                }

                $sorted_list[$i]['proceeding'][$u]['sb'] = $temp_arr;

            }
        }

        return $sorted_list;

    }


    public function addProcType($data)
    {
        $is_uniq = $this->is_uniq_proc_type($data['proc_name']);

        if (!$is_uniq) {

            $statement = $this->dbAdapter->query("
									INSERT INTO proceeding_type SET proc_type_name = '{$data['proc_name']}',
									inactive = '{$data['inactive']}', practice_area_id = '{$data['pa_id']}'
            ");
            $statement->execute();

            $last_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            if (!empty($data['other_proc_id'])) {
                $statement = $this->dbAdapter->query("
									UPDATE proceeding_main SET proc_type_id = '{$last_id}', proc_type_ot = ''
									WHERE proc_id = '{$data['other_proc_id']}'
                ");
                $statement->execute();
            }

            return $last_id;

        } else {
            return "Entry exist";
        }
    }

    public function addProcSubtype($data)
    {
        $is_uniq = $this->is_uniq_proc_subtype($data['pst_name']);

        if (!$is_uniq) {

            $statement = $this->dbAdapter->query("
									INSERT INTO proceeding_subtype SET proc_subtype_name = '{$data['pst_name']}',
									inactive = '{$data['inactive']}', proc_type_id = '{$data['pt_id']}'
            ");
            $statement->execute();

            $last_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            if (!empty($data['other_pstl_id'])) {

                $statement = $this->dbAdapter->query("
									UPDATE proc_subtype_list SET proc_subtype_id = '{$last_id}', proc_subtype_ot = ''
									WHERE proc_subtype_list_id = '{$data['other_pstl_id']}'
                ");
                $statement->execute();
            }

            return $last_id;

        } else {
            return "Entry exist";
        }
    }

    public function is_uniq_proc_type($proc_name)
    {
        $where = "WHERE proc_type_name = '" . $proc_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT proc_type_name
									 FROM proceeding_type $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function is_uniq_proc_subtype($proc_name)
    {
        $where = "WHERE proc_subtype_name = '" . $proc_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT proc_subtype_name
									 FROM proceeding_subtype $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function updateProcType($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proceeding_type SET proc_type_name = '{$data['proc_name']}' WHERE
									proc_type_id = '{$data['proc_type_id']}'
            ");
        $statement->execute();
    }

    public function updateProcSubtype($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proceeding_subtype SET proc_subtype_name = '{$data['pst_name']}' WHERE
									proc_subtype_id = '{$data['pst_id']}'
            ");
        $statement->execute();
    }

    public function moveProcType($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proceeding_type SET practice_area_id = '{$data['pa_id']}' WHERE
									proc_type_id = '{$data['proc_id']}'
            ");
        $statement->execute();
    }

    public function moveProcSubtype($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proceeding_subtype SET proc_type_id = '{$data['pt_id']}' WHERE
									proc_subtype_id = '{$data['pst_id']}'
            ");
        $statement->execute();
    }

    public function replaceProcType($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proceeding_main SET proc_type_id = '{$data['proc_type_id']}', proc_type_ot = ''
									WHERE proc_id = '{$data['other_proc_id']}'
                ");
        $statement->execute();
    }

    public function replaceProcSubtype($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proc_subtype_list SET proc_subtype_id = '{$data['pst_id']}', proc_subtype_ot = ''
									WHERE proc_subtype_list_id = '{$data['pstl_id']}'
                ");
        $statement->execute();
    }

    public function inactiveHandlerProcType($params)
    {
        $error = "System can not inactive Practice Area, used in the following tables : ";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT proc_type_id
									 FROM proceeding_subtype WHERE proc_type_id = '{$params['pt_id']}'
            ");
            $results = $statement->execute();
            $proc_st = $results->getResource()->fetch();


            $statement = $this->dbAdapter->query("
                                SELECT proc_type_id
                                 FROM proceeding_main WHERE proc_type_id = '{$params['pt_id']}'
            ");
            $results = $statement->execute();
            $proc_main = $results->getResource()->fetch();

            if (empty($proc_st) && empty($proc_main)) {

                $statement = $this->dbAdapter->query("
									UPDATE proceeding_type SET inactive = 1 WHERE
									proc_type_id = '{$params['pt_id']}'
                ");
                $statement->execute();

                return "success";
            } else {

                if (!empty($proc_st)) $error .= "proceeding_subtype ";
                if (!empty($proc_main)) $error .= "proceeding_main ";

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE proceeding_type SET inactive = 0 WHERE
									proc_type_id = '{$params['pt_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    public function inactiveHandlerProcSubtype($params)
    {
        $error = "System can not inactive Proceeding SubType, used in the following tables : proc_subtype_list";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT proc_subtype_id
									 FROM proc_subtype_list WHERE proc_subtype_id = '{$params['pst_id']}'
            ");
            $results = $statement->execute();
            $proc_st = $results->getResource()->fetch();

            if (empty($proc_st)) {

                $statement = $this->dbAdapter->query("
									UPDATE proceeding_subtype SET inactive = 1 WHERE
									proc_subtype_id = '{$params['pst_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE proceeding_subtype SET inactive = 0 WHERE
									proc_subtype_id = '{$params['pst_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    // END Proceeding : Proceeding Type, SubType, SubType Parametres

    public function getRelatedList()
    {
        $statement = $this->dbAdapter->query("SELECT rel_proceeding_name,rel_proceeding_id,inactive FROM related_proceedings ORDER BY rel_proceeding_name ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getActionTypeList()
    {
        $statement = $this->dbAdapter->query("SELECT * FROM proceeding_type WHERE inactive = 0 ORDER BY proc_type_name ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function getProceedingSubTypeList()
    {

        $statement = $this->dbAdapter->query("
                                    SELECT *, pst.inactive, pst.proc_type_id proc_type_id
                                             FROM proceeding_type pt
                                    LEFT JOIN proceeding_subtype pst
                                    ON pt.proc_type_id =
                                             pst.proc_type_id
                                    WHERE inactive = 0
                                    ");

        $results = $statement->execute();

        $arr = array();
        foreach ($results->getResource()->fetchAll() as $value) {
            $arr[$value[0]][] =
                array(
                    'proc_type_id' => $value['proc_type_id'],
                    'proc_type_name' => $value['proc_type_name'],
                    'inactive' => $value['inactive'],
                    'proc_subtype_id' => $value['proc_subtype_id'],
                    'proc_subtype_name' => $value['proc_subtype_name']

                );
        }

        return $arr;

    }

    public function getProceedingSubType($post = null)
    {
        $where = "WHERE inactive = 0";

        if ($post != null && $post['proceedingTypeId']) {
            $where = " AND proc_type_id = '" . $post['proceedingTypeId'] . "'";
        }

        $statement = $this->dbAdapter->query("
            SELECT * FROM proceeding_subtype
            " . $where . "
            ORDER BY proc_subtype_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    public function getProceedingSubTypePara($post = null)
    {
        $where = "WHERE inactive = 0";

        if ($post != null) {
            $where = " AND proc_subtype_id IN ( " . $post['ID'] . ")";
        }

        $statement = $this->dbAdapter->query("
            SELECT * FROM proceeding_subtype_para
            " . $where . "
            ORDER BY proc_st_para
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getProceedingSubTypeParamsList()
    {
        $statement = $this->dbAdapter->query("SELECT proc_st_para_id, proc_st_para, inactive
                                                FROM proceeding_subtype_para");
        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getIssuesInHearing()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM dec_hearing_issue
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getOutcome()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM outcome WHERE outcome_id != 1
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }
    public function getHearings()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM dec_hearing WHERE dec_hearing_id != 1
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getHearingTypeList()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM hearing_type WHERE hearing_type_id != 1
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();

    }


    public function getHearingSubTypeList()
    {

        $statement = $this->dbAdapter->query("
            SELECT *, hs.inactive, hs.hearing_type_id hearing_type_id
            FROM hearing_type ht
            LEFT JOIN hearing_sub hs
              ON ht.hearing_type_id = hs.hearing_type_id
		");

        $results = $statement->execute();

        $arr = array();

        foreach ($results->getResource()->fetchAll() as $value) {
            $arr[$value[0]][] = array(
                'hearing_name' => $value['hearing_name'],
                'hearing_sub_id' => $value['hearing_sub_id'],
                'hearing_sub_name' => $value['hearing_sub_name'],
                'inactive' => $value['inactive'],
                'hearing_type_id' => $value['hearing_type_id']
            );
        }

        return $arr;
    }


    public function getPartyTypeList($courtId = null, $options = null)
    {
        if ($options && isset($options['options']) && count($options['options'])) {
            $where = ' AND party_type_id IN (' . implode(',', $options['options']) . ')';
        } else {
            $where = '';
        }

        if (!empty($courtId)) {
            $query = "
                SELECT * FROM party_type
                WHERE (party_type_name != NULL
                  OR party_type_name != '')
                  AND inactive = 0 
                  AND court_id = " . (int)$courtId . "
                  " . $where . "
                ORDER BY party_type_name
            ";

        } else {
            $query = "
                SELECT pt.*, ct.court_name
                FROM party_type pt
                LEFT JOIN court_type ct
                  ON ct.court_id = pt.court_id
                  AND ct.inactive = 0
                WHERE (pt.party_type_name != NULL
                  OR pt.party_type_name != '')
                  AND pt.inactive = 0
                " . $where . "
                ORDER BY ct.court_name, pt.party_type_name
            ";
        }

        $statement = $this->dbAdapter->query($query);
        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    public function getProceedingPosition()
    {
        $statement = $this->dbAdapter->query("

									SELECT * FROM party_position WHERE  party_position_name != NULL OR party_position_name != '' ORDER BY party_position_name");
        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getPartyCategory()
    {

        $statement = $this->dbAdapter->query("
									SELECT party_category_id,party_category_name,party_cat_master,inactive
									 FROM party_category
									 WHERE  party_category_name != NULL OR party_category_name != ''
				ORDER BY party_category_name");
        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getPartyTypesToCatalog()
    {
        return $this->dbAdapter->query("SELECT proc_party_type_id, party_type_ot FROM proceeding_party_type
                                                WHERE party_type_ot != NULL OR party_type_ot != ''
                                                ORDER BY party_type_ot
                                                ")->execute()->getResource()->fetchAll();
    }


    public function getPartySuffixesToCatalogList()
    {


        $statement = $this->dbAdapter->query("
									SELECT * FROM proceeding_party
									 WHERE  party_suffix_ot != NULL OR party_suffix_ot != ''
                                         ORDER BY party_suffix_ot
		");

        $results = $statement->execute();
        $proceedingPartyList = $results->getResource()->fetchAll();

        return $proceedingPartyList;

    }


    public function getProceedingSub($proceedingTypeId)
    {

        $where = "";
        if (!empty($proceedingTypeId)) {
            $where = "WHERE proc_id = '$proceedingTypeId'";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM proceeding_subtype
                                              $where ORDER BY proc_sub_name");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function getProceedingSubPara($proceedingSubTypeId)
    {
        $where = "";
        if (!empty($proceedingTypeId)) {
            $where = "WHERE sub_proc_id = '$proceedingSubTypeId'";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM proceeding_subtype_para $where ORDER BY proc_sub_para");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function getPartyPositionCatalogList()
    {

        $statement = $this->dbAdapter->query("
									SELECT * FROM proceeding_party_position
									WHERE proc_party_position_id IS NULL
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();

    }


    public function getPartyNameToCatalogList()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM proceeding_party pp
									WHERE
									party_name_ot <> ''
									AND
									send_name_to_dropdown = '1'
		");

        $results = $statement->execute();
        $proceedingPartyList = $results->getResource()->fetchAll();

        return $proceedingPartyList;

    }

    public function getProceedingCategoryList()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM proceeding_category
									WHERE proc_cat_name IS NOT NULL AND inactive = 0
		");

        $results = $statement->execute();
        $proceedingPartyList = $results->getResource()->fetchAll();

        return $proceedingPartyList;

    }

    public function getPartyNameList($arg = null)
    {

        $statement = $this->dbAdapter->query("
									SELECT * FROM party_name
									WHERE  party_name != ''
		");

        $results = $statement->execute();
        $proceedingPartyList = $results->getResource()->fetchAll();
        if ($arg == null) return $proceedingPartyList;
        $arr = array();
        foreach ($proceedingPartyList as $key => $proceedingParty) {

            $arr[$proceedingParty['party_id']][] = array(
                'proc_party_id' => $proceedingParty['proc_party_id'],
                'proc_id' => $proceedingParty['proc_party_id'],
                'party_id' => $proceedingParty['proc_party_id'],
                'party_name_ot' => $proceedingParty['party_name_ot'],
                'send_name_to_dropdown' => $proceedingParty['send_name_to_dropdown'],
                'party_suffix' => $proceedingParty['party_suffix'],
                'party_suffix_ot' => $proceedingParty['party_suffix_ot'],
                'send_suffix_to_dropdown' => $proceedingParty['send_suffix_to_dropdown'],
                'PARTY_NAME' => $proceedingParty['party_name'],
                'inactive' => $proceedingParty['inactive']
            );
        }

        return $arr;
    }

    /**
     *  Link Parties - Form to link parties together, usage is for franchised entities.
     *    Parties to Link Suggestions - This list will be populated by an algorithm run by the system.
     *  The algorithm will query all Party_Name.Party_ID where link_party_id is blank,
     *  and then do a compare of the respective Party_Name for similarities to all other Party names
     *  in the table, irrespective of whether the other Party names have link_party_id set to a value or blank.
     *   If the items being suggested to link have at least one item previously linked,
     *   that Party_Name shows up in the parent name,
     *   with the unlinked suggested items showing up as child items.
     *   If none of the items being suggested have previous links,
     *   then the parent name displays as standard text �Suggested Link�.
     *   To link items together, they are highlighted, and the Approve Link Suggestions button is clicked.
     *   In the wireframe example, the following scenarios could occur:
     *
     *
     * @return array
     */
    public function getPartiesLinkSuggestionsList()
    {


        $statement = $this->dbAdapter->query("
									SELECT * FROM party_name
									WHERE link_party_id IS NULL
		");

        $results = $statement->execute();
        $partiesLinkSuggestionsList = $results->getResource()->fetchAll();

        $partyMaskArr = array();
        $arr = array();
        foreach ($partiesLinkSuggestionsList as $value) {
            /*
            Do compare of the respective Party_Name for similarities to all other Party names
            in the table, irrespective of whether the other Party names have link_party_id set to a value or blank.
            */
            $partyNameMaskPos = strrpos($value ['party_name'], " ");
            if ($partyNameMaskPos == false) {
                $partyNameMask = $value['party_name'];
            } else {
                $partyNameMask = substr($value ['party_name'], 0, $partyNameMaskPos);
            }
            $partyNameMask = trim($partyNameMask);
            if (!in_array($partyNameMask, $partyMaskArr)) {
                $partyMaskArr[] = $partyNameMask;

                $query = "SELECT * FROM party_name
                      WHERE party_name LIKE '%" . $partyNameMask . "%'
                      ORDER BY party_id DESC";
                $statement = $this->dbAdapter->query($query);

                $result = $statement->execute();
                $partySuggestionList = $result->getResource()->fetchAll();

                $item = array_shift($partySuggestionList);
                $item['child'] = $partySuggestionList;
                $arr[] = $item;

            }


        }
        return $arr;


    }

    public function getPartiesLinkListEBD()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM party_name pn
									INNER JOIN link_party lp
									ON pn.link_party_id = lp.link_party_id
									WHERE lp.inactive = '0'
									ORDER BY pn.party_id
		");

        $results = $statement->execute();
        $partiesLinkSuggestionsList = $results->getResource()->fetchAll();
        $arr = array();

        $ID = 0;
        foreach ($partiesLinkSuggestionsList as $partyName) {
            $linkPartyID = $partyName['link_party_id'];
            if ($ID != $linkPartyID) {
                $ID = $linkPartyID;
                $arr[$ID] = $partyName;
            } else {
                $arr[$ID]["child"][] = $partyName;

            }
        }

        return $arr;
    }

    public function getPartiesLinkList()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM party_name pn
									INNER JOIN link_party lp
									ON pn.link_party_id = lp.link_party_id
									ORDER BY pn.party_id
		");

        $results = $statement->execute();
        $partiesLinkSuggestionsList = $results->getResource()->fetchAll();
        $arr = array();
        foreach ($partiesLinkSuggestionsList as $partyName) {
            $arr[$partyName['link_party_id']][] = array(
                'link_party_id' => $partyName['link_party_id'],
                'party_id' => $partyName['party_id'],
                'party_name' => $partyName['party_name']);
        }

        return $arr;
    }


    public function getUnlinkedPartiesList()
    {
        $statement = $this->dbAdapter->query("
            SELECT *
            FROM  party_name
            WHERE  link_party_id IS NULL
		");

        $results = $statement->execute();
        return $partiesLinkSuggestionsList = $results->getResource()->fetchAll();

    }

    public function getCjType($countryId = null)
    {
        $where = "WHERE inactive = 0";

        if (!is_null($countryId)) {
            $where .= " AND cj_type_id = '" . $countryId;
        }

        $statement = $this->dbAdapter->query("
            SELECT * FROM cj_type
            " . $where . "
            ORDER BY cj_type_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function getCjTypeList()
    {


        $statement = $this->dbAdapter->query("
									SELECT *, cs.country_id country_id FROM country cs
									LEFT JOIN cj_type ct
									ON ct.country_id = cs.country_id WHERE  name != NULL OR name != ''
                                        ORDER BY name,cj_type_name


		");

        $results = $statement->execute();
        $countryList = $results->getResource()->fetchAll();

        $cjTypeListArr = array();
        foreach ($countryList as $key => $value) {
            $cjTypeListArr[$value[0]][] = array(
                'cj_type_id' => $value['cj_type_id'],
                'cj_type_name' => $value['cj_type_name'],
                'country_id' => $value['country_id'],
                'is_pres' => $value['is_pres'],
                'inactive' => $value['inactive'],
                'name' => $value['name']
            );
        }

        return $cjTypeListArr;
    }

    public function getCjHistoryList($cjNameId = null, $sortById = null)
    {


        /**
         * proceeding_Dec_Counsel.CJ_ID_Suggest
         * and Dec_Pres_Auth.CJ_ID_Suggest
         */
        $where = "";
        if (!empty($cjNameId)) {

            $where = "WHERE cj_history.cj_id = '" . $cjNameId . "'";
        } else {

            return array();
        }

        $desc = "DESC";
        if ($sortById == "old") {

            $desc = "";
        }

        $statement = $this->dbAdapter->query("
								SELECT * FROM cj_history

								INNER JOIN cj_main
								ON cj_main.cj_id = cj_history.cj_id
				                /*
								LEFT JOIN country c
								ON cj_history.country_id = c.country_id
									*/
								LEFT JOIN cj_type
								ON cj_history.cj_type_id =  cj_type.cj_type_id

								LEFT JOIN state
								ON cj_history.state_id =  state.state_id


								LEFT JOIN cj_title
								ON cj_history.cj_title_id =  cj_title.cj_title_id

								/*
								LEFT JOIN law_firm
								ON cj_history.country_id =  state.country_id
								*/

								LEFT JOIN court_type
								ON cj_history.court_id = court_type.court_id
								/*
								LEFT JOIN court_region
								ON cj_history.court_reg_id = court_region.Court_Reg_ID
            */
				    				$where

								/* GROUP BY cj_main.CJ_ID */
								ORDER BY cj_history.cj_history_id $desc
				");

        $results = $statement->execute();
        return $cjHistory = $results->getResource()->fetchAll();

        $cjHistoryArr = array();
        foreach ($cjHistory as $key => $value) {

            $cjHistoryArr[$value['country_id']][] = array(
                'cj_history_id' => $value['cj_history_id'],
                'cj_id' => $value['cj_id'],
                'cj_type_id' => $value['cj_type_id'],            
                'state_id' => $value['state_id'],
                'law_firm_id' => $value['law_firm_id'],
                'title_id' => $value['title_id'],
                'title_ot' => $value['title_ot'],
                'court_id' => $value['court_id'],
                'court_reg_id' => $value['court_reg_id'],
                'court_mun_id' => $value['court_mun_id'],
                'Court_Mun_OT' => $value['court_mun_ot'],
                'start_date' => $value['start_date'],
                'end_date' => $value['end_date'],
                'update_date' => $value['update_date'],
                'name' => $value['name'],
                'inactive' => $value['inactive']
            );
        }

        return $cjHistoryArr;

    }


    public function getStepTypes($courtTypeStepId = null)
    {
        $where = "";
        if (!empty($courtTypeStepId)) {
            $where = "WHERE court_id = '$courtTypeStepId'";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM step_type $where ORDER BY step_name");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function getSubStepTypes($stepTypeId)
    {
        $where = "";
        if (!empty($stepTypeId)) {
            $where = "WHERE step_type_id = '$stepTypeId'";
        }
        $statement = $this->dbAdapter->query("SELECT * FROM sub_step $where ORDER BY sub_step_name");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getPartyType($partyTypeId = null)
    {
        $where = "";
        if (!empty($partyTypeId)) {
            $where = "WHERE step_type_id = '$partyTypeId'";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM party_type $where");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }



    public function getLinkParty($partyLinkId = null)
    {
        $where = "";
        if (isset($partyLinkId)) {

            $where = "WHERE link_party_id = '$partyLinkId' ";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM link_party $where");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getLastLinkPartyRow()
    {
        $statement = $this->dbAdapter->query("SELECT * FROM link_party
										ORDER BY link_party_id DESC
										LIMIT 1
				");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }
    
    public function insertDecmainStartEndDate($data)
    {
        if((trim($data['dates']) == null)or(trim($data['dates']) == '')){
            
            $newData = array(
                'hearing_date_start' => NULL,
                'hearing_date_end' => NULL,
                'hearing_date_other_id' => $data["ModeHearings"],
                'dec_id' => $data["id"]
            );
        }else {

            $dates = explode(" - ", $data['dates']);
            $dataStart = trim($dates[0]);
            $dataEnd = trim($dates[1]);

            $newData = array(
                'hearing_date_start' => $dataStart,
                'hearing_date_end' => $dataEnd,
                'hearing_date_other_id' => $data["ModeHearings"],
                'dec_id' => $data["id"]
            );
        }

        $sql = new Sql($this->dbAdapter);
        $insert = $sql->insert("dec_hearing_date");
        $insert->values($newData);
        $insertString = $sql->getSqlStringForSqlObject($insert);

        $results = $this->dbAdapter->query($insertString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }

    
    public function updateDecmainStartEndDate($data)
    {
        if((trim($data['dates']) == null)or(trim($data['dates']) == '')){
        
            $newData = array(
                'hearing_date_start' => NULL,
                'hearing_date_end' => NULL,
                'hearing_date_other_id' => $data["ModeHearings"],
                'dec_id' => $data["id"]
            );
        }else {

            $dates = explode(" - ", $data['dates']);
            $dataStart = trim($dates[0]);
            $dataEnd = trim($dates[1]);

            $newData = array(
                'hearing_date_start' => $dataStart,
                'hearing_date_end' => $dataEnd,
                'hearing_date_other_id' => $data["ModeHearings"],
                'dec_id' => $data["id"]
            );
        }

        $sql = new Sql($this->dbAdapter);
        $update = $sql->update('dec_hearing_date');
        $update->set($newData);
        $update->where(array("hearing_date_id" => $data["hearingId"]));
        $updateString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }

    public function updateDecHearingDateInactive($id, $to)
    {
        $newData = ['inactive' => $to];
        $sql = new Sql($this->dbAdapter);
        $update = $sql->update('dec_hearing_date');
        $update->set($newData);
        $update->where(array("hearing_date_id" => $id));
        $updateString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }

    public function updateDecMain($data)
    {
        $type = $data['type'];       

        parse_str($data['decMainForm'], $params);
        if($params['Amedment'] == "on"){
            $params['Amedment'] = 1;
        }else {
            $params['Amedment'] = 0;
        }
        if($params['Addendum'] == "on"){
            $params['Addendum'] = 1;
        }else {
            $params['Addendum'] = 0;
        }
        $decDateArr = explode("/", $params["dec_date"]);
           $newData = [
            'decname' => $params['dec_name'],
            'dec_date' => date($decDateArr[0] . "-" . $decDateArr[1] . "-" . $decDateArr[2]),
            'citation_no' => $params['cit_number'],
            'practice_area_id' => $params['decmain_decision_type'],
            'country_id' => $params['decmain_country'],
            'state_id' => $params['decmain_state'],
            'court_type_id' => $params['decmain_court-type'],
            'court_mun_id' => $params['decmain_court-mun'],
            'court_mun_ot' => $params['decmain_other_court_mun'],
            'court_office_id' => $params['decmain_court-office'],
            'court_office_ot' => $params['decmain_other_court_office'],
            'is_amend' => $params['Amedment'],
            'is_addend' => $params['Addendum'],
        ];
        $sql = new Sql($this->dbAdapter);
        $update = $sql->update($type);
        $update->set($newData);
        $update->where(array("dec_id" => $data["id"]));
        $updateString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {

            return "success";
        } else {
            return "fail";
        }
    }

    public function addDecMain($data)
    {
        $type = $data['type'];

        parse_str($data['decMainForm'], $params);

        $decDateArr = explode("/", $params["dec_date"]);

        $decDate = date($decDateArr[2] . "-" . $decDateArr[0] . "-" . $decDateArr[1]);

        $citNumber = $params['cit_number'];
        $decName = $params['dec_name'];

        $country = $params['decmain_country'];
        $state = $params['decmain_state'];

        $courtType = $params['decmain_court-type'];

        $courtMun = $params['decmain_court-mun'];
        $otherCourtMun = $params['decmain_other_court_mun'];

        $newData = array(

            'decname' => $decName,
            'dec_date' => $decDate,
            'citation_no' => $citNumber,
            'country_id' => $country,
            'state_id' => $state,
            'court_type_id' => $courtType,
            'court_mun_id' => $courtMun,
            'court_mun_ot' => $otherCourtMun

        );


        $sql = new Sql($this->dbAdapter);
        $insert = $sql->insert($type);
        $insert->values($newData);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $decId = $this->dbAdapter->getDriver()->getLastGeneratedValue();
        if ($results) {

            $decDateArr = explode("/", $params["dec_date"]);
            $decDateStart = date($decDateArr[2] . "-" . $decDateArr[0] . "-" . $decDateArr[1]);
            $decDateArr = explode("/", $params["date_end"]);
            $decDateEnd = date($decDateArr[2] . "-" . $decDateArr[0] . "-" . $decDateArr[1]);
            $newData = array(
                'dec_id' => $decId,
                'hearing_date_start' => $decDateStart,
                'hearing_date_end' => $decDateEnd
            );

            $sql = new Sql($this->dbAdapter);
            $insert = $sql->insert('dec_hearing_date');
            $insert->values($newData);
            $selectString = $sql->getSqlStringForSqlObject($insert);
            $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            if ($results) {
                return "success";
            } else {

                return "fail";
            }
        } else {

            return "fail";
        }
    }

    public function addPresidingAuthList($post)
    {
        $cjType = $post['cjType'];
        $cjNameH = $post['cjNameH'];
        $aliasOther = $post['aliasOther'];
        $comments = $post['comments'];
        $decMainID = $post['decMainID'];
        $sugges = $post['sugges'];
        $authorId = $post['authorId'];
        $chairId = $post['chairId'];
        $dataCjId = $post['dataCjId'];
        if($sugges == "0"){
            $sugges = null;
        }else{
            $sugges = trim($dataCjId);
        }
        $newData = array(
            'dec_id' => $decMainID,
            'cj_type_id' => $cjType,
            'cj_history_id' => $cjNameH,
            'pres_name_ot' => $aliasOther,
            'ot_comments' => $comments,
            'is_dec_author' => $authorId,
            'is_chair' => $chairId,
            'cj_id_suggest' => $sugges,

        );
        $sql = new Sql($this->dbAdapter);
        $insert = $sql->insert('dec_pres_auth');
        $insert->values($newData);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        print_r($selectString);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }

    
    public function addCounselProcList($post)
    {
        $proceedType = $post['proceedType'];
        $proceedSubtype = $post['proceedSubtype'];
        $shortStyleCause = $post['shortStyleCause'];
        $courtFileNumber = $post['courtFileNumber'];
        $additionalParam = $post['additionalParam'];
        $countryTab = $post['countryTab'];
        $stateTab = $post['stateTab'];
        $courtTypeTab = $post['courtTypeTab'];
        $courtMunTab = $post['courtMunTab'];
        $counselOther = $post['counselOther'];

        $dataProceedingMain = array(

            'short_soc' => $shortStyleCause,
            'court_file_no' => $courtFileNumber,
            'proc_type_id' => $proceedType,
            'country_id' => $countryTab,
            'state_id' => $stateTab,
            'court_type_id' => $courtTypeTab,
            'court_mun_id' => $courtMunTab,
            'court_mun_ot' => $counselOther,

        );

        $sql = new Sql($this->dbAdapter);

        $proceedingMain = $sql->insert('proceeding_main');
        $proceedingMain->values($dataProceedingMain);
        $selectString = $sql->getSqlStringForSqlObject($proceedingMain);
        $resultsMain = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        $procID = $this->dbAdapter->getDriver()->getLastGeneratedValue();

        $dataSTList = array(
            'proc_subtype_list_id' => null,
            'proc_id' => $procID,
            'proc_subtype_id' => $proceedSubtype,
            'proc_subtype_ot' => $additionalParam
        );
        $insertProceedingMain = $sql->insert('proc_subtype_list');
        $insertProceedingMain->values($dataSTList);
        $selectList = $sql->getSqlStringForSqlObject($insertProceedingMain);
        $resultsList = $this->dbAdapter->query($selectList, Adapter::QUERY_MODE_EXECUTE);

        if ($resultsMain) {
            if ($resultsList) {
                return 'success';
            } else {
                return 'fail';
            }
        }


    }

    public function updateCounselProcList($post)
    {
        $id = $post['counselId'];
        $proceedType = $post['proceedType'];
        $proceedSubtype = $post['proceedSubtype'];
        $shortStyleCause = $post['shortStyleCause'];
        $courtFileNumber = $post['courtFileNumber'];
        $additionalParam = $post['additionalParam'];
        $countryTab = $post['countryTab'];
        $stateTab = $post['stateTab'];
        $courtTypeTab = $post['courtTypeTab'];
        $courtMunTab = $post['courtMunTab'];
        $counselOther = $post['counselOther'];

        $newData = array(
            'proc_id' => $id,
            'short_soc' => $shortStyleCause,
            'court_file_no' => $courtFileNumber,
            'proc_type_id' => $proceedType,
            'country_id' => $countryTab,
            'state_id' => $stateTab,
            'court_type_id' => $courtTypeTab,
            'court_mun_id' => $courtMunTab,
            'court_mun_ot' => $counselOther
        );
        $sql = new Sql($this->dbAdapter);
        $update = $sql->update('proceeding_main');
        $update->where("Proc_ID=$id");
        $update->set($newData);
        $selectString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }


   
    public function updatePresidingAuthList ($post)
    {

        $cjType = $post['cjType'];
        $cjNameH = $post['cjNameH'];
        $decMainID = $post['decMainID'];
        $aliasOther = $post['aliasOther'];
        $comments = $post['comments'];
        $sugges = $post['sugges'];
        $authorId = $post['authorId'];
        $chairId = $post['chairId'];
        $hasJuryAuthority = $post['hasJuryAuthority'];

        $newData = array(
            'cj_type_id' => $cjType,
            'cj_history_id' => $cjNameH,
            'pres_name_ot' => $aliasOther,
            'ot_comments' => $comments,
            'is_dec_author' => $authorId,
            'is_chair' => $chairId,
            'cj_id_suggest' => $sugges
        );
        $sql = new Sql($this->dbAdapter);
        $update = $sql->update('dec_pres_auth');
        $update->where(array("dec_id"=>$decMainID));
        $update->set($newData);
        $selectString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            $newData = array(
                'has_jury' => $hasJuryAuthority
            );
            $update = $sql->update('dec_hearing');
            $update->where(array("dec_id" => $decMainID));
            $update->set($newData);
            $selectString = $sql->getSqlStringForSqlObject($update);
            $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            if ($results) {
                 return "success";
            } else {
                return "fail";
            }
        } else {
            return "fail";
        }

    }

    public function addProceedingMain($data)
    {
        $type = $data['type'];


        $newDataToProcMain = array(
            'short_soc' => $data->radop[name2],
            'court_file_no' => $data->radop[name1],
            'proc_type_id' => $data->radop[name9],
            'country_id' => $data->radop[name5],
            'state_id' => $data->radop[name6],
            'court_type_id' => $data->radop[name3],
            'court_mun_id' => $data->radop[name7],
            'court_mun_ot' => $data->radop[name8],
            'proc_cat_id' => $data->radop[name4],
            'proc_type_ot' => $data->radop[name10]
        );

        $sql = new Sql($this->dbAdapter);
        $insertToProcMain = $sql->insert($type);
        $insertToProcMain->values($newDataToProcMain);
        $insertToProcMainString = $sql->getSqlStringForSqlObject($insertToProcMain);
        $results = $this->dbAdapter->query($insertToProcMainString, Adapter::QUERY_MODE_EXECUTE);

        if ($results) {
            return "success";
        } else {
            return "fail";
        }



    }

    public function updateProceedingMain($data, $mainProcID)
    {
        $type = $data['type'];
        parse_str($data['procMainForm'], $params);


        $newData = array(

            'short_soc' => $params['short_soc'],
            'court_file_no' => $params['file_number'],
            'proc_type_id' => $params['proceeding-type'],
            'country_id' => $params['proc-country'],
            'state_id' => $params['proc-state'],
            'court_type_id' => $params['proc-court-type'],
            'proc_type_ot' => $params['other_type'],
            'court_mun_id' => $params['court-mun'],
            'court_mun_ot' => $params['proc_other_court_mun'],         
        );

        $sql = new Sql($this->dbAdapter);
        $update = $sql->update($type);
        $update->where("proc_id=$mainProcID");
        $update->set($newData);
        $selectString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if (!$results) {
            return "fail";
        }

        if (isset($params['proc-other-subtypes']) && $params['proc-other-subtypes'] != 0) {
            $other_subtypes = $sql->update('');
            $other_subtypes->set([
                'proc_subtype_name' => $params['other_subtypes'],
                'proc_type_id' => $params['proceeding-type'],
                'inactive' => 0
            ]);
            $insString = $sql->getSqlStringForSqlObject($other_subtypes);
            $result = $this->dbAdapter->query($insString, Adapter::QUERY_MODE_EXECUTE);
            if (!$result) return 'fail';
        }

    }

    public function fetchDecType()
    {
        $select = "SELECT * FROM practice_area";
        $statement = $this->dbAdapter->query($select);
        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function fetchDecMain($post)
    {
        $where = "WHERE dm.dec_id = '" . $post["decMainId"] . "'";
        $statement = $this->dbAdapter->query("
											SELECT
											  *, dh.inactive AS inactive_hearing_date, dh.hearing_date_id AS id_hearing_date_id
											FROM
											  dec_main
											AS
											  dm
											LEFT JOIN
											  dec_hearing_date AS dh
    			                            ON
    			                              dm.dec_id = dh.dec_id
    			                            LEFT JOIN
    			                              court_office AS co
    			                            ON
    			                              co.court_office_id = dm.court_office_id
											 $where
										");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    public function selectAll($table, $order = null, $where = null)
    {
        $statement = $this->dbAdapter->query("
            SELECT * FROM " . $table . "
            WHERE inactive = 0
            " . $where . "
            " . $order . "
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getOtherDates()
    {

    }

    public function searcheDecMain($post)
    {
        $where = "WHERE dm.dec_id = '" . $post['decMainId'] . "'";
        $statement = $this->dbAdapter->query("
											SELECT *, dm.dec_id AS id_Dec
											FROM dec_main AS dm
											LEFT JOIN dec_hearing_date AS dh
    			                            ON dm.dec_id = dh.dec_id
											$where
										");

        $results = $statement->execute();
        return $results->getResource()->fetch();

    }

    public function addPartyMain($post, $idProc)
    {

        $type = $post["type"];
        $dataToProcParty = array(
            "party_suffix" => $post["partySuffix"],
            "party_id" => $post["partyName"],
            "proc_id" => $idProc
        );
        $sql = new Sql($this->dbAdapter);
        $insertToProceedingParty = $sql->insert($type);
        $insertToProceedingParty->values($dataToProcParty);
        $insertToProceedingPartyString = $sql->getSqlStringForSqlObject($insertToProceedingParty);
        $result = $this->dbAdapter->query($insertToProceedingPartyString, Adapter::QUERY_MODE_EXECUTE);
        if (!$result) return "fail";

        $lastId = $this->dbAdapter->getDriver()->getLastGeneratedValue();

        $insertToProceedingPartyPosition = "INSERT INTO proceeding_party_position ( proc_party_id ,proc_party_position_id ) VALUES ($lastId ,'" . $post[partyPosition] . "')";
        $result = $this->dbAdapter->query($insertToProceedingPartyPosition, Adapter::QUERY_MODE_EXECUTE);
        if (!$result) return "fail";

        $insertToProceedingPartyType = "INSERT INTO proceeding_party_type ( proc_party_id ,party_type_id ) VALUES ($lastId ,'" . $post[partyType] . "')";
        $result = $this->dbAdapter->query($insertToProceedingPartyType, Adapter::QUERY_MODE_EXECUTE);

        if ($result) {
            return "success";
        } else {
            return "fail";
        }

    }

    public function updateProceedingParty($post)
    {
        $type = $post['type'];

        $newData = [
            'party_suffix' => $post->partySuffix,
            'party_id' => $post->partyName,
            'party_name_ot' => $post->aliasName,
            'is_company' => $post->CompanyID,
            'party_suffix_ot' => $post->procPartySuffixOT,
            'associated_party' => $post->assiciated,
        ];
        $sql = new Sql($this->dbAdapter);
        $update = $sql->update($type);
        $update->set($newData);
        $update->where(array("proc_id" => $post->procId));
        $updateString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($updateString, Adapter::QUERY_MODE_EXECUTE);

        if(!empty($results)){

            $post['type'] = "proceeding_party_type" ;
            $type = $post['type'];



            $statement = $this->dbAdapter->query("
											UPDATE {$type} AS t
											 INNER JOIN proceeding_party AS p
											 ON t.proc_party_id = p.proc_party_id
                                              SET t.party_type_id ='{$post->partyType}',
                                              t.party_type_ot = '{$post->partyTypeOther}'
											 WHERE p.proc_id='{$post->procId}'
										");
            $results = $statement->execute();

        }
        if(!empty($results)) {
            $post['type'] = "proceeding_party_position";
            $type = $post['type'];
            $id_2 = uniqid("pref_");

            $statement = $this->dbAdapter->query("
											UPDATE {$type} AS pos
											 INNER JOIN proceeding_party AS p
											 ON pos.proc_party_id = p.proc_party_id
                                              SET pos.party_position_id ='{$post->partyPositionID}',
                                              pos.Party_Position_OT = '{$post->positionOther}'
											 WHERE p.proc_id='{$post->procId}'
										");
            $results = $statement->execute();
        }
        if ($results) {
            return 'success';
        } else {
            return 'fail';

        }

    }

    public function addDropdownField($data)
    {
        $type = $data['type'];

        $data['name'] = trim($data['name']);

        $newData = array();
        if ($type == 'country') {
            $newData = array(
                'name' => $data['name'], 'inactive' => $data['inactive']);
        } elseif ($type == 'state') {

            $newData = array(
                'state_name' => $data['name'],
                'country_id' => $data['countryId'],
                'inactive' => $data['inactive']
            );

        } elseif ($type == 'city') {

            $newData = array('city_name' => $data['name'],
                'state_id' => $data['stateId'],
                'country_id' => $data['countryId']
            );

        } elseif ($type == "court_type") {

            $newData = array(
                'court_name' => $data['name'],
                'country_id' => $data['countryId'],
                'state_id' => $data['stateId']
            );

        } elseif ($type == "court_region") {

            $newData = array(
                'reg_name' => $data['name'],
                'court_id' => $data['courtId']
            );
        } elseif ($type == "court_mun") {

            $newData = array(
                'mun_name' => $data['name'],
                'court_id' => $data['courtTypeId'],
                'court_reg_id' => $data['courtRegId']
            );

        } elseif ($type == "court_office") {

            $newData = array(
                'court_office_name' => $data['name'],
                'court_office_id' => $data['courtOffceId'],
                'court_reg_id' => $data['GllobalfCourtRegioneId'],
                'court_id' => $data['GllobalfCourtTypeId'],
                'court_mun_id' => $data['GlobalCourtOfficeId']
            );

        } elseif ($type == "court_office_room") {

            $newData = array(
                'court_office_room_name' => $data['name'],
                'court_office_id' => $data['roomId']

            );

        } elseif ($type == "court_list_event") {

            $newData = array(
                'court_list_event_name' => $data['name']
            );

        } elseif ($type == "customer_role") {

            $newData = array(
                'cust_role_name' => $data['name']
            );

        } elseif ($type == "doc_source") {

            $newData = array(
                'source_name' => $data['name']
            );

        } elseif ($type == "doc_format") {

            $newData = array(
                'doc_format_name' => $data['name']
            );

        } elseif ($type == "proceeding_category") {

            $newData = array(
                'proc_cat_name' => $data['name']
            );
        } elseif ($type == "proceeding_category") {

            $newData = array(
                'proc_cat_name' => $data['name']
            );
        } elseif ($type == "practice_area") {

            $newData = array(
                'practice_area_name' => $data['name']
            );
        } elseif ($type == "proceeding_type") {

            $newData = array(
                'proc_type_name' => $data['name']
            );
        } elseif ($type == "related_proceedings") {

            $newData = array(
                'rel_proceeding_name' => $data['name']
            );
        } elseif ($type == "proceeding_subtype") {

            $newData = array(
                'proc_subtype_name' => $data['name'],
                'proc_type_id' => $data['proceedingTypeId']
            );

        } elseif ($type == "industry") {

            $newData = array(
                'industry_name' => $data['name']

            );

        } elseif ($type == "gender") {

            $newData = array(
                'gender_name' => $data['name']

            );

        } elseif ($type == "proceeding_subtype_para") {

            $newData = array(
                'proc_st_para' => $data['name'],
                'proc_subtype_id' => $data['proceedingSubTypeId']
            );

        } elseif ($type == 'dec_hearing_date_other') {
            $newData = array(
                'dec_hearing_date_other_name' => $data['HearingName']);
        } elseif ($type == "hearing_type") {

            $newData = array(
                'hearing_name' => $data['name']
            );

            //hearingeeding sub type
        } elseif ($type == "hearing_sub") {

            $newData = array(
                'hearing_sub_name' => $data['name'],
                'hearing_type_id' => $data['hearingTypeId']
            );

        } elseif ($type == "referral_source") {

            $newData = array(
                'referral_source_name' => $data['name']

            );

        } else if ($type == "step_type") {

            $newData = array(
                'step_name' => $data['name'],
                'court_id' => $data['courtTypeStepId']
            );
        } else if ($type == "sub_step") {

            $newData = array(
                "step_type_id" => $data['stepTypeId'],
                "sub_step_name" => $data['name']
            );

        } else if ($type == "party_type") {

            $newData = array(
                "party_type_name" => $data['name'],
                "party_type_level" => $data['level']
            );

        } else if ($type == "party_suffix") {

            $newData = array(
                "party_suffix_name" => $data['name']
            );

        } else if ($type == "party_size") {

            $newData = array(
                "party_size_name" => $data['name']
            );

        } elseif ($type == "party") {

            $newData = array(
                "party_name" => $data['name']
            );
        } elseif ($type == "link_party") {

            $newData = array(
                "party_link_cnt" => $data['name']
            );

        } elseif ($type == "proceeding_party") {

            //add alias
            if ($data['add-alias']) {

            }

        } elseif ($type == "party_position") {

            $newData = array(
                "proc_subtype_id" => $data['procSubType'],
                "proc_type_id" => $data['procType'],
                "party_position_name" => $data['name']
            );

        } elseif ($type == "proceeding_party_position") {

            //Mandatory thing
            $type = "party_position";

            //First add into db party_position
            $newData = array(
                "proc_subtype_id" => $data['proceedingSubTypeId'],
                "party_position_name" => $data['name']
            );

        } elseif ($type == "party_name") {

            $newData = array(
                "party_name" => $data['name']
            );

        } elseif ($type == "party_category") {

            $newData = array(
                "party_category_name" => $data['name']
            );

        } elseif ($type == "cj_type") {


            $newData = array(
                "cj_type_name" => $data['name'],
                "country_id" => $data['countryID'],
                "is_pres" => $data['is_pres']
            );

        } elseif ($type == "cj_title") {

            $newData = array(
                "cj_title_name" => $data['name'],
                "country_id" => $data['countryCjtitleId']
            );

        } elseif ($type == 'cj_position') {

            $newData = array(
                'cj_position_name' => $data['cjPositionName'],
                'court_id' => $data['posCourtId']
            );

        } elseif ($type == "law_firm") {

            $newData = array(
                "law_firm_name" => $data['name'],
                "country_id" => $data['countryID'],
                "state_id" => $data['stateId']
            );

        } elseif ($type == "law_university") {

            $newData = array(
                "law_school_name" => $data['name'],
                "country_id" => $data['countryID'],
                "state_id" => $data['stateId']
            );

        } elseif ($type == "cause_list") {

            $newData = array(
                "cause_name" => $data['name']
            );

        } elseif ($type == "issue") {
            $newData = array(
                "issue_name" => $data['name']
            );

        } elseif ($type == 'originating_process') {
            $newData = array(
                "op_name" => $data['name']
            );
        } elseif ($type == 'outcome') {
            $newData = array(
                "outcome_name" => $data['name']
            );
        } elseif ($type == 'cost_award_coverage') {
            $newData = array(
                "cac_name" => $data['name']
            );
        } elseif ($type == 'damage_heads') {
            $newData = array(
                "dmg_head_name" => $data['name']
            );
        } elseif ($type == 'hearing_party_type') {
            $newData = array(
                "hearing_party_type_name" => $data['HearingPartyName'],
                "hearing_party_type_level" => $data['Hearinglevel']
            );
        } elseif ($type == 'role') {
            $newData = array(
                "role_name" => $data['name']
            );
        } else {
            return "Type not defined";
        }


        //continue

        $sql = new Sql($this->dbAdapter);
        $insert = $sql->insert($type);
        $insert->values($newData);

        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        if ($results) {
            if ($data['type'] == 'party_type' && !empty($data['relatedproceeding'])) {
                $party_type_id = $results->getGeneratedValue();
                $relatedprocc_id = (int)$data['relatedproceeding'];
                $this->dbAdapter->query("INSERT INTO related_proceedings_to_party_type (rel_proceeding_id, party_type_id) VALUES ({$relatedprocc_id}, {$party_type_id})")->execute();
            }

            if ($data['type'] == "proceeding_party_position") {

                //update proceeding_party_position
                $type = $data['type'];

                $partyPositionId = $this->dbAdapter->getDriver()->getLastGeneratedValue();

                $sql = new Sql($this->dbAdapter);
                $update = $sql->update($type);

                $update->set(array(
                    'proc_party_position_ot' => '',
                    'proc_party_position_id' => trim($partyPositionId)
                ));

                $update->where(array('proc_party_st_pos_id' => $data['proceedingPartySTPosID']));

                $selectString = $sql->getSqlStringForSqlObject($update);
                $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

                if ($results) {
                    return 'success';
                } else {
                    return 'fail';

                }
            }


            if ($type == 'state') {

                return $this->getState($data['countryId']);
            } elseif ($type == 'country') {

                return $this->showinactiveCountries(0);

            } elseif ($type == 'city') {

                return $this->showinactiveCountriesForStatesAndCities(0);

            } elseif ($type == 'court_type') {

                return $this->getCourtType($data['countryId'], $data['stateId']);
            } elseif ($type == 'court_region') {

                return $this->getCourtRegionList($data['courtId']);

            } elseif ($type == 'court_mun') {

                return $this->getCourtMunList($data['courtRegId'], $data['courtTypeId']);
            } elseif ($type == 'referral_source') {


                return $this->getReferralSource();
            } elseif ($type == 'proceeding_category') {


                return $this->getProceedingCategory();
            } elseif ($type == 'proceeding_type') {


                return $this->getProceedingTypeList();
            } elseif ($type == "proceeding_subtype") {

                return $this->getProceedingSubType();

            } elseif ($type == "proceeding_subtype_para") {

                return $this->getProceedingSubTypeParamsList();

            } else if ($type == "proceeding_sub_para") {

                return $this->getProceedingSubPara($data['proceedingSubTypeId']);

            } else if ($type == "hearing_type") {

                return $this->getHearingTypeList();

            } else if ($type == "hearing_party_type") {

                return $this->getHearingPatyType();

            } else if ($type == "party_size") {

                return $this->getPartySizeList();

            } else if ($type == "hearing_sub") {

                return $this->getHearingSubTypeList();

            } else if ($type == "step_type") {

                return $this->getStepTypes($data['courtTypeStepId']);

            } else if ($type == "sub_step") {

                return $this->getSubStepTypes($data['stepTypeId']);
            } else if ($type == "party_type") {

                return $this->getPartyTypeList();

            } else if ($type == "party_suffix") {

                return $this->showinactivePartySuffix(0);


            } elseif ($type == "party") {

                return $this->getParty();

            } elseif ($type == "link_party") {

                return $this->getLinkParty();
            } elseif ($type == "party_position") {

                return $this->getPartyPosition();

            } elseif ($type == "party_name") {

                return $this->getPartyNameList();
            } elseif ($type == "cj_type") {

                return $this->getCjTypeList();

            } elseif ($type == "cj_title") {

                return $this->showCjTitles();

            } elseif ($type == "law_firm") {

                return $this->showCountryStatesLawFirms();

            } elseif ($type == "law_university") {

                return $this->showCountryStatesLawUniversities();
            } elseif ($type == "cause_list") {

                return $this->showCauseProceedingList();
            } elseif ($type == "issue") {

                return $this->showIssueList();
            } elseif ($type == "originating_process") {

                return $this->showOriginProc();
            } elseif ($type == "dec_hearing_date_other") {

                return $this->getHearingDate();
            } elseif ($type == "outcome") {

                return $this->showOutcome();
            } elseif ($type == "party_category") {

                return $this->getPartyCategory();
            }elseif ($type == "cj_position") {

                return $this->cjPosListMaster('read', ['inactive' => '0']);
            } elseif ($type == 'cost_award_coverage') {

                return $this->showCAC();
            }

            return $this->getCountry();
        } else {
            return 0;
        }
    }

    public function updateDropdownField($data)
    {
        $type = $data['type'];
        $sql = new Sql($this->dbAdapter);
        $update = $sql->update($type);

        $data['name'] = trim($data['name']);

        if ($type == 'country') {

            $update->set(array('name' => $data['name']));
            $update->where(array('country_id' => $data['countryId']));

        } elseif ($type == 'state') {

            $update->set(
                array(
                    'state_name' => $data['name'],
                    'country_id' => $data['countryId']
                )

            );
            $update->where(array('state_id' => $data['stateId']));

        } elseif ($type == 'city') {

            $update->set(array('city_name' => $data['name']));
            $update->where(array('city_id' => $data['cityId']));
        } elseif ($type == 'court_type') {

            $update->set(array('court_name' => $data['name']));
            $update->where(array('court_id' => $data['courtTypeId']));

        } elseif ($type == 'court_list_event') {

            $update->set(array('court_list_event_name' => $data['name']));
            $update->where(array(' court_list_event_id' => $data['courtOfficeEventId']));

        } elseif ($type == 'court_region') {

            $update->set(array('reg_name' => $data['name']));
            $update->where(array('court_reg_id' => $data['courtRegionId']));

        } elseif ($type == 'court_mun') {

            $update->set(array('mun_name' => $data['name']));
            $update->where(array('court_mun_id' => $data['courtMunId']));

        } elseif ($type == 'industry') {

            $update->set(array('industry_name' => $data['name']));
            $update->where(array('industry_id' => $data['industryy']));

        } elseif ($type == 'customer_role') {

            $update->set(array('cust_role_name' => $data['name']));
            $update->where(array('cust_role_id' => $data['CustomerRoleID']));

        } elseif ($type == 'doc_source') {

            $update->set(array('source_name' => $data['name']));
            $update->where(array('source_id' => $data['dicisionSourceId']));

        } elseif ($type == 'doc_format') {

            $update->set(array('doc_format_name' => $data['name']));
            $update->where(array('doc_format_id' => $data['decesionFormatId']));

        } elseif ($type == 'damage_heads') {

            $update->set(array('dmg_head_name' => $data['name']));
            $update->where(array('dmg_head_id' => $data['damageHeadsId']));

        } elseif ($type == 'proceeding_category') {

            $update->set(array('proc_cat_name' => $data['name']));
            $update->where(array('proc_cat_id' => $data['procCatId']));

        } elseif ($type == 'proceeding_type') {


            $update->set(array('proc_type_name' => $data['name']));
            $update->where(array('proc_type_id' => $data['proceedingTypeId']));

        } elseif ($type == 'practice_area') {

            $update->set(array('practice_area_name' => $data['name']));
            $update->where(array('practice_area_id' => $data['practiceAreaId']));

        } elseif ($type == 'related_proceedings') {

            $update->set(array('rel_proceeding_name' => $data['name']));
            $update->where(array('rel_proceeding_id' => $data['relatedProcId']));

        } elseif ($type == 'role') {

            $update->set(array('role_name' => $data['name']));
            $update->where(array('role_id' => $data['employeeRole']));

        } elseif ($type == 'proceeding_subtype') {

            $update->set(array('proc_subtype_name' => $data['name']));
            $update->where(array('proc_subtype_id' => $data['proceedingSubTypeId']));

        } elseif ($type == 'proceeding_subtype_para') {

            $update->set(array('proc_st_para' => $data['name']));
            $update->where(array('proc_st_para_id' => $data['proceedingSubTypeParaId']));

        } elseif ($type == 'hearing_type') {
            $update->set(array('hearing_name' => $data['name']));
            $update->where(array('hearing_type_id' => $data['hearingTypeId']));

        } elseif ($type == 'hearing_sub') {

            if (isset($data['hearingTypeId'])) {
                $update->set(array(
                    'hearing_sub_name' => $data['name'],
                    'hearing_type_id' => $data['hearingTypeId']
                ));

            } else {
                $update->set(array('hearing_sub_name' => $data['name']));
            }

            $update->where(array('hearing_sub_id' => $data['hearingSubTypeId']));
        } elseif ($type == 'party_type') {

            $update->set(array('party_type_name' => $data['name']));
            $update->where(array('party_type_id' => $data['partyTypeId']));

        } elseif ($type == 'cj_history') {

            $update->set(array(
                'law_firm_ot' => $data['name'],
                'court_mun_ot' => $data['name']
            ));

            $update->where(array(
                'cj_history_id' => $data['lawCatalogId'],
                'cj_history_id' => $data['municioalityCatalogId'],

            ));
        } elseif ($type == 'cj_main') {

            $update->set(array('law_uni_ot' => $data['name']));
            $update->where(array('cj_id' => $data['lawUnivCatalogId']));
        } elseif ($type == 'dec_main') {

            $update->set(array('court_office_ot' => $data['name']));
            $update->where(array('dec_id' => $data['courtOfficeCatalogId']));
        } elseif ($type == 'users') {

            $update->set(array('city_name_ot' => $data['name']));
            $update->where(array('user_id' => $data['citiesCatalogId']));

        } elseif ($type == 'gender') {

            $update->set(array('gender_name' => $data['name']));
            $update->where(array('gender_id' => $data['genderrID']));

        } elseif ($type == 'cost_award_coverage') {

            $update->set(array('cac_name' => $data['name']));
            $update->where(array('cac_id' => $data['scaleCosts']));

        } elseif ($type == 'customers') {

            $update->where(array('customer_id' => $data['customersCatalogId']));
        } elseif ($type == 'dec_hearing_issue') {

            $update->set(array('hearing_issue_ot' => $data['name']));
            $update->where(array('dec_hearing_issue_id' => $data['IsuueCatalog']));
        }  elseif ($type == 'cj_position') {

            $update->set(array('cj_position_name' => $data['name']));
            $update->where(array('cj_position_id' => $data['cjPositionId']));
        } elseif ($type == "proceeding_party") {

            if (isset($data['add_alias_suffix'])) {
                $update->set(array('party_suffix_ot' => $data['name']));
                $update->where(array('proc_party_id' => $data['proceedingPartyId']));
            }


            //add alias
            if ($data['add-party-suffix']) {

                $update->set(
                    array(
                        'party_suffix_ot' => $data['name'],
                        'party_suffix' => $data['partySuffixId'],
                        'send_suffix_to_dropdown' => 0
                    )
                );

                $update->where(array('proc_party_id' => $data['proceedingPartyId']));
            }

            if (isset($data['add-alias-party-name'])) {

                $update->set(array(
                    'party_name_ot' => $data['name'],
                    'party_id' => $data['partyNameId'],
                    'send_name_to_dropdown' => 0

                ));
                $update->where(array('proc_party_id' => $data['add-alias-party-name']));
            }
            if (isset($data['partyNameAliasId'])) {
                $update->set(array(
                    'party_name_ot' => $data['name']
                ));
                $update->where(array('proceeding_Party_ID' => $data['partyNameAliasId']));
            }

        } elseif ($type == "party_name") {

            if ($data['do'] == "link_sugg") {

                $data = json_decode($data['data']);

                //Update Link Suggestions Parts
                return $data;
                foreach ($data as $row) {

                    $sql = new Sql($this->dbAdapter);
                    $updatePartyName = $sql->update($type);
                    $updateLinkParty = $sql->update('link_party');


                    $getLinkParty = $this->getLinkParty($row->parentLinkPartyId);
                    $PartyLinkCnt = $getLinkParty[0]['Party_Link_Cnt'] + 1;


                    //update link party

                    $updateLinkParty->set(array(
                        'party_link_cnt' => $PartyLinkCnt
                    ));

                    $updateLinkParty->where(array('link_party_id' => $row->parentLinkPartyId));
                    $selectString = $sql->getSqlStringForSqlObject($updateLinkParty);
                    $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);


                    //update party name
                    $updatePartyName->set(array(
                        'link_party_id' => $row->parentLinkPartyId
                    ));

                    $updatePartyName->where(array('party_id' => $row->childPartyId));
                    $selectString = $sql->getSqlStringForSqlObject($updatePartyName);
                    $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);


                }
                return $this->getPartiesLinkSuggestionsList();

            } elseif ($data['do'] == "unlink_parties") {


                $data = json_decode($data['data']);

                //Update Link Suggestions Parts
                foreach ($data as $row) {

                    $sql = new Sql($this->dbAdapter);
                    $updatePartyName = $sql->update($type);
                    $updateLinkParty = $sql->update('link_party');


                    $getLinkParty = $this->getLinkParty($row->parentLinkPartyId);
                    $PartyLinkCnt = $getLinkParty[0]['Party_Link_Cnt'] - 1;

                    //update link party

                    $updateLinkParty->set(array(
                        'party_link_cnt' => $PartyLinkCnt
                    ));

                    $updateLinkParty->where(array('link_party_id' => $row->parentLinkPartyId));
                    $selectString = $sql->getSqlStringForSqlObject($updateLinkParty);
                    $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

                    //update party name
                    $updatePartyName->set(array(
                        'link_party_id' => null
                    ));

                    $updatePartyName->where(array('party_id' => $row->childPartyId));
                    $selectString = $sql->getSqlStringForSqlObject($updatePartyName);
                    $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
                }
                return $this->getPartiesLinkList();

            } elseif ($data['do'] == "link_parties") {

                $data = json_decode($data['data']);

                $getLastLinkPartyRow = $this->getLastLinkPartyRow();

                $linkPartyGeneratedID = $getLastLinkPartyRow['link_party_id'] + 1;


                $c = 0;
                foreach ($data as $row) {

                    $sql = new Sql($this->dbAdapter);
                    $updatePartyName = $sql->update($type);

                    $updatePartyName->set(array(
                        'link_party_id' => $linkPartyGeneratedID
                    ));

                    $updatePartyName->where(array('party_id' => $row->childPartyId));
                    $selectString = $sql->getSqlStringForSqlObject($updatePartyName);
                    $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

                }

                return $this->getUnlinkedPartiesList();


            } else {
                $update->set(array('party_name' => $data['name']));
                $update->where(array('party_id' => $data['partyId']));
            }

        } elseif ($type == "law_firm") {

            $update->set(array('law_firm_name' => $data['name']));
            $update->where(array('law_firm_id' => $data['lawFirmId']));

        } elseif ($type == "law_university") {

            $update->set(array('law_school_name' => $data['name']));
            $update->where(array('law_school_id' => $data['lawUnivId']));
        } elseif ($type == "cause_list") {

            $update->set(array('cause_name' => $data['name']));
            $update->where(array('cause_id' => $data['causeID']));
        } elseif ($type == "issue") {

            $update->set(array('issue_name' => $data['name']));
            $update->where(array('issue_id' => $data['issueID']));
        } elseif ($type == "originating_process") {

        } elseif ($type == "outcome") {

            $update->set(array('outcome_name' => $data['name']));
            $update->where(array('outcome_id' => $data['outcomeID']));
        } elseif ($type == 'cost_award_coverage') {

            $update->set(array('cac_name' => $data['name']));
            $update->where(array('cac_id' => $data['cacID']));
        }

        $selectString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return 'success';
        } else {
            return 'fail';

        }

    }


    public function movetoDropdownField($data)
    {
        $type = $data['type'];

        return $this->updateField($data);

    }


    public function updateField($data)
    {

        try {
            $sql = new Sql($this->dbAdapter);
            $update = $sql->update($data['type']);

            if ($data['type'] == "state") {

                if (empty($data['countryId'])) {

                    return 'fail';
                } elseif (empty($data['stateId'])) {

                    return 'fail';
                }

                $update->set(
                    array(
                        'country_id' => $data['countryId']
                    )
                );

                $update->where(array('state_id' => $data['stateId']));

            } elseif ($data['type'] == "city") {


                if (empty($data['stateId'])) {

                    return 'fail';
                } elseif (empty($data['cityId'])) {

                    return 'fail';
                }


                $update->set(
                    array(
                        'state_id' => $data['stateId']
                    )
                );


                $update->where(array('city_id' => $data['cityId']));
            } elseif ($data['type'] == "court_type") {


                $update->set(
                    array(
                        'country_id' => $data['countryId'],
                        'state_id' => $data['stateId'],
                    )
                );


                $update->where(array('court_id' => $data['courtTypeId']));

            } elseif ($data['type'] == "court_region") {

                $update->set(
                    array(
                        'court_id' => $data['courtTypeId'],
                    )
                );

                $update->where(array('court_reg_id' => $data['courtRegId']));
            } elseif ($data['type'] == "proceeding_subtype") {

                $update->set(
                    array(
                        'proc_type_id' => $data['proceedingTypeId'],
                    )
                );
                $update->where(array('proc_subtype_id' => $data['proceedingSubTypeId']));

            } elseif ($data['type'] == "proceeding_subtype_para") {

                $update->set(
                    array(
                        'proc_subtype_id' => $data['proceedingSubTypeId'],
                    )
                );
                $update->where(
                    array('proc_st_para_id' => $data['proceedingSubTypeParaId'])
                );

            } elseif ($data['type'] == "hearing_sub") {

                $update->set(
                    array(
                        'hearing_type_id' => $data['hearingTypeId'],
                    )
                );
                $update->where(
                    array('hearing_sub_id' => $data['hearingSubTypeId'])
                );

            }

            $selectString = $sql->getSqlStringForSqlObject($update);
            $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
            if ($results) {
                return 'success';
            } else {
                return 'fail';

            }

        } catch (Exception $e) {


            return 'fail';
        }
    }


    public function deleteDropdownField($data)
    {

        $type = $data['type'];

        $sql = new Sql($this->dbAdapter);
        $delete = $sql->delete($type);

        if ($type == 'country') {
            $delete->where(array('country_id' => $data['countryId']));
        } elseif ($type == 'state') {
            $delete->where(array('state_id' => $data['stateId']));

        } elseif ($type == 'city') {
            $delete->where(array('city_id' => $data['cityId']));

        } elseif ($type == 'court_type') {
            $delete->where(array('court_id' => $data['courtId']));

        } elseif ($type == 'court_region') {

            $delete->where(array('court_reg_id' => $data['courtRegionId']));

        } elseif ($type == 'court_mun') {


            $delete->where(array('court_mun_id' => $data['courtMunId']));

        } elseif ($type == 'proceeding') {

            $delete->where(array('proceeding_id' => $data['proceedingId']));

        } elseif ($type == 'sub_proceeding') {


            $delete->where(array('sub_proc_id' => $data['subtypeProceedingId']));

            //proceedingTypeId
        } elseif ($type == 'proceeding_subtype_para') {

            $delete->where(array('sub_proc_para_id' => $data['subtypeProceedingParaId']));

        } elseif ($type == 'step_type') {

            $delete->where(array('step_type_id' => $data['dataSteptypeProceedingId']));
        } elseif ($type == 'sub_step') {

            $delete->where(array('sub_step_id' => $data['dataSubStepId']));

        } elseif ($type == 'party_type') {

            $delete->where(array('party_type_id' => $data['dataPartyTypeProceedingId']));
        }


        $selectString = $sql->getSqlStringForSqlObject($delete);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {

            if ($type == 'country') {
                return $this->showinactiveCountries(0);
            } elseif ($type == 'state') {
                return $this->getState($data['countryId']);

            } elseif ($type == 'city') {
                return $this->getCity($data['stateId']);
            } elseif ($type == 'court_type') {
                return $this->getCourtType($data['countryId'], $data['stateId']);
            } elseif ($type == 'court_region') {

                return $this->getCourtRegionList($data['courtTypeId']);

            } elseif ($type == "court_mun") {

                return $this->getCourtMunList($data['courtRegId'], $data['courtTypeId']);
            } elseif ($type == "proceeding") {


                return $this->getProceedingList();
            } elseif ($type == "proceeding_subtype") {


                return $this->getProceedingSub($data['proceedingTypeId']);
            } elseif ($type == 'proceeding_subtype_para') {

                return $this->getProceedingSubPara($data['proceedingSubtypeId']);
            } elseif ($type == 'step_type') {

                return $this->getStepTypes($data['dataCourtId']);
            } elseif ($type == 'sub_step') {

                return $this->getSubStepTypes($data['dataStepTypeId']);

            } elseif ($type == 'party_type') {

                return $this->getPartyType();
            }

        } else {
            return 'fail';

        }

    }

    public function getPgCount($docName)
    {
        $query = "SELECT pg_count
                  FROM dec_main
                  WHERE offline_document_name = '$docName'";
        $count = $this->getQueryResult($query, true)[0][0];
        return (int)$count;
    }


    public function addFields($data, $userId)
    {
        $DecSummaryWip = array(
            'dec_total_count' => $data['Dec_total_count'],
            'dec_mine_done_count' => $data['Dec_mine_done_count'],
            'dec_summary_done' => $data['Dec_summary_done'],
            'country_id' => $data['Country'],
            'state_id' => $data['State'],
            'court_type_id' => $data['Court_Type'],
            'proc_id' => 1,
            'dec_year' => $data['Dec_Year'],
            'dec_month' => $data['Dec_Month'],
        );
        echo "<pre>";
        print_r($DecSummaryWip);
        echo "</pre>";
        die;

        $decMain = array(
            'Link_Dec_ID' => $data[''],
            'Shortname' => $data[''],
            'Decname' => $data[''],            
            'Import_Log_ID' => $data[''],
            'Link_To_Src' => $data[''],
            'Dec_Date' => $data[''],
            'Citation_No' => $data[''],
            'Short_SoC' => $data[''],
            'Court_File_No' => $data[''],
            'proc_type_id' => $data[''],
            'Proc_Subtype_ID' => $data[''],
            'Proc_Subtype_OT' => $data[''],
            'Proc_Subtype_Para_ID' => $data[''],
            'country_id' => $data[''],
            'state_id' => $data[''],
            'Court_Type_ID' => $data[''],
            'Court_Municipality_ID' => $data[''],
            'Pres_auth_ID' => $data[''],
            'Pres_name_ID' => $data[''],
            'Pres_name_OT ' => $data[''],
        );

    }


    public function showinactiveCountries($status)
    {

        if ($status == 2) {

            $where = "";
        } else {

            $where = "WHERE name != NULL OR name != '' AND inactive = '$status' ";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM country
    			                              " . $where . "
    										  ORDER BY name");
        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    public function showinactiveEvents($status)
    {

        if ($status == 2) {

            $where = "";
        } else {

            $where = "WHERE inactive = '$status' ";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM court_list_event
    			                              " . $where . "
    										  ORDER BY name");
        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    public function showCjTitles()
    {

        $where = "";
        $groupBy = "";
        $orderBy = "WHERE name != NULL OR name != '' ";


        $statement = $this->dbAdapter->query("SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_cjtitile
    			                              FROM country c
    										  LEFT JOIN cj_title s
    										  ON c.country_id = s.country_id

    										  $where
    										  $groupBy
    										  $orderBy

    											ORDER BY name,cj_title_name");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $arr = array();
        foreach ($countries as $key => $value) {


            $arr[$value[0]][] = array('country_name' => $value['name'],
                'cj_title_name' => $value['cj_title_name'],
                'cj_title_id' => $value['cj_title_id'],
                'inactive_cjtitile' => $value['inactive_cjtitile'],
                'country_id' => $value['country_id']
            );

        }


        return $arr;

    }

    public function showinactiveCountriesForStatesAndCities($status)
    {

        $where = "";
        $groupBy = "";
        $orderBy = "WHERE name != NULL OR name != ''";

        $statement = $this->dbAdapter->query("SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
								    			FROM country c
								    			LEFT JOIN state s
								    			ON c.country_id = s.country_id

    											$where
								    			$groupBy
								    			$orderBy
								    			");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();


        $arr = array();
        foreach ($countries as $key => $value) {


            $stateId = $value['state_id'];

            $where = "WHERE c.state_id = '$stateId' ";

            $statement = $this->dbAdapter->query("SELECT * FROM city c
    				$where
    				");


            $results = $statement->execute();
            $cities = $results->getResource()->fetchAll();


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'cities' => $cities
            );


        }

        return $arr;

    }

    /**
     * Court Types
     *
     * @param unknown $status
     * @return Ambigous <multitype:, multitype:unknown >
     */


    public function ShowCitiesCatalog()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM users WHERE city_name_ot != NULL OR city_name_ot != ''

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }


    public function showMunicipalitiesCatalog()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM cj_history WHERE court_mun_ot != NULL OR court_mun_ot != '' AND job_position_ot != NULL OR job_position_ot != ''

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function showCountryCourtTypeStates($status)
    {

        $where = "";
        $groupBy = "";
        $orderBy = "WHERE name != NULL OR name != ''";

        $statement = $this->dbAdapter->query("
    			SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
    			FROM country c
    			LEFT JOIN state s
    			ON c.country_id = s.country_id

    			$where
    			$groupBy
    			$orderBy
    			ORDER BY name,state_name");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();


        $arr = array();
        foreach ($countries as $key => $value) {

            $countryId = $value['country_id'];
            $stateId = $value['state_id'];


            //courtTypesCountry
            $where = "WHERE c.country_id = '$countryId' AND c.state_id = '0' ";

            $statement = $this->dbAdapter->query("SELECT * FROM court_type c
    				$where
    				ORDER BY court_name");

            $results = $statement->execute();
            $courtTypesCountry = $results->getResource()->fetchAll();


            //courtTypesState
            $where = "WHERE c.country_id = '$countryId' AND c.state_id = '$stateId' ";

            $statement = $this->dbAdapter->query("SELECT * FROM court_type c
    					$where
    		ORDER BY court_name");

            $results = $statement->execute();
            $courtTypesState = $results->getResource()->fetchAll();


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'court_types_country' => $courtTypesCountry,
                'court_types_state' => $courtTypesState
            );
        }


        return $arr;

    }


    /**
     * Court Types
     *
     * @param unknown $status
     * @return Ambigous <multitype:, multitype:unknown >
     */

    public function showCountryCourtTypeStatesCjTitles($status)
    {

        $where = "";
        $groupBy = "";
        $orderBy = "ORDER BY c.country_id";

        $statement = $this->dbAdapter->query("
    			SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
    			FROM country c
    			LEFT JOIN state s
    			ON c.country_id = s.country_id

    			$where
    			$groupBy
    			$orderBy
    			");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();


        $arr = array();
        foreach ($countries as $key => $value) {

            $countryId = $value['country_id'];
            $stateId = $value['state_id'];


            //courtTypesCountry
            $where = "WHERE c.country_id = '$countryId' AND c.state_id = '0' ";

            $statement = $this->dbAdapter->query("SELECT * FROM court_type c
        		$where
        		");

            $results = $statement->execute();
            $courtTypesCountry = $results->getResource()->fetchAll();


            $courtTypesCountryArr = array();

            foreach ($courtTypesCountry as $key => $val) {

                $where = "WHERE court_type.court_id = '" . $val['court_id'] . "' ";

                $statement = $this->dbAdapter->query("SELECT * FROM court_type

        					");

                $results = $statement->execute();
                $cjTitleList = $results->getResource()->fetchAll();


                $courtTypesCountryArr[$val['court_id']] = array(
                    'court_id' => $val['court_id'],
                    'court_name' => $val['court_name'],
                    'country_id' => $val['country_id'],
                    'state_id' => $val['state_id'],
                    'inactive' => $val['inactive'],
                    'cjtitlelist' => $cjTitleList
                );

            }


            //courtTypesState
            $where = "WHERE c.country_id = '$countryId' AND c.state_id = '$stateId' ";

            $statement = $this->dbAdapter->query("SELECT * FROM court_type c
        		$where
        		");

            $results = $statement->execute();
            $courtTypesState = $results->getResource()->fetchAll();


            $courtTypesStateArr = array();
            foreach ($courtTypesState as $key => $val) {


                $where = "WHERE court_type.court_id = '" . $val['court_id'] . "' ";

                $statement = $this->dbAdapter->query("SELECT 	* FROM court_type

        					");

                $results = $statement->execute();
                $cjTitleList = $results->getResource()->fetchAll();

                $courtTypesStateArr[$val['court_id']] = array(
                    'court_id' => $val['court_id'],
                    'court_name' => $val['court_name'],
                    'country_id' => $val['country_id'],
                    'state_id' => $val['state_id'],
                    'inactive' => $val['inactive'],
                    'cjtitlelist' => $cjTitleList
                );

            }


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'court_types_country' => $courtTypesCountryArr,
                'court_types_state' => $courtTypesStateArr
            );
        }


        return $arr;

    }


    /**
     * Court Regions
     *
     * @param unknown $status
     * @return Ambigous <multitype:, multitype:unknown >
     */

    public function showCountryCourtTypesRegions($status)
    {

        $where = "";
        $groupBy = "";
        $orderBy = "ORDER BY c.country_id";

        $statement = $this->dbAdapter->query("
    			SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
    			FROM country c
    			LEFT JOIN state s
    			ON c.country_id = s.country_id

    		 ORDER BY state_name");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();


        $arr = array();
        foreach ($countries as $key => $value) {

            $countryId = $value['country_id'];
            $stateId = $value['state_id'];

            //Court_Types_Country
            $where = "WHERE c.country_id = '$countryId' AND c.state_id = '0' ";
            $statement = $this->dbAdapter->query("SELECT * FROM court_type c
    		$where
    		");

            $results = $statement->execute();
            $courtTypesCountry = $results->getResource()->fetchAll();

            //courtTypesRegionCountry

            $courtTypesCountryArr = array();
            foreach ($courtTypesCountry as $courtType) {


                $where = "WHERE cr.court_id = '$courtType[court_id]' ";

                $statement = $this->dbAdapter->query("
    					SELECT * FROM court_type ct
    					INNER JOIN court_region cr
    					ON cr.court_id = ct.court_id
    					$where
    				ORDER BY reg_name");

                $results = $statement->execute();
                $courtTypesCountryRegions = $results->getResource()->fetchAll();


                $courtTypesCountryArr[] = array(
                    "court_id" => $courtType["court_id"],
                    "court_name" => $courtType["court_name"],
                    "country_id" => $courtType["country_id"],
                    "state_id" => $courtType["state_id"],
                    "inactive" => $courtType["inactive"],
                    "Court_Types_Country_Regions" => $courtTypesCountryRegions
                );
            }


            //Court_Types_State
            $where = "WHERE c.country_id = '$countryId' AND c.state_id = '$stateId' ";

            $statement = $this->dbAdapter->query("SELECT * FROM court_type c
    				$where
    				");

            $results = $statement->execute();
            $courtTypesState = $results->getResource()->fetchAll();


            $courtTypesStateArr = array();
            foreach ($courtTypesState as $courtRegion) {

                $where = "WHERE cr.court_id = '$courtRegion[court_id]' ";

                $statement = $this->dbAdapter->query("
    					SELECT * FROM court_type ct
    					INNER JOIN court_region cr
    					ON cr.court_id = ct.court_id

    					$where

    					");


                $results = $statement->execute();
                $courtTypesStateRegions = $results->getResource()->fetchAll();


                $courtTypesStateArr[] = array(
                    'court_id' => $courtRegion['court_id'],
                    'court_name' => $courtRegion['court_name'],
                    'country_id' => $courtRegion['country_id'],
                    'state_id' => $courtRegion['state_id'],
                    'inactive' => $courtRegion['inactive'],
                    'Court_Types_state_Regions' => $courtTypesStateRegions
                );


            }


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'Court_Types_Country' => $courtTypesCountryArr,
                'Court_Types_State' => $courtTypesStateArr
            );
        }

        return $arr;
    }


    public function showCountryCourtTypesMun($status)
    {

        $where = "";
        $groupBy = "";
        $orderBy = "ORDER BY c.country_id";

        $statement = $this->dbAdapter->query("
    			SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
    			FROM country c
    			LEFT JOIN state s
    			ON c.country_id = s.country_id

    			$where
    			$groupBy
    			$orderBy
    			");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();


        $arr = array();
        foreach ($countries as $key => $value) {

            $countryId = $value['country_id'];
            $stateId = $value['state_id'];

            //Court_Types_Country
            $where = "WHERE c.country_id = '$countryId' AND c.state_id = '0' ";
            $statement = $this->dbAdapter->query("SELECT * FROM court_type c
    		$where
    		");

            $results = $statement->execute();
            $courtTypesCountry = $results->getResource()->fetchAll();

            //courtTypesRegionCountry

            $courtTypesCountryArr = array();
            foreach ($courtTypesCountry as $courtType) {


                $where = "WHERE cr.court_id = '$courtType[court_id]' ";

                $statement = $this->dbAdapter->query("
    		SELECT * FROM court_type ct
    		INNER JOIN court_region cr
    		ON cr.court_id = ct.court_id
    		$where
    		");

                $results = $statement->execute();
                $courtTypesCountryRegions = $results->getResource()->fetchAll();

                $courtTypesCountryRegionsArr = array();
                foreach ($courtTypesCountryRegions as $region) {


                    $where = "WHERE court_id = '$region[Court_ID]' AND court_reg_id = '$region[court_reg_id]' ";

                    $statement = $this->dbAdapter->query("
    					SELECT * FROM court_mun
    					 $where
    					");


                    $results = $statement->execute();
                    $courtTypesMun = $results->getResource()->fetchAll();

                    $courtTypesCountryRegionsArr[] = array(
                        'court_id' => $region['court_id'],
                        'court_name' => $region['court_name'],
                        'country_id' => $region['country_id'],
                        'state_id' => $region['state_id'],
                        'inactive' => $region['inactive'],
                        'court_reg_id' => $region['court_reg_id'],
                        'reg_name' => $region['reg_name'],
                        'Court_Mun_Items' => $courtTypesMun
                    );


                }

                $courtTypesCountryArr[] = array(
                    "court_id" => $courtType["court_id"],
                    "court_name" => $courtType["court_name"],
                    "country_id" => $courtType["country_id"],
                    "state_id" => $courtType["state_id"],
                    "inactive" => $courtType["inactive"],
                    "Court_Types_Country_Regions" => $courtTypesCountryRegionsArr
                );
            }


            //Court_Types_State
            $where = "WHERE c.country_id = '$countryId' AND c.state_id = '$stateId' ";

            $statement = $this->dbAdapter->query("SELECT * FROM court_type c
    				$where
    						");

            $results = $statement->execute();
            $courtTypesState = $results->getResource()->fetchAll();


            $courtTypesStateArr = array();
            foreach ($courtTypesState as $courtRegion) {

                $where = "WHERE cr.court_id = '$courtRegion[court_id]' ";

                $statement = $this->dbAdapter->query("
	    		SELECT * FROM court_type ct
	    		INNER JOIN court_region cr
	    		ON cr.court_id = ct.court_id
	    		$where
	    	");


                $results = $statement->execute();
                $courtTypesStateRegions = $results->getResource()->fetchAll();



                //Get court mun


                $courtTypesStateRegionsArr = array();
                foreach ($courtTypesStateRegions as $region) {


                    $where = "WHERE court_id = '$region[court_id]' AND court_reg_id = '$region[court_reg_id]' ";

                    $statement = $this->dbAdapter->query("
	    					SELECT * FROM court_mun
	    					$where
	    					");


                    $results = $statement->execute();
                    $courtTypesMun = $results->getResource()->fetchAll();

                    $courtTypesStateRegionsArr[] = array(
                        'court_id' => $region['court_id'],
                        'court_name' => $region['court_name'],
                        'country_id' => $region['country_id'],
                        'state_id' => $region['state_id'],
                        'inactive' => $region['inactive'],
                        'court_reg_id' => $region['court_reg_id'],
                        'reg_name' => $region['reg_name'],
                        'Court_Mun_Items' => $courtTypesMun
                    );


                }


                $courtTypesStateArr[] = array(
                    'court_id' => $courtRegion['court_id'],
                    'court_name' => $courtRegion['court_name'],
                    'country_id' => $courtRegion['country_id'],
                    'state_id' => $courtRegion['state_id'],
                    'inactive' => $courtRegion['inactive'],
                    'Court_Types_state_Regions' => $courtTypesStateRegionsArr
                );


            }


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'Court_Types_Country' => $courtTypesCountryArr,
                'Court_Types_State' => $courtTypesStateArr
            );
        }

        return $arr;


    }

    public function getPartySuffix()

    {

        $statement = $this->dbAdapter->query("

                                    SELECT * FROM party_suffix WHERE inactive = 0 AND  party_suffix_name != NULL OR party_suffix_name != '' ORDER BY party_suffix_name
	                                    LIMIT 200

                ");

        $results = $statement->execute();

        return $proceedingPartyList = $results->getResource()->fetchAll();

    }


    public function showinactiveStatesAndCities($countryId, $status)
    {

        $where = "WHERE s.country_id = '$countryId' ";

        $statement = $this->dbAdapter->query("
    						SELECT *, c.inactive inactive, s.inactive inactive_state
    						FROM state s
    						LEFT JOIN country c
    						ON s.country_id = c.country_id
    						$where
    			");

        $results = $statement->execute();
        $states = $results->getResource()->fetchAll();


        foreach ($states as $key => $value) {

            $stateId = $value['State_ID'];

            $where = "WHERE c.state_id = '$stateId' ";

            $statement = $this->dbAdapter->query("SELECT * FROM city c
    				$where
    				");

            $results = $statement->execute();
            $cities = $results->getResource()->fetchAll();


            $arr[] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'cities' => $cities
            );
        }

        return $arr;
    }


    public function setinactive($status, $id, $table)
    {


        $sql = new Sql($this->dbAdapter);
        $update = $sql->update($table);

        $update->set(
            array(
                'inactive' => $status,
            )
        );

        if ($table == "country") {
            $update->where(array('country_id' => $id));
        } elseif ($table == "state") {
            $update->where(array('state_id' => $id));
        } elseif ($table == "city") {
            $update->where(array('city_id' => $id));
        } elseif ($table == "court_type") {
            $update->where(array('court_id' => $id));
        } elseif ($table == "court_region") {
            $update->where(array('court_reg_id' => $id));
        } elseif ($table == "role") {
            $update->where(array('role_id' => $id));
        } elseif ($table == "customer_role") {
            $update->where(array('cust_role_id' => $id));
        } elseif ($table == "doc_source") {
            $update->where(array('source_id' => $id));
        } elseif ($table == "court_mun") {
            $update->where(array('court_mun_id' => $id));
        } elseif ($table == "doc_format") {
            $update->where(array('doc_format_id' => $id));
        } elseif ($table == "court_office") {
            $update->where(array('court_office_id' => $id));
        } elseif ($table == "proceeding_category") {
            $update->where(array('proc_cat_id' => $id));
        } elseif ($table == "proceeding_type") {
            $update->where(array('proc_type_id' => $id));
        } elseif ($table == "practice_area") {
            $update->where(array('practice_area_id' => $id));
        } elseif ($table == "court_office_room") {
            $update->where(array('court_office_room_ID' => $id));
        } elseif ($table == "related_proceedings") {
            $update->where(array('rel_proceeding_id' => $id));
        } elseif ($table == "proceeding_subtype") {
            $update->where(array('proc_subtype_id' => $id));
        } elseif ($table == "proceeding_subtype_para") {
            $update->where(array('proc_st_para_id' => $id));
        } elseif ($table == "hearing_type") {
            $update->where(array('hearing_type_id' => $id));
        } elseif ($table == "hearing_sub") {
            $update->where(array('hearing_sub_id' => $id));
        } elseif ($table == "hearing_party_type") {
            $update->where(array('hearing_party_type_id' => $id));
        } elseif ($table == "dec_hearing_date_other") {
            $update->where(array('dec_hearing_date_other_id' => $id));
        } elseif ($table == "court_list_event") {
            $update->where(array(' court_list_event_id' => $id));
        } elseif ($table == "gender") {
            $update->where(array('gender_id' => $id));
        } elseif ($table == "party_size") {
            $update->where(array('party_size_id' => $id));
        } elseif ($table == "party_type") {

            $update->where(array('party_type_id' => $id));

        } elseif ($table == "party_category") {

            $update->where(array('party_category_id' => $id));

        } elseif ($table == "referral_source") {

            $update->where(array('referral_source_id' => $id));

        } elseif ($table == "party_position") {

            $update->where(array('party_position_id' => $id));

        } elseif ($table == "party_suffix") {
            $update->where(array('party_suffix_id' => $id));

        } elseif ($table == "proceeding_party") {

            $update->where(array("proc_party_id" => $id));

        } elseif ($table == "cj_type") {


            $update->where(array('cj_type_id' => $id));

        } elseif ($table == "cj_title") {

            $update->where(array('cj_title_id' => $id));

        } elseif ($table == "cj_main") {

            $update->where(array('cj_id' => $id));

        } elseif ($table == "law_firm") {

            $update->where(array('law_firm_id' => $id));
        } elseif ($table == "law_university") {

            $update->where(array('law_school_id' => $id));

        } elseif ($table == "cause_list") {
            $update->where(array('Cause_ID' => $id));
        } elseif ($table == "issue") {
            $update->where(array('Issue_ID' => $id));
        } elseif ($table == "outcome") {
            $update->where(array('outcome_id' => $id));
        } elseif ($table == "damage_heads") {
            $update->where(array('dmg_head_id' => $id));
        } elseif ($table == 'cost_award_coverage') {
            $update->where(array('cac_id' => $id));
        } elseif ($table == 'industry') {
            $update->where(array('industry_id' => $id));
        }

        $selectString = $sql->getSqlStringForSqlObject($update);

        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return 'success';
        } else {
            return 'fail';
        }
    }

    public function showCountryStatesLawFirms()
    {
        $statement = $this->dbAdapter->query("
            SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
            FROM country c
            LEFT JOIN state s
              ON c.country_id = s.country_id
            WHERE  name != NULL OR name != ''
              AND c.inactive = 0
        ");

        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $arr = array();

        foreach ($countries as $key => $value) {
            $statement = $this->dbAdapter->query("
                SELECT * FROM law_firm
                WHERE country_id = '" . $value['country_id'] . "'
                    AND state_id = '" . $value['state_id'] . "'
                    AND inactive = 0
            ");

            $results = $statement->execute();
            $lawFirms = $results->getResource()->fetchAll();

            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'Law_Firms_List' => $lawFirms
            );
        }

        return $arr;
    }

    public function showCountryStatesLawUniversities()
    {
        $where = "";
        $groupBy = "";
        $orderBy = "WHERE  name != NULL OR name != '' ";

        $statement = $this->dbAdapter->query("SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
    			FROM country c
    			LEFT JOIN state s
    			ON c.country_id = s.country_id
    			$where
    			$groupBy
    			$orderBy

    			ORDER BY name,state_name");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $arr = array();
        foreach ($countries as $key => $value) {

            $where = "WHERE country_id = '" . $value['country_id'] . "' AND state_id = '" . $value['state_id'] . "'";

            $statement = $this->dbAdapter->query("SELECT * FROM law_university
    					$where
    					ORDER BY law_school_name");

            $results = $statement->execute();
            $lawUniversity = $results->getResource()->fetchAll();


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'Law_School_List' => $lawUniversity
            );
        }
        return $arr;
    }


    public function showInacLawUniversities()
    {
        $where = "";
        $groupBy = "";
        $orderBy = "WHERE  name != NULL OR name != '' ";

        $statement = $this->dbAdapter->query("SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
    			FROM country c
    			LEFT JOIN state s
    			ON c.country_id = s.country_id
    			$where
    			$groupBy
    			$orderBy

    			ORDER BY name,state_name");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $arr = array();
        foreach ($countries as $key => $value) {

            $where = "WHERE country_id = '" . $value['country_id'] . "' AND state_id = '" . $value['state_id'] . "' AND inactive = 0";

            $statement = $this->dbAdapter->query("SELECT * FROM law_university
    					$where
    					ORDER BY law_school_name");

            $results = $statement->execute();
            $lawUniversity = $results->getResource()->fetchAll();


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'Law_School_List' => $lawUniversity
            );
        }
        return $arr;
    }

    public function showCountryStatesCjNames()
    {
        return null;
        $where = "";
        $groupBy = "";
        $orderBy = "ORDER BY c.Name";

        $statement = $this->dbAdapter->query("SELECT *, c.country_id country_id, c.inactive inactive, s.inactive inactive_state
    				FROM country c
    				LEFT JOIN state s
    				ON c.country_id = s.country_id
    				$where
    				$groupBy
    				$orderBy

    				");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $arr = array();
        foreach ($countries as $key => $value) {

            $where = "WHERE country_id = '" . $value['country_id'] . "' AND state_id = '" . $value['state_id'] . "' ";

            $statement = $this->dbAdapter->query("SELECT * FROM law_university
    		    					$where
    		    					");

            $results = $statement->execute();
            $lawUniversity = $results->getResource()->fetchAll();


            $arr[$value['country_id']][] = array('Country_Name' => $value['name'],
                'state_name' => $value['state_name'],
                'state_id' => $value['state_id'],
                'inactive_state' => $value['inactive_state'],
                'country_id' => $value['country_id'],
                'Law_School_List' => $lawUniversity
            );
        }
        return $arr;

    }


    public function showCountryCjType()
    {

        $statement = $this->dbAdapter->query("SELECT *, c.country_id country_id FROM country c
	    				LEFT JOIN cj_type ct
	    				ON ct.country_id = c.country_id
    					WHERE c.inactive = 0
    				");

        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $arr = array();

        foreach ($countries as $item) {

            $arr[$item['country_id']][] = array(

                "country_id" => $item["country_id"],

                "name" => $item["name"],
                "inactive" => $item["inactive"],
                "cj_type_id" => $item["cj_type_id"],
                "cj_type_name" => $item["cj_type_name"],
                "is_pres" => $item["is_pres"]

            );

        }


        return $arr;
    }

    public function getPartyName($partyId)
    {
        $statement = $this->dbAdapter->query("
    				SELECT * FROM party_name
    				WHERE party_id = '$partyId'
    		");

        $results = $statement->execute();
        return $results->getResource()->fetch();
    }


    public function showCountryCJName()
    {
        $statement = $this->dbAdapter->query("
    				SELECT * FROM party_name
    				");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function showCountryCJList()
    {

        $statement = $this->dbAdapter->query("
    				SELECT *, cm.inactive as inactive, cm.country_id as country_id
    				FROM cj_main cm

    				LEFT JOIN country c
    				ON cm.country_id = c.country_id

    				LEFT JOIN cj_history
    				ON cj_history.cj_id = cm.cj_id

    				GROUP BY cm.cj_id



    				");

        $results = $statement->execute();
        $cjMain = $results->getResource()->fetchAll();

        $cjMainArr = array();
        foreach ($cjMain as $key => $value) {

            $cjMainArr[$value['country_id']][] = array(
                'cj_id' => $value['cj_id'],
                'cj_fn' => $value['cj_fn'],
                'cj_mn' => $value['cj_mn'],
                'cj_ln' => $value['cj_ln'],
                'country_id' => $value['country_id'],
                'call_to_bar' => $value['call_to_bar'],
                'law_uni_id' => $value['law_uni_id'],
                'law_uni_ot' => $value['law_uni_ot'],
                'link_cj_id' => $value['link_cj_id'],
                'inactive' => $value['inactive'],
                'country_name' => $value['name'],
                'cj_history_id' => $value['cj_history_id'],
                'cj_type_id' => $value['cj_type_id'],
                'state_id' => $value['state_id'],
                'law_firm_id' => $value['law_firm_id'],
                'law_firm_ot' => $value['law_firm_ot'],
                'cj_title_id' => $value['cj_title_id'],
                'title_ot' => $value['title_ot'],
                'court_id' => $value['court_id'],
                'court_reg_id' => $value['court_reg_id'],
                'court_mun_id' => $value['court_mun_id'],
                'court_mun_ot' => $value['court_mun_ot'],
                'start_date' => $value['start_date'],
                'end_date' => $value['end_date'],
                'update_date' => $value['update_date']
            );
        }

        return $cjMainArr;
    }


    public function showCJNamesToCatalog()
    {

        $statement = $this->dbAdapter->query("
    				SELECT * FROM proceeding_dec_counsel adc
    				LEFT JOIN dec_pres_auth dpa
    				ON adc.cj_history_id = dpa.cj_history_id
    				WHERE adc.cj_history_id IS NULL
     					  LIMIT 1000
    				");
        $results = $statement->execute();
        $arr = $results->getResource()->fetchAll();
        return $arr;
    }

    public function showToCatalog()
    {

        $statement = $this->dbAdapter->query("
    				SELECT * FROM cj_history WHERE  law_firm_ot != NULL OR law_firm_ot != '' ORDER BY law_firm_ot
    				");

        $results = $statement->execute();
        $arr = $results->getResource()->fetchAll();
        return $arr;
    }

    public function ShowUniLawCatalog()
    {

        $statement = $this->dbAdapter->query("
    				SELECT * FROM cj_main WHERE  law_uni_ot != NULL OR law_uni_ot != '' ORDER BY law_uni_ot
    				");

        $results = $statement->execute();
        $arr = $results->getResource()->fetchAll();
        return $arr;
    }

    public function showCJNames()
    {

        $where = "WHERE adc.cj_history_id IS NULL";

        $statement = $this->dbAdapter->query("
    				SELECT * FROM procs_dec_counsel adc
    				LEFT JOIN dec_pres_auth dpa
    				ON adc.cj_history_id = dpa.cj_history_id
    				$where
     				LIMIT 1000
    		");

        $results = $statement->execute();
        $arr = $results->getResource()->fetchAll();
        return $arr;

    }


    function hearingeedingList()
    {


        $statement = $this->dbAdapter->query("
    				SELECT * FROM proceeding_main pm
    				WHERE court_mun_ot != NULL OR court_mun_ot != ''
    		");

        $results = $statement->execute();
        $arr = $results->getResource()->fetchAll();
        return $arr;
    }


    public function causeList()
    {
        $where = "";

        $statement = $this->dbAdapter->query("
    				SELECT * FROM cause_list op
    				$where
    				LIMIT 1000
    				");

        $results = $statement->execute();
        $arr = $results->getResource()->fetchAll();

        return $arr;
    }

    public function costAwardCoverageList()
    {

        $statement = $this->dbAdapter->query("
                        SELECT * FROM cost_award_coverage WHERE cac_id != 1
                        LIMIT 1000
                        ");

        $results = $statement->execute();
        $arr = $results->getResource()->fetchAll();

        return $arr;
    }


    public function showCourtRegions($post = null)
    {

        $where = "";
        if (!empty($post['courtTypeId'])) {
            $where = "WHERE court_id = '" . $post['courtTypeId'] . "' ";
        }

        $statement = $this->dbAdapter->query("
    			SELECT * FROM court_region
    			$where
    		");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function showCauseProceedingList($post = null)
    {
        $where = '';
        if ($post['action'] == "show" && $post["status"] != "all") {
            $status = ($post["status"] == "active") ? 0 : 1;
            $where = "WHERE inactive = $status";
        }
        $statement = $this->dbAdapter->query("
    				SELECT * FROM cause_list
    				$where
    		");


        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function showIssueList($post = null)
    {

        $where = '';
        if ($post['action'] == "show" && $post["status"] != "all") {
            $status = ($post["status"] == "active") ? 0 : 1;
            $where = "WHERE inactive = $status";
        }
        $statement = $this->dbAdapter->query("
    				SELECT * FROM issue
    				$where
    		");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function showOriginProc($post = null)
    {
        $where = '';
        if ($post['action'] == "show" && $post["status"] != "all") {
            $status = ($post["status"] == "active") ? 0 : 1;
            $where = "WHERE inactive = $status";
        }
        $statement = $this->dbAdapter->query("SELECT * FROM originating_process
                                       $where
                                      ORDER BY OP_name ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function showOutcome($post = null)
    {
        $where = '';
        if ($post['action'] == "show" && $post["status"] != "all") {
            $status = ($post["status"] == "active") ? 0 : 1;
            $where = "WHERE inactive = $status";
        } elseif ($where = " WHERE outcome_name != NULL OR outcome_name != ''") {

        }
        $statement = $this->dbAdapter->query("SELECT * FROM outcome
                                      $where
                                      ORDER BY outcome_name");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function showeOfficeList($post = null)
    {

        $statement = $this->dbAdapter->query("SELECT * FROM court_office
                                  ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function showCAC($post = null)
    {
        $where = '';
        if ($post['action'] == "show" && $post["status"] != "all") {
            $status = ($post["status"] == "active") ? 0 : 1;
            $where = "WHERE inactive = $status";
        }
        $statement = $this->dbAdapter->query("SELECT * FROM cost_award_coverage
                                      $where
                                      ORDER BY cac_name");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function updateCJMainRecord($post)
    {
        parse_str($post['cjNamesForm'], $cjNamesForm);
        $type = $post['type'];
        $cjMainID = $post['cjMainID'];

        $sql = new Sql($this->dbAdapter);

        $update = $sql->update($type);
        $update->set(array(
            'cj_fn' => trim($cjNamesForm['first_name']),
            'cj_mn' => trim($cjNamesForm['middle_name']),
            'cj_ln' => trim($cjNamesForm['last_name']),
            'country_id' => trim($cjNamesForm['cjname-country']),
            'call_to_bar' => trim($cjNamesForm['call-to-bar']),
            'law_uni_id' => trim($cjNamesForm['law-university']),
            'law_uni_ot' => trim($cjNamesForm['other_university'])
        ));
        $update->where(array('cj_id' => $cjMainID));

        $selectString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        if ($results) {
            return 'success';
        } else {
            return 'fail';
        }

    }


    public function updateCJRecordHistory($post)
    {
        parse_str($post['cjHistoryRecordForm'], $cjHistoryRecordForm);

        $type = $post['type'];
        $cjHistoryId = $post['cjHistoryId'];

        $sql = new Sql($this->dbAdapter);
        $update = $sql->update("cj_history");

        $update->set($cjHistoryRecordForm);

        $update->where(array('CJ_History_ID' => $cjHistoryId));

        $selectString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        if ($results) {
            return 'success';
        } else {
            return 'fail';

        }
    }


    public function updateCJRecordHistoryAlias($proceedingDecCounselID, $cjHistoryId)
    {

        $sql = new Sql($this->dbAdapter);
        $update = $sql->update("procs_dec_counsel");


        $data = array("cj_history_id" => $cjHistoryId);


        $update->set($data);

        $update->where(array('proc_dec_counsel_id' => $proceedingDecCounselID));

        $selectString = $sql->getSqlStringForSqlObject($update);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        if ($results) {
            return 'success';
        } else {
            return 'fail';

        }


    }


    public function addCJMainRecord($post)
    {
        $sql = new Sql($this->dbAdapter);

        parse_str($post['cjNamesForm'], $cjNamesForm);
        parse_str($post['cjHistoryRecordForm'], $cjHistoryRecordForm);
        $type = $post['type'];
        $cjMainID = $post['cjMainID'];

        $newData = array(
            'cj_fn' => trim($cjNamesForm['first_name']),
            'cj_mn' => trim($cjNamesForm['middle_name']),
            'cj_ln' => trim($cjNamesForm['last_name']),
            'country_id' => trim($cjNamesForm['cjname-country']),
            'call_to_bar' => trim($cjNamesForm['call-to-bar']),
            'law_uni_id' => trim($cjNamesForm['law-university']),
            'law_uni_ot' => trim($cjNamesForm['other_university'])
        );

        $insert = $sql->insert($type);
        $insert->values($newData);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        $cjMainId = $this->dbAdapter->getDriver()->getLastGeneratedValue();
        $sql = new Sql($this->dbAdapter);

        $insert = $sql->insert("cj_history");
        $insert->values($cjHistoryRecordForm);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }


    /**
     * Adds CJ Main and History record
     *
     * @param unknown $post
     * @return string
     */
    public function addCJMainRecordHistory($post)
    {
        parse_str($post['cjHistoryRecordForm'], $cjHistoryRecordForm);
        parse_str($post['cjNamesForm'], $cjNamesForm);

        $type = $post['type'];
        $cjMainID = $post['cjMainID'];
        $proceedingDecCounselID = $post['proceedingDecCounselID'];

        $sql = new Sql($this->dbAdapter);
        $newData = array(
            'cj_fn' => trim($cjNamesForm['first_name']),
            'cj_mn' => trim($cjNamesForm['middle_name']),
            'cj_ln' => trim($cjNamesForm['last_name']),
            'country_id' => trim($cjNamesForm['cjname-country']),
            'call_to_bar' => trim($cjNamesForm['call-to-bar']),
            'law_uni_id' => trim($cjNamesForm['law-university']),
            'law_uni_ot' => trim($cjNamesForm['other_university'])
        );

        $insert = $sql->insert("cj_main");
        $insert->values($newData);

        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        $cjMainId = $this->dbAdapter->getDriver()->getLastGeneratedValue();

        $sql = new Sql($this->dbAdapter);

        $cjHistoryRecord = array_merge($cjHistoryRecordForm, array("cj_id" => $cjMainId));


        $insert = $sql->insert("cj_history");
        $insert->values($cjHistoryRecord);

        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        $cjHistoryId = $this->dbAdapter->getDriver()->getLastGeneratedValue();

        if ($results) {

            //Alias Adding
            $update = $sql->update("procs_dec_counsel");
            $update->set(array(
                'cj_history_id' => $cjHistoryId
            ));

            $update->where(array('proc_dec_counsel_id' => $proceedingDecCounselID));

            $selectString = $sql->getSqlStringForSqlObject($update);
            $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

            if ($results) {
                return 'success';
            } else {
                return 'fail';
            }

        } else {
            return "fail";
        }


    }


    public function addCJRecordHistory($post)
    {

        parse_str($post['cjHistoryRecordForm'], $cjHistoryRecordForm);
        $type = $post['type'];
        $cjMainID = $post['cjMainID'];


        $sql = new Sql($this->dbAdapter);
        $cjHistoryRecordForm = array_merge($cjHistoryRecordForm, array("cj_id" => $cjMainID));

        $insert = $sql->insert("cj_history");
        $insert->values($cjHistoryRecordForm);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        if ($results) {
            return 'success';
        } else {
            return 'fail';
        }

    }


    public function addCjRecordAndAlias($post)
    {
        $cjHistoryRecordForm = parse_str($post['cjHistoryRecordForm'], $cjHistoryRecord);


        $proceedingDecCounselID = $post['proceedingDecCounselID'];
        $historyRecordId = $post['historyRecordId'];
        $cjMainID = $post['cjMainID'];

        //add CJ Record History
        $sql = new Sql($this->dbAdapter);
        $cjHistoryRecord = array_merge($cjHistoryRecord, array("cj_id" => $cjMainID));

        $insert = $sql->insert("cj_history");
        $insert->values($cjHistoryRecord);
        $selectString = $sql->getSqlStringForSqlObject($insert);
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

        $cjHistoryId = $this->dbAdapter->getDriver()->getLastGeneratedValue();

        if ($results) {

            $update = $sql->update("procs_dec_counsel");

            $data = array("cj_history_id" => $cjHistoryId);

            $update->set($data);
            $update->where(array('proc_dec_counsel_id' => $proceedingDecCounselID));

            $selectString = $sql->getSqlStringForSqlObject($update);
            $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);

            if ($results) {
                return 'success';
            } else {
                return 'fail';
            }


            return 'success';
        } else {
            return 'fail';
        }


    }

    public function getSearchPdfFiles($post)
    {
        $where = "WHERE offline_document_name = '$post[q]'";
        $statement = $this->dbAdapter->query("
                                    SELECT * FROM dec_main
                    ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getHearingSub($post)
    {
        $where = "WHERE hearing_sub_id = '$post[hearingTypeId]'";
        $statement = $this->dbAdapter->query("
                                    SELECT * FROM hearing_sub
                    ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }


    public function proceedingList()
    {

        $statement = $this->dbAdapter->query("
                                    SELECT * FROM proceeding_main WHERE Proc_ID != 1
                    ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

	
    public function presidingAuthList($post = null)
    {
       
        $where = "WHERE dpa.dec_id = '" . $post['decMainID'] . "'";

        if ($post == null) {
            $sql = "SELECT dec_pres_auth_id,pres_name_ot
                            FROM dec_pres_auth";
        } else {
            $sql = "SELECT dpa.dec_pres_auth_id, h.cj_type_id, dpa.cj_history_id, dpa.cj_id_suggest,dpa.is_chair, dpa.is_dec_author, dpa.pres_name_ot, dpa.ot_comments, m.country_id, m.cj_fn, m.cj_mn, m.cj_ln, m.cj_id, cjt.cj_type_name
                            FROM dec_pres_auth AS dpa
                            LEFT JOIN cj_history AS h ON dpa.cj_history_id = h.cj_history_id
                            LEFT JOIN cj_main AS m ON h.cj_id = m.cj_id
                            LEFT OUTER JOIN cj_type AS cjt ON h.cj_type_id = cjt.cj_type_id
                            $where";
        }
        $statement = $this->dbAdapter->query($sql);
        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function presidingAuthListSearch($post = null)
    {
        $where = "WHERE dpa.dec_pres_auth_id = '" . $post['id'] . "'";
        if ($post == null) {
            $sql = "SELECT dec_pres_auth_id,pres_name_ot
                                    FROM dec_pres_auth";
        } else {
            $sql = "SELECT dpa.dec_pres_auth_id, dpa.cj_type_id, dpa.cj_history_id, dpa.cj_id_suggest, dpa.pres_name_ot, dpa.ot_comments, m.country_id
                                    FROM dec_pres_auth AS dpa
                                    LEFT JOIN cj_history AS h ON dpa.cj_history_id = h.cj_history_id
                                    LEFT JOIN cj_main AS m ON h.cj_id = m.cj_id
                                    $where";
        }
        $statement = $this->dbAdapter->query($sql);
        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    
    public function counselProcList($post = null)
    {
        if ($post == null) {
            $sql = "SELECT proc_id,short_soc
                            FROM proceeding_main";
        } else {

            $where = "WHERE pm.`proc_id` = '" . $post['id'] . "' AND pm.`proc_type_id`=ps.`proc_type_id` AND pm.`proc_id`=psl.`proc_id` AND psl.`proc_subtype_id`= ps.`proc_subtype_id` AND ps.`proc_subtype_id`= psp.`proc_subtype_id`";

            $sql = "SELECT *
                            FROM proceeding_main pm, proceeding_subtype ps, proceeding_subtype_para psp,  proc_subtype_list AS psl
                            $where";

        }
        $statement = $this->dbAdapter->query($sql);
        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function counselProcListName($post = null)
    {
    
        $where = "WHERE pm.`Proc_ID` = '" . $post['id'] . "'";
        $sql = "SELECT *
                            FROM proceeding_main AS pm
                            LEFT JOIN proceeding_party AS pp ON pm.Proc_ID= pp.Proc_ID
                            LEFT JOIN party_name AS pn ON pp.party_id= pn.party_id
                            $where";

        $statement = $this->dbAdapter->query($sql);
        $results = $statement->execute();
        
        return $results->getResource()->fetchAll();
    }

    public function getProceedingPartySearch($post = null)
    {

    }

    public function getProceedingParty($post = null)
    {
        if ($post == null) {
            $query = "SELECT * FROM proceeding_party AS ap
                                LEFT JOIN proceeding_party_type AS apt ON apt.proc_party_id = ap.proc_party_id
                                LEFT JOIN party_type AS pt ON apt.party_type_id = pt.party_type_id

                                LEFT JOIN proceeding_party_position AS app ON ap.proc_party_id = app.proc_party_id
                                LEFT JOIN party_position AS pp ON app.proc_party_id = pp.party_position_id

                                LEFT JOIN party_suffix AS ps ON ap.party_suffix = ps.party_suffix_id

                                LEFT JOIN party_name AS pn ON ap.party_id = pn.party_id

                                ";
           


            $statement = $this->dbAdapter->query($query);

            $results = $statement->execute();
            return $results->getResource()->fetchAll();
        } elseif ($post['proceedingPartyId'] != '') {
            $where = "WHERE ap.proc_id = '" . $post['proceedingPartyId'] . "'";

            $query = "SELECT * FROM proceeding_party AS ap
                                LEFT JOIN proceeding_party_type AS apt ON apt.proc_party_id = ap.proc_party_id
                                LEFT JOIN party_type AS pt ON apt.party_type_id = pt.party_type_id

                                LEFT JOIN proceeding_party_position AS app ON ap.proc_party_id = app.proc_party_id
                                LEFT JOIN party_position AS pp ON app.proc_party_id = pp.party_position_id

                                LEFT JOIN party_suffix AS ps ON ap.Party_suffix = ps.party_suffix_id

                                LEFT JOIN party_name AS pn ON ap.party_id = pn.party_id
                                $where";
            $statement = $this->dbAdapter->query($query);

            $results = $statement->execute();
            return $results->getResource()->fetchAll();
        } else {
          
            $where = "WHERE ap.proc_id = '" . $post['proceedingPartyId'] . "'";

            $query = "SELECT * FROM proceeding_party AS ap
                                LEFT JOIN proceeding_party_type AS apt ON ap.proc_party_id = apt.PROC_PARTY_ID
                                LEFT JOIN proceeding_party_position AS app ON ap.proc_party_id = app.Proc_Party_ID
                                $where ";

            $statement = $this->dbAdapter->query($query);

            $results = $statement->execute();
            return $results->getResource()->fetchAll();
        }


    }

    // proceeding_subtype_para_list
    public function getProceedingSubtypeParaList()
    {
        $statement = $this->dbAdapter->query("
            SELECT * FROM proceeding_subtype
            WHERE inactive = 0
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getPartyPosition()
    {
        $statement = $this->dbAdapter->query("
                                       SELECT *,party_position.inactive,party_position.proc_type_id,proceeding_type.proc_type_id,proceeding_subtype.proc_subtype_id
                                       FROM party_position , proceeding_type ,proceeding_subtype
                                       WHERE party_position.proc_type_id = proceeding_type.proc_type_id AND
                                       party_position.proc_subtype_id = proceeding_subtype.proc_subtype_id
                                                                                ORDER BY party_position_name
                    ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getCitNo($citNo)
    {

        $statement = $this->dbAdapter->query("
                                    SELECT * FROM dec_main
                                    WHERE citation_no LIKE '$citNo%'");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getDecMainList($post = null)
    {
        if ($post == null) {
            $statement = $this->dbAdapter->query("
                                    SELECT * FROM dec_main AS dm
                                    LEFT JOIN dec_hearing_date AS dhd ON dm.dec_id = dhd.dec_id
                    ");

            $results = $statement->execute();
            return $results->getResource()->fetchAll();
        } else {
            $where = "WHERE dm.dec_id = '" . $post['id'] . "'";
            $statement = $this->dbAdapter->query("
                                    SELECT * FROM dec_main AS dm
                                    LEFT JOIN dec_hearing_date AS dhd ON dm.dec_id = dhd.dec_id
                                    $where
                    ");

            $results = $statement->execute();
            return $results->getResource()->fetchAll();
        }
    }

    public function getProceedingMainListInDec($post = null)
    {

        $mainProceedingId = intval($post['mainProceedingId']);

        $where = "";
        if (isset($post['mainProceedingId'])) {
            $where = "WHERE dec_id = '" . $mainProceedingId . "'";
        }

        $statement = $this->dbAdapter->query("
                                            SELECT proc_id FROM proc_in_dec

                                            $where
                            ");

        $results = $statement->execute();
        return $results->getResource()->fetch();
    }

    public function getProceedingIdInDec($post = null)
    {
        $decMainIdSearch = intval($post['decMainIdSearch']);
        $where = "";
        if (isset($post['mainProceedingId'])) {
            $where = "WHERE dec_id = '" . $decMainIdSearch . "'";
        }
        $statement = $this->dbAdapter->query("
                                                    SELECT proc_id FROM proc_in_dec
                                                    $where
                                    ");
        $results = $statement->execute();
        return $results->getResource()->fetch();
    }

    public function getProceedingMainList($post = null)
    {

        $where = "";
        if (isset($post)) {
            $where = "WHERE proc_id = '" . $post . "'";
        }

        $statement = $this->dbAdapter->query("
                                    SELECT * FROM proceeding_main
                                    /*LEFT JOIN proceeding_cause AS pc ON pm.Proc_ID = pc.Proc_ID*/
                                    $where
                    ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getProceedingPartyList($post = null)
    {


        $where = "";
        if (isset($post)) {
            
            $where = "WHERE pid.dec_id = '" . $post . "'";

        }

        $statement = $this->dbAdapter->query("
                                    SELECT * FROM proc_in_dec AS pid
                                    LEFT JOIN proceeding_main AS pm ON pm.proc_id = pid.proc_id
                              /*      LEFT JOIN proceeding_cause AS pc ON pm.Proc_ID = pc.Proc_ID */
                                    $where
                    ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    
    public function getCJNamesSuggest($historyID = null)
    {
        if (!empty($historyID)) {
            $where = "WHERE `cj_history_id` = $historyID AND inactive = 0";

            $statement = $this->dbAdapter->query("
                SELECT * FROM proceeding_dec_counsel
                " . $where . "
            ");

            $results = $statement->execute();
            return $results->getResource()->fetchAll();
        }

        return false;
    }

    public function getProcSubType($id = null)
    {
        $where = "WHERE inactive = 0";

        if ($id != null) {
            $where .= " AND proc_type_id = " . $id;
        }

        $statement = $this->dbAdapter->query("
            SELECT proc_subtype_id,proc_type_id,proc_subtype_name
            FROM proceeding_subtype
            " . $where . "
            ORDER BY proc_subtype_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    
    public function getHearingsSubType($id = null)
    {
        $where = "";

        if ($id != null) {
            $where = " AND hearing_type_id = " . $id;
        }

        $statement = $this->dbAdapter->query("
            SELECT hearing_sub_id,hearing_type_id,hearing_sub_name
            FROM hearing_sub
            WHERE inactive = 0 " . $where . "
            ORDER BY hearing_sub_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getHearingsTypeList($id = null, $idSub = null)
    {
        $where = "";

        if ($id != null) {
            $where = "WHERE dec_hearing.hearing_type = $id AND dec_hearing.hearing_subtype = $idSub";
        }

        $statement = $this->dbAdapter->query("
            SELECT dec_hearing.dec_hearing_id as dec_hearing_id,
              hearing_type.hearing_name as hearing_name
            FROM dec_hearing
            LEFT JOIN hearing_sub h
              ON dec_hearing.Hearing_Type = h.hearing_type_id
            LEFT JOIN hearing_type
              ON dec_hearing.Hearing_Type =  hearing_type.hearing_type_id
            " . $where . "
            ORDER BY hearing_type.hearing_sub_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    
    public function getProceedParam($id = null)
    {
        if ($id != null) {
            $sql = "
                SELECT proc_st_para_id, proc_st_para
                FROM proceeding_subtype_para
                WHERE inactive = 0
            ";

            $statement = $this->dbAdapter->query($sql);
            $results = $statement->execute();
            return $results->getResource()->fetchAll();
        }

        return false;
    }

    
    public function getCJTypePresAuth($countryId = null)
    {
        $statement = $this->dbAdapter->query("
            SELECT cj_type_id,cj_type_name,country_id,is_pres
            FROM cj_type
            WHERE inactive = 0
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    
    public function getActionSub($actionTypeId)
    {

        $where = "";
        if (!empty($actionTypeId)) {
            $where = "WHERE action_id = '$actionTypeId'";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM sub_action $where ORDER BY sub_act_name");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    
    public function getActionSubPara($actionSubTypeId)
    {
        $where = "";
        if (!empty($actionTypeId)) {
            $where = "WHERE sub_act_id = '$actionSubTypeId'";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM sub_action_para $where ORDER BY sub_act_para");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    
    public function getCjNameHistoryList($CJTypeID)
    {
        $where = "WHERE cj_history.cj_type_id = '" . $CJTypeID . "' AND cj_history.inactive = 0";

        $statement = $this->dbAdapter->query("
            SELECT * FROM cj_history
            INNER JOIN cj_main
              ON cj_main.cj_id = cj_history.cj_id
            LEFT JOIN cj_type
              ON cj_history.cj_type_id = cj_type.cj_type_id
            " . $where . "
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function getDocumentFormat($post = null)
    {
        $where = "WHERE inactive = 0 AND  doc_format_name != NULL OR doc_format_name != ''";

        if ($post['action'] == 'show') {
            if ($post['status'] == "inactive") {
                $where = "WHERE inactive = 1 AND  doc_format_name != NULL OR doc_format_name != ''";
            } elseif ($post['status'] == "all") {
                $where = '';
            }
        }

        $query = "SELECT * FROM doc_format $where ORDER BY doc_format_name";
        return $this->getQueryResult($query, true);
    }

    public function updateDocumentFormat($post)
    {
        $id = $post['docFormatID'];
        $where = "WHERE doc_format_id = $id";
        if ($post['action'] == 'setinactivation') {

            $status = ($post['status'] == "active") ? 0 : 1;
            $query = "UPDATE doc_format SET inactive=$status
                      $where";

        } elseif ($post['action'] == 'update') {
            $name = $post['name'];
            $query = "UPDATE doc_format SET doc_format_name = '$name'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    public function addDocumentFormat($post)
    {
        $newDocName = $post['value'];
        $query = "INSERT INTO doc_format(doc_format_name) VALUES ('$newDocName') ";
        $results = $this->dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }

    //Didenko
    public function getDocumentSource($post = null)
    {
        $where = "WHERE inactive = 0";

        if ($post['action'] == 'show') {
            if ($post['status'] == "inactive") {
                $where = "WHERE inactive = 1";
            } elseif ($post['status'] == "all") {
                $where = '';
            }
        }

        $query = "SELECT * FROM doc_source $where";
        return $this->getQueryResult($query, true);
    }

    public function updateDocumentSource($post)
    {
        $id = $post['docSourceID'];
        $where = "WHERE source_id = $id";
        if ($post['action'] == 'setinactivation') {

            $status = ($post['status'] == "active") ? 0 : 1;
            $query = "UPDATE doc_source SET inactive=$status
                      $where";

        } elseif ($post['action'] == 'update') {
            $name = $post['name'];
            $query = "UPDATE doc_source SET source_id = '$name'
                      $where";
        }

        //        return $query;
        $this->getQueryResult($query);

    }

    public function UpdateRoomOffice($post)
    {
        $id = $post['roomId'];
        $where = "WHERE court_office_room_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $query = "UPDATE court_office_room SET court_office_room_name = '$name'
                      $where";
        }

        //        return $query;
        $this->getQueryResult($query);

    }

    public function UpdateOffice($post)
    {
        $id = $post['officeId'];
        $where = "WHERE court_office_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $query = "UPDATE court_office SET court_office_name = '$name'
                      $where";
        }

        //        return $query;
        $this->getQueryResult($query);

    }

    public function UpdateIndustry($post)
    {
        $id = $post['industryId'];
        $where = "WHERE industry_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $query = "UPDATE industry SET industry_name = '$name'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    public function addDocumentSource($post)
    {
        $newDocName = $post['value'];
        $query = "INSERT INTO doc_source( source_id) VALUES ('$newDocName') ";
        $results = $this->dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }

    
    private function getQueryResult($query, $returnArray = null)
    {
        if ($returnArray) {
            $statement = $this->dbAdapter->query($query);

            $results = $statement->execute();
            return $results->getResource()->fetchAll();
        } else {
            $results = $this->dbAdapter->query($query, Adapter::QUERY_MODE_EXECUTE);
            if ($results) {
                return "success";
            } else {
                return "fail";
            }
        }
    }

    
    public function addHearing($data)
    {

        parse_str($data['form'], $param);

        $hearingType = $param['hearing_type'];
        $hearingSubtype = $param['hearing_subtype'];
        $hearingSubtypeOther = $param['hearing_subtype_other'];
        $hearingsHasJury = $param ['Has_Jury'];


        $newData = array(
            'hearing_type' => $hearingType,
            'hearing_subtype' => $hearingSubtype,
            'hearing_subtype_ot' => $hearingSubtypeOther,
            'has_jury' => $hearingsHasJury
        );
        $sql = new Sql($this->dbAdapter);
        $insert = $sql->insert('dec_hearing');
        $insert->values($newData);
        $selectString = $sql->getSqlStringForSqlObject($insert);

        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {

            return "fail";
        }
    }


    public function getHearingsMainList()
    {

        $statement = $this->dbAdapter->query("
                                    SELECT * FROM dec_hearing
                    ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }


    public function updateHearingsList($post)
    {
        $id = $post['hearingsUpdateID'];
        $decHerID = null;
        $hearingType = $post['hearingType'];
        $hearingSubtype = $post['hearingSubtype'];
        $hearingSubtypeOther = $post['hearingSubtypeOther'];
        $hearingsHasJury = $post['hearingsHasJury'];


        $newData = array(

            'dec_id' => $decHerID,
            'hearing_type' => $hearingType,
            'hearing_subtype' => $hearingSubtype,
            'hearing_subtype_ot' => $hearingSubtypeOther,
            'has_jury' => $hearingsHasJury,

        );
        $sql = new Sql($this->dbAdapter);
        $update = $sql->update('dec_hearing');
        $update->where("dec_hearing_id=$id");
        $update->set($newData);
        $selectString = $sql->getSqlStringForSqlObject($update);
        
        $results = $this->dbAdapter->query($selectString, Adapter::QUERY_MODE_EXECUTE);
        if ($results) {
            return "success";
        } else {
            return "fail";
        }
    }

    public function fetchHearings($post)
    {
        $where = "WHERE dec_hearing_id = '$post[hearingsID]'";
        $statement = $this->dbAdapter->query("
											SELECT * FROM dec_hearing
											$where
										");

        $results = $statement->execute();
        return $results->getResource()->fetch();

    }

    public function getPdfSearch($post)
    {
        $search = $post['name'];
        $query = "SELECT dm.dec_id,dm.offline_document_name,dm.citation_no,dm.decname, pm.court_file_no FROM dec_main AS dm

            LEFT JOIN proc_in_dec AS pid ON dm.dec_id = pid.dec_id
            LEFT JOIN proceeding_main AS pm ON pm.proc_id = pid.proc_id
                    WHERE dm.offline_document_name LIKE '%$search%' OR dm.citation_no LIKE '%$search%' OR dm.decname LIKE '%$search%' OR pm.court_file_no LIKE '%$search%'
                    ";
        
        $statement = $this->dbAdapter->query($query);

        $results = $statement->execute();
        return $results->getResource()->fetchAll();;
    }

    public function showDecMain()
    {
        $statement = $this->dbAdapter->query("
									SELECT * FROM dec_main WHERE court_mun_ot != NULL OR court_mun_ot != ''

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    
    public function getCourtEvent()
    {
        $statement = $this->dbAdapter->query("
									SELECT  court_list_event_id, court_list_event_name,inactive
									 FROM court_list_event WHERE court_list_event_name != NULL OR court_list_event_name != ''

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getDicisionFormat()
    {
        $statement = $this->dbAdapter->query("
									SELECT doc_format_id,doc_format_name,inactive
									 FROM doc_format WHERE doc_format_name != NULL OR doc_format_name != '' ORDER BY doc_format_name

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getDicisionSource()
    {
        $statement = $this->dbAdapter->query("
									SELECT source_name, source_id,inactive
									 FROM doc_source WHERE source_name != NULL OR source_name != '' ORDER BY source_name

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getHearingDateToCatalog()
    {
        $statement = $this->dbAdapter->query("
									SELECT hearing_date_other_ot,hearing_date_id
									 FROM dec_hearing_date WHERE hearing_date_other_ot != NULL OR hearing_date_other_ot != ''

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getHearingPatyTypeToCatalog()
    {
        $statement = $this->dbAdapter->query("
									SELECT dec_hear_party_type_ot,dec_hear_party_type_id
									 FROM dec_hearing_party_type WHERE dec_hear_party_type_ot != NULL OR dec_hear_party_type_ot != ''

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getHearingPatyType()
    {
        $statement = $this->dbAdapter->query("
									SELECT hearing_party_type_id,hearing_party_type_name,hearing_party_type_level,inactive
									 FROM hearing_party_type WHERE hearing_party_type_name != NULL OR hearing_party_type_name != ''

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getHearingDate()
    {
        $statement = $this->dbAdapter->query("
									SELECT dec_hearing_date_other_id,dec_hearing_date_other_name,inactive
									 FROM dec_hearing_date_other WHERE dec_hearing_date_other_name != NULL OR dec_hearing_date_other_name != ''

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getCourtOffice()
    {
        $statement = $this->dbAdapter->query("
									SELECT court_office_id,court_office_name, court_mun_id
									 FROM court_office

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getCourtOfficeRoom()
    {
        $statement = $this->dbAdapter->query("
									SELECT court_office_room_id,court_office_room_name
									 FROM court_office_room

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    /* Prceeding : Practice Area, Proceeding Category, Related Proceeding, Proceeding Type,
       Proceeding SubType, Proceeding SubType Parameters;  */

    // Proceeding : Practice Area

    public function crudPracticeAreaMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'PracticeAreaSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readPracticeAreaSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = " AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = " AND inactive = 1";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT practice_area_id, practice_area_name, inactive
                                FROM practice_area WHERE practice_area_name != NULL OR practice_area_name != ''
                                {$where}
       ORDER BY practice_area_name ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function createPracticeAreaSlave($data)
    {
        $is_uniq = $this->isUniqPracticeArea($data['pa_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO practice_area SET practice_area_name = '{$data['pa_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }
    }

    public function updatePracticeAreaSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE practice_area SET practice_area_name = '{$data['pa_name']}' WHERE
									practice_area_id = '{$data['pa_id']}'
            ");
        $statement->execute();
    }

    public function isUniqPracticeArea($doc_name)
    {
        $where = "WHERE practice_area_name = '" . $doc_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT practice_area_name
									 FROM practice_area $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerPracticeAreaSlave($params)
    {
        $error = "System can not inactive Practice Area, used in the following tables : ";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT practice_area_id
									 FROM proceeding_type WHERE practice_area_id = '{$params['pa_id']}'
            ");
            $results = $statement->execute();
            $proc_type = $results->getResource()->fetch();


            $statement = $this->dbAdapter->query("
                                SELECT practice_area_id
                                 FROM dec_main WHERE practice_area_id = '{$params['pa_id']}'
            ");
            $results = $statement->execute();
            $dec_main = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT practice_area_id
                                 FROM customer_practice_area WHERE practice_area_id = '{$params['pa_id']}'
            ");
            $results = $statement->execute();
            $customer = $results->getResource()->fetch();

            if (empty($proc_type) && empty($dec_main) && empty($customer)) {

                $statement = $this->dbAdapter->query("
									UPDATE practice_area SET inactive = 1 WHERE
									practice_area_id = '{$params['pa_id']}'
                ");
                $statement->execute();

                return "success";
            } else {

                if (!empty($proc_type)) $error .= "proceeding_type ";
                if (!empty($dec_main)) $error .= "dec_main ";
                if (!empty($customer)) $error .= "customer_practice_area ";

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE practice_area SET inactive = 0 WHERE
									practice_area_id = '{$params['pa_id']}'
            ");
            $statement->execute();

            return "success";
        }
    }

    //Proceeding Category

    public function crudProceedingCategoryMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'ProceedingCategorySlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readProceedingCategorySlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = " AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = " AND inactive = 1";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT proc_cat_id, proc_cat_name, inactive
                                 FROM proceeding_category WHERE proc_cat_name != NULL OR proc_cat_name != ''
                                  $where
        ORDER BY proc_cat_name");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function createProceedingCategorySlave($data)
    {
        $is_uniq = $this->isUniqProceedingCategory($data['pc_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO proceeding_category SET proc_cat_name = '{$data['pc_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }
    }

    public function updateProceedingCategorySlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proceeding_category SET proc_cat_name = '{$data['pc_name']}' WHERE
									proc_cat_id = '{$data['pc_id']}'
            ");
        $statement->execute();
    }

    public function isUniqProceedingCategory($doc_name)
    {
        $where = "WHERE proc_cat_name = '" . $doc_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT proc_cat_name
									 FROM proceeding_category $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerProceedingCategorySlave($params)
    {
        $error = "System can not inactive Proceeding Category, used in the following tables : proceeding_main";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT proc_cat_id
									 FROM proceeding_main WHERE proc_cat_id = '{$params['pc_id']}'
            ");
            $results = $statement->execute();
            $proc_main = $results->getResource()->fetch();

            if (empty($proc_main)) {

                $statement = $this->dbAdapter->query("
									UPDATE proceeding_category SET inactive = 1 WHERE
									proc_cat_id = '{$params['pc_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE proceeding_category SET inactive = 0 WHERE
									proc_cat_id = '{$params['pc_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    // Related Proceeding

    public function crudRelatedProceedingMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'RelatedProceedingSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readRelatedProceedingSlave($data, $options = null)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            }
        }

        if ($options && isset($options['options']) && count($options['options'])) {
            $where .= ' AND rel_proceeding_id IN (' . implode(',', $options['options']) . ')';
        }

        $statement = $this->dbAdapter->query("
            SELECT rel_proceeding_id, rel_proceeding_name, inactive
            FROM related_proceedings
            WHERE rel_proceeding_name != NULL
              OR rel_proceeding_name != ''
              " . $where . "
            ORDER BY rel_proceeding_name
       ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function createRelatedProceedingSlave($data)
    {
        $is_uniq = $this->isUniqRelatedProceeding($data['rp_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO related_proceedings SET rel_proceeding_name = '{$data['rp_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }
    }

    public function updateRelatedProceedingSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE related_proceedings SET rel_proceeding_name = '{$data['rp_name']}' WHERE
									rel_proceeding_id = '{$data['rp_id']}'
            ");
        $statement->execute();
    }

    public function isUniqRelatedProceeding($doc_name)
    {
        $where = "WHERE rel_proceeding_name = '" . $doc_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT rel_proceeding_name
									 FROM related_proceedings $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerRelatedProceedingSlave($params)
    {
        $error = "System can not inactive Proceeding Category, used in the following tables : Related_Proceedings_to_Party_Type";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT rel_proceeding_id
									 FROM related_proceedings_to_party_type WHERE rel_proceeding_id = '{$params['rp_id']}'
            ");
            $results = $statement->execute();
            $rel_pt = $results->getResource()->fetch();

            if (empty($rel_pt)) {

                $statement = $this->dbAdapter->query("
									UPDATE related_proceedings SET inactive = 1 WHERE
									rel_proceeding_id = '{$params['rp_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE related_proceedings SET inactive = 0 WHERE
									rel_proceeding_id = '{$params['rp_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    // Proceeding SubType Parameters

    public function crudPstpMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'PstpSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readPstpSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT proc_st_para_id, proc_st_para, inactive
                                 FROM proceeding_subtype_para WHERE proc_st_para != NULL or proc_st_para != '' {$where}
       ORDER BY proc_st_para ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function otherPstpSlave()
    {
        $statement = $this->dbAdapter->query("SELECT proc_st_para_list_id, proc_st_para_ot FROM
                            proc_subtype_para_list WHERE proc_st_para_ot != NULL OR proc_st_para_ot != ''");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function createPstpSlave($data)
    {
        $is_uniq = $this->isUniqPstp($data['pstp_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO proceeding_subtype_para SET proc_st_para = '{$data['pstp_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            $last_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            if (!empty($data['pstpl_id'])) {
                $statement = $this->dbAdapter->query("
									UPDATE proc_subtype_para_list SET proc_st_para_id = '{$last_id}', proc_st_para_ot = ''
									WHERE proc_st_para_list_id = '{$data['pstpl_id']}'
                ");
                $statement->execute();
            }

            return $last_id;
        } else {
            return "Exist";
        }

    }

    public function replacePstpSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proc_subtype_para_list SET proc_st_para_id = '{$data['pstp_id']}', proc_st_para_ot = ''
									WHERE proc_st_para_list_id = '{$data['pstpl_id']}'
            ");
        $statement->execute();
    }

    public function updatePstpSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE proceeding_subtype_para SET proc_st_para = '{$data['pstp_name']}' WHERE
									proc_st_para_id = '{$data['pstp_id']}'
            ");
        $statement->execute();
    }

    public function isUniqPstp($pstp_name)
    {
        $where = "WHERE proc_st_para = '" . $pstp_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT proc_st_para
									 FROM proceeding_subtype_para $where
             ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerPstpSlave($params)
    {
        $error = "System can not inactive Document, used in the following tables : proc_subtype_para_list";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT proc_st_para_id
									 FROM proc_subtype_para_list WHERE proc_st_para_id = '{$params['pstp_id']}'
            ");
            $results = $statement->execute();
            $para = $results->getResource()->fetch();

            if (empty($para)) {

                $statement = $this->dbAdapter->query("
									UPDATE proceeding_subtype_para SET inactive = 1 WHERE
									proc_st_para_id = '{$params['pstp_id']}'
                ");
                $statement->execute();

                return "success";
            } else {

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE proceeding_subtype_para SET inactive = 0 WHERE
									proc_st_para_id = '{$params['pstp_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    // Reference Documents Section; 

    /*
     * CRUD Document Type
     */

    public function crudDocTypeMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'DocTypeSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readDocTypeSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = " AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = " AND inactive = 1";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT rule_doc_type_id, rule_doc_type_name, inactive
                                 FROM rule_doc_type WHERE rule_doc_type_name != NULL OR rule_doc_type_name != '' {$where}
         ORDER BY rule_doc_type_name");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function createDocTypeSlave($data)
    {
        $is_uniq = $this->isUniqDocTypeSlave($data['dt_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO rule_doc_type SET rule_doc_type_name = '{$data['dt_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }
    }

    public function updateDocTypeSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE rule_doc_type SET rule_doc_type_name = '{$data['dt_name']}' WHERE
									rule_doc_type_id = '{$data['dt_id']}'
            ");
        $statement->execute();
    }

    public function isUniqDocTypeSlave($dt_name)
    {
        $where = "WHERE rule_doc_type_name = '" . $dt_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT rule_doc_type_id, rule_doc_type_name
									 FROM rule_doc_type $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerDocTypeSlave($params)
    {
        $error = "System can not inactive Document, used in the following tables : rule_doc_master";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT rule_doc_type_id
									 FROM rule_doc_master WHERE rule_doc_type_id = '{$params['dt_id']}'
            ");
            $results = $statement->execute();
            $doc_master = $results->getResource()->fetch();


            if (empty($doc_master)) {

                $statement = $this->dbAdapter->query("
									UPDATE rule_doc_type SET inactive = 1 WHERE
									rule_doc_type_id = '{$params['dt_id']}'
            ");
                $statement->execute();

                return "success";
            } else {

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE rule_doc_type SET inactive = 0 WHERE
									rule_doc_type_id = '{$params['dt_id']}'
            ");
            $statement->execute();

            return "success";
        }
    }

    /*
     * CRUD Document Category
     */

    public function crudDocCatMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'DocCatSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readDocCatSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT rule_doc_cat_id, rule_doc_cat_name, inactive
                                 FROM rule_doc_cat WHERE rule_doc_cat_name != NULL OR rule_doc_cat_name != '' {$where}
                                 ORDER BY rule_doc_cat_name
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function createDocCatSlave($data)
    {
        $is_uniq = $this->isUniqDocCat($data['dc_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO rule_doc_cat SET rule_doc_cat_name = '{$data['dc_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }
    }

    public function updateDocCatSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE rule_doc_cat SET rule_doc_cat_name = '{$data['dc_name']}' WHERE
									rule_doc_cat_id = '{$data['dc_id']}'
            ");
        $statement->execute();
    }

    public function isUniqDocCat($dc_name)
    {
        $where = "WHERE rule_doc_cat_name = '" . $dc_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT rule_doc_cat_id, rule_doc_cat_name
									 FROM rule_doc_cat $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;

    }

    public function inactiveHandlerDocCatSlave($params)
    {
        $error = "System can not inactive Document, used in the following tables : rule_doc_master";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT rule_doc_cat
									 FROM rule_doc_master WHERE rule_doc_cat = '{$params['dc_id']}'
            ");
            $results = $statement->execute();
            $doc_master = $results->getResource()->fetch();


            if (empty($doc_master)) {

                $statement = $this->dbAdapter->query("
									UPDATE rule_doc_cat SET inactive = 1 WHERE
									rule_doc_cat_id = '{$params['dc_id']}'
            ");
                $statement->execute();

                return "success";
            } else {

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE rule_doc_cat SET inactive = 0 WHERE
									rule_doc_cat_id = '{$params['dc_id']}'
            ");
            $statement->execute();

            return "success";
        }
    }

    /*
     * CRUD Document name
     */

    public function crudDocNameMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'DocNameSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readDocNameSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = " AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = " AND inactive = 1";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT c.name, c.country_id, s.state_name, s.state_id
                                FROM country c LEFT JOIN state s ON c.country_id = s.country_id
                                WHERE (c.name !=NULL OR c.name != '')
                                ORDER BY name,state_name
                  ");

        $results = $statement->execute();
        $docs = $results->getResource()->fetchAll();
        $used_countries = array();

        $sorted_list = array();
        $cn = 0;

        for ($j = 0; $j < count($docs); $j++) {

            if (!in_array($docs[$j]['name'], $used_countries)) {

                $sorted_list[$cn] = ['Country' => $docs[$j]['name'],
                    'country_id' => $docs[$j]['country_id']];

                for ($i = 0; $i < count($docs); $i++) {

                    if ($sorted_list[$cn]['Country'] == $docs[$i]['name']) {

                        $sorted_list[$cn]['states'][] = ['state_name' => empty($docs[$i]['state_name']) ? '[no_state]' : $docs[$i]['state_name'],
                            'state_id' => $docs[$i]['state_id']];

                    }
                }
                $cn++;
            }

            $used_countries[] = $docs[$j]['name'];
        }

        $statement = $this->dbAdapter->query("
                                SELECT * FROM rule_doc_master WHERE rule_doc_name != NULL OR rule_doc_name != '' {$where}
                  ");

        $results = $statement->execute();
        $docs = $results->getResource()->fetchAll();

        for ($i = 0; $i < count($sorted_list); $i++) {

            for ($j = 0; $j < count($docs); $j++) {

                $temp = ['rule_doc_id' => $docs[$j]['rule_doc_id'],
                    'rule_doc_name' => $docs[$j]['rule_doc_name'],
                    'rule_doc_type_id' => empty($docs[$j]['rule_doc_type_id']) ? "0" : $docs[$j]['rule_doc_type_id'],
                    'rule_doc_cat' => empty($docs[$j]['rule_doc_cat']) ? "0" : $docs[$j]['rule_doc_cat'],
                    'practice_area' => empty($docs[$j]['practice_area']) ? "0" : $docs[$j]['practice_area'],
                    'inactive' => $docs[$j]['inactive']];

                if (empty($docs[$j]['state_id']) && $sorted_list[$i]['country_id'] == $docs[$j]['country_id']) {

                    $sorted_list[$i]['docs'][] = $temp;

                } else if (!empty($docs[$j]['state_id'])) {

                    for ($u = 0; $u < count($sorted_list[$i]['states']); $u++) {

                        if ($sorted_list[$i]['states'][$u]['state_id'] == $docs[$j]['state_id']) {
                            $sorted_list[$i]['states'][$u]['docs'][] = $temp;
                        }

                    }

                }
            }
        }

        return $sorted_list;
    }

    public function readOtherListDocNameSlave()
    {
        $statement = $this->dbAdapter->query("
                                SELECT dec_legislation_ref_id, rule_doc_ot FROM dec_legislation_ref
                                 WHERE rule_doc_ot != NULL OR rule_doc_ot != ''
                  ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function readVerListDocNameSlave()
    {
        $statement = $this->dbAdapter->query("
                                SELECT rule_doc_ver_id, rule_doc_ver_name, rule_doc_id, inactive FROM rule_doc_ver
                                WHERE (rule_doc_ver_name != NULL OR rule_doc_ver_name != '') AND inactive = 0
                  ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function selectsDocNameSlave()
    {
        $statement = $this->dbAdapter->query("
                                SELECT practice_area_id, practice_area_name
                                 FROM practice_area WHERE practice_area_name != NULL OR practice_area_name != ''
        ");

        $results = $statement->execute();
        $selectors['Practice_Area'] = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT rule_doc_type_id, rule_doc_type_name
                                 FROM rule_doc_type WHERE rule_doc_type_name != NULL OR rule_doc_type_name != ''
        ");

        $results = $statement->execute();
        $selectors['Document_Type'] = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT rule_doc_cat_id, rule_doc_cat_name
                                 FROM rule_doc_cat WHERE rule_doc_cat_name != NULL OR rule_doc_cat_name != ''
        ");

        $results = $statement->execute();
        $selectors['Document_Category'] = $results->getResource()->fetchAll();

        return $selectors;
    }

    public function createDocNameSlave($data)
    {
        $is_uniq = $this->isUniqDocName($data['dn_name']);

        if (!$is_uniq) {

            $vals = "";

            if ($data['dt_id'] != '0') $vals .= ", rule_doc_type_id = " . $data['dt_id'];
            if ($data['dc_id'] != '0') $vals .= ", rule_doc_cat = " . $data['dc_id'];
            if ($data['pa_id'] != '0') $vals .= ", practice_area = " . $data['pa_id'];
            if ($data['st_id'] != '0') $vals .= ", state_id = " . $data['st_id'];

            $statement = $this->dbAdapter->query("
									INSERT INTO rule_doc_master SET rule_doc_name = '{$data['dn_name']}',
									country_id = '{$data['cn_id']}', inactive = '{$data['inactive']}' {$vals}
            ");
            $statement->execute();

            $rd_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();
            $vr_id = NULL;

            if (!empty($data['vr_name'])) {

                $statement = $this->dbAdapter->query("
									INSERT INTO rule_doc_ver SET Rule_Doc_Ver_Name = '{$data['vr_name']}',
									rule_doc_id = '{$rd_id}', inactive = 0
                ");
                $statement->execute();

                $vr_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            }

            if (!empty($data['doc_ol_id'])) {

                if (empty($vr_id)) {
                    $set = ", Rule_Doc_Ver_ID = NULL";
                } else {
                    $set = ", Rule_Doc_Ver_ID = " . $vr_id;
                }

                $statement = $this->dbAdapter->query("
									UPDATE dec_legislation_ref SET rule_doc_id = '{$rd_id}',
									Rule_Doc_OT = NULL {$set} WHERE Dec_Legislation_Ref_ID = '{$data['doc_ol_id']}'
                ");
                $statement->execute();

            }

            return $rd_id;
        } else {
            return "Exist";
        }
    }

    public function updatedocDocNameSlave($data)
    {
        $vals = " SET ";

        if ($data['dt_id'] != '0') {
            $vals .= 'Rule_Doc_Type_ID = ' . $data['dt_id'] . ', ';
        } else {
            $vals .= 'Rule_Doc_Type_ID = NULL, ';
        }
        if ($data['dc_id'] != '0') {
            $vals .= 'Rule_Doc_Cat = ' . $data['dc_id'] . ', ';
        } else {
            $vals .= 'Rule_Doc_Cat = NULL, ';
        }
        if ($data['pa_id'] != '0') {
            $vals .= 'Practice_Area = ' . $data['pa_id'];
        } else {
            $vals .= 'Practice_Area = NULL';
        }

        $statement = $this->dbAdapter->query("
									UPDATE rule_doc_master {$vals} WHERE
									rule_doc_id = '{$data['dn_id']}'
            ");
        $statement->execute();
    }

    public function updateDocNameSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE rule_doc_master SET rule_doc_name = '{$data['dn_name']}' WHERE
									rule_doc_id = '{$data['dn_id']}'
            ");
        $statement->execute();
    }

    public function inactiveHandlerDocNameSlave($params)
    {
        $error = "System can not inactive Document, used in the following tables : ";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT rule_doc_id
									 FROM rule_ref WHERE rule_doc_id = '{$params['dn_id']}'
            ");
            $results = $statement->execute();
            $rule_ref = $results->getResource()->fetch();


            $statement = $this->dbAdapter->query("
                                SELECT rule_doc_id
                                 FROM dec_legislation_ref WHERE rule_doc_id = '{$params['dn_id']}'
            ");
            $results = $statement->execute();
            $dec_leg = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT rule_doc_id
                                 FROM rule_doc_ver WHERE rule_doc_id = '{$params['dn_id']}'
            ");
            $results = $statement->execute();
            $rule_ver = $results->getResource()->fetch();

            if (empty($rule_ref) && empty($dec_leg) && empty($rule_ver)) {

                $statement = $this->dbAdapter->query("
									UPDATE rule_doc_master SET inactive = 1 WHERE
									rule_doc_id = '{$params['dn_id']}'
                ");
                $statement->execute();

                return "success";
            } else {

                if (!empty($rule_ref)) $error .= "rule_ref ";
                if (!empty($dec_leg)) $error .= "dec_legislation_ref ";
                if (!empty($rule_ver)) $error .= "rule_doc_ver ";

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE rule_doc_master SET inactive = 0 WHERE
									rule_doc_id = '{$params['dn_id']}'
            ");
            $statement->execute();

            return "success";
        }
    }

    public function inactiveVerHandlerDocNameSLave($params)
    {
        $statement = $this->dbAdapter->query("
									SELECT rule_doc_ver_id
									 FROM dec_legislation_ref WHERE rule_doc_ver_id = '{$params['ver_id']}'
            ");
        $results = $statement->execute();
        $dec_ref = $results->getResource()->fetch();

        if (empty($dec_ref)) {

            $statement = $this->dbAdapter->query("
									UPDATE rule_doc_ver SET inactive = 1 WHERE
									rule_doc_ver_id = '{$params['ver_id']}'
            ");
            $statement->execute();

            return "success";
        } else {
            return "System can not inactive Document Version, used in the following tables : dec_legislation_ref";
        }
    }

    public function updateVersionDocNameSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE rule_doc_ver SET rule_doc_ver_name = '{$data['ver_name']}' WHERE
									rule_doc_ver_id = '{$data['ver_id']}'
            ");
        $statement->execute();
    }

    public function createVersionDocNameSlave($data)
    {
        $statement = $this->dbAdapter->query("
									INSERT INTO rule_doc_ver SET rule_doc_ver_name = '{$data['ver_name']}',
									rule_doc_id = '{$data['dn_id']}'
            ");
        $statement->execute();

        return $this->dbAdapter->getDriver()->getLastGeneratedValue();
    }

    public function moveDocNameSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE rule_doc_master SET state_id = '{$data['st_id']}' WHERE
									rule_doc_id = '{$data['dn_id']}'
            ");
        $statement->execute();
    }

    public function replaceDocNameSlave($data)
    {
        if (empty($data['ver_id'])) {
            $set = ", rule_doc_ver_id = NULL";
        } else {
            $set = ", rule_doc_ver_id = " . $data['ver_id'];
        }

        $statement = $this->dbAdapter->query("
									UPDATE dec_legislation_ref SET rule_doc_id = '{$data['dn_id']}',
									rule_doc_ot = NULL {$set} WHERE dec_legislation_ref_id = '{$data['ol_id']}'
                ");
        $statement->execute();
    }

    public function isUniqDocName($dn_name)
    {
        $where = "WHERE rule_doc_name = '" . $dn_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT rule_doc_name
									 FROM rule_doc_master $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;

    }

    /* Hearing section : Hearing Type, SubTypeCategory, SubType, PartyType, HDOO;  */

    // Hearing : Hearing Type

    public function crudHtTypeMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'HtTypeSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readHtTypeSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
            SELECT hearing_type_id, hearing_name, inactive
            FROM hearing_type
            WHERE (hearing_name != NULL OR hearing_name != '')
              " . $where . "
            ORDER BY hearing_name
        ");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function createHtTypeSlave($data)
    {
        $is_uniq = $this->isUniqHtType($data['ht_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO hearing_type SET hearing_name = '{$data['ht_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }

    }

    public function updateHtTypeSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE hearing_type SET hearing_name = '{$data['ht_name']}' WHERE
									hearing_type_id = '{$data['ht_id']}'
            ");
        $statement->execute();
    }

    public function isUniqHtType($ht_name)
    {
        $where = "WHERE hearing_name = '" . $ht_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT hearing_name
									 FROM hearing_type $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerHtTypeSlave($params)
    {
        $error = "System can not inactive Hearing Type, used in the following tables : hearing_type";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT hearing_type
									 FROM dec_hearing WHERE hearing_type = '{$params['ht_id']}'
            ");
            $results = $statement->execute();
            $dec_hear = $results->getResource()->fetch();

            if (empty($dec_hear)) {

                $statement = $this->dbAdapter->query("
									UPDATE hearing_type SET inactive = 1 WHERE
									hearing_type_id = '{$params['ht_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE hearing_type SET inactive = 0 WHERE
									hearing_type_id = '{$params['ht_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    // Hearing : Hearing SubType Category

    public function crudHstcMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'HstcSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readHstcSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT hearing_sub_cat_id, hearing_sub_cat_name, hearing_type_id, inactive
                                 FROM hearing_sub_cat_label WHERE hearing_sub_cat_name != NULL OR
                                 hearing_sub_cat_name != '' {$where}
         ORDER BY hearing_sub_cat_name");
        $results = $statement->execute();
        $hstc_list = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT hearing_type_id, hearing_name
                                 FROM hearing_type WHERE hearing_name != NULL OR hearing_name != ''
       ORDER BY hearing_name ");

        $results = $statement->execute();
        $ht_list = $results->getResource()->fetchAll();
        $count = ['hstc_list' => count($hstc_list), 'ht_list' => count($ht_list)];

        $sorted_list = array();

        for ($i = 0; $i < $count['ht_list']; $i++) {

            $sorted_list[$i]['hearing_type_name'] = $ht_list[$i]['hearing_name'];
            $sorted_list[$i]['hearing_type_id'] = $ht_list[$i]['hearing_type_id'];
            $temp_arr = array();

            for ($j = 0; $j < $count['hstc_list']; $j++) {

                if ($hstc_list[$j]['hearing_type_id'] == $ht_list[$i]['hearing_type_id']) {
                    $temp_arr[$j]['hearing_sub_cat_name'] = $hstc_list[$j]['hearing_sub_cat_name'];
                    $temp_arr[$j]['hearing_sub_cat_id'] = $hstc_list[$j]['hearing_sub_cat_id'];
                    $temp_arr[$j]['inactive'] = $hstc_list[$j]['inactive'];
                }
            }

            $sorted_list[$i]['Sub_Cat'] = $temp_arr;

        }

        return $sorted_list;
    }

    public function createHstcSlave($data)
    {
        $is_uniq = $this->isUniqHstc($data['hstc_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO hearing_sub_cat_label SET hearing_sub_cat_name = '{$data['hstc_name']}',
									hearing_type_id = '{$data['ht_id']}', inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }

    }

    public function updateHstcSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE hearing_sub_cat_label SET hearing_sub_cat_name = '{$data['hstc_name']}' WHERE
									hearing_sub_cat_id = '{$data['hstc_id']}'
            ");
        $statement->execute();
    }

    public function moveHstcSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE hearing_sub_cat_label SET hearing_type_id = '{$data['ht_id']}' WHERE
									hearing_sub_cat_id = '{$data['hstc_id']}'
            ");
        $statement->execute();
    }

    public function isUniqHstc($hstc_name)
    {
        $where = "WHERE hearing_sub_cat_name = '" . $hstc_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT hearing_sub_cat_name
									 FROM hearing_sub_cat_label $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerHstcSlave($params)
    {
        $error = "System can not inactive Hearing Type, used in the following tables : hearing_sub";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT hearing_sub_cat_id
									 FROM hearing_sub WHERE hearing_sub_cat_id = '{$params['hstc_id']}'
            ");
            $results = $statement->execute();
            $hear_sub = $results->getResource()->fetch();

            if (empty($hear_sub)) {

                $statement = $this->dbAdapter->query("
									UPDATE hearing_sub_cat_label SET inactive = 1 WHERE
									hearing_sub_cat_id = '{$params['hstc_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE hearing_sub_cat_label SET inactive = 0 WHERE
									hearing_sub_cat_id = '{$params['hstc_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    // CRUD Hearing SubType

    public function crudHstMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'HstSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readHstSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT hearing_sub_cat_id, hearing_sub_cat_name, hearing_type_id, inactive
                                 FROM hearing_sub_cat_label WHERE hearing_sub_cat_name != NULL OR hearing_sub_cat_name != ''
        ORDER BY hearing_sub_cat_name");
        $results = $statement->execute();
        $hstc_list = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT hearing_type_id, hearing_name
                                 FROM hearing_type WHERE hearing_name != NULL OR hearing_name != ''
      ORDER BY hearing_name  ");

        $results = $statement->execute();
        $ht_list = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT hearing_sub_id, hearing_sub_name, hearing_sub_cat_id, hearing_type_id, inactive
                                 FROM hearing_sub WHERE hearing_sub_name != NULL OR hearing_sub_name != '' {$where}
        ORDER BY hearing_sub_name");

        $results = $statement->execute();
        $hst_list = $results->getResource()->fetchAll();

        $count = ['hstc_list' => count($hstc_list), 'ht_list' => count($ht_list), 'hst_list' => count($hst_list)];

        $sorted_list = array();

        for ($i = 0; $i < $count['ht_list']; $i++) {

            $sorted_list[$i]['hearing_type_name'] = $ht_list[$i]['hearing_name'];
            $sorted_list[$i]['hearing_type_id'] = $ht_list[$i]['hearing_type_id'];
            $temp_arr = array();

            for ($j = 0; $j < $count['hstc_list']; $j++) {

                if ($hstc_list[$j]['hearing_type_id'] == $ht_list[$i]['hearing_type_id']) {
                    $temp_arr[$j]['hearing_sub_cat_name'] = $hstc_list[$j]['hearing_sub_cat_name'];
                    $temp_arr[$j]['hearing_sub_cat_id'] = $hstc_list[$j]['hearing_sub_cat_id'];
                }

                $temp_arr_s = array();

                for ($u = 0; $u < $count['hst_list']; $u++) {

                    if ($hst_list[$u]['hearing_sub_cat_id'] == $hstc_list[$j]['hearing_sub_cat_id']) {
                        $temp_arr_s[$u]['hearing_sub_cat_id'] = $hst_list[$u]['hearing_sub_cat_id'];
                        $temp_arr_s[$u]['hearing_sub_name'] = $hst_list[$u]['hearing_sub_name'];
                        $temp_arr_s[$u]['hearing_sub_id'] = $hst_list[$u]['hearing_sub_id'];
                        $temp_arr_s[$u]['hearing_type_id'] = $hst_list[$u]['hearing_type_id'];
                        $temp_arr_s[$u]['inactive'] = $hst_list[$u]['inactive'];
                    }

                    if ($hst_list[$u]['hearing_sub_cat_id'] == NULL && $hst_list[$u]['hearing_type_id'] == $ht_list[$i]['hearing_type_id']) {
                        $sorted_list[$i]['Sub_No_Cat'][$u]['hearing_sub_name'] = $hst_list[$u]['hearing_sub_name'];
                        $sorted_list[$i]['Sub_No_Cat'][$u]['hearing_sub_id'] = $hst_list[$u]['hearing_sub_id'];
                        $sorted_list[$i]['Sub_No_Cat'][$u]['hearing_type_id'] = $hst_list[$u]['hearing_type_id'];
                        $sorted_list[$i]['Sub_No_Cat'][$u]['inactive'] = $hst_list[$u]['inactive'];
                    }

                    if (!empty($temp_arr_s)) {
                        $temp_arr[$j]['Sub'] = $temp_arr_s;
                    }
                }
            }

            $sorted_list[$i]['Sub_Cat'] = $temp_arr;
        }
        return $sorted_list;
    }

    public function catalogHstSlave()
    {
        $statement = $this->dbAdapter->query("
                                SELECT dec_hearing_id, hearing_subtype_ot
                                 FROM dec_hearing WHERE hearing_subtype_ot != '' OR hearing_subtype_ot != NULL
        ");
        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function catalogRuleRefHstSlave()
    {
        $statement = $this->dbAdapter->query("
                                SELECT dec_hearing_id, hearing_subtype, hearing_st_rule_ot
                                 FROM dec_hearing WHERE hearing_st_rule_ot != '' OR hearing_st_rule_ot != NULL
        ");
        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function ruleRefListHstSlave($hst_id)
    {
        $statement = $this->dbAdapter->query("
                                SELECT rr.rule_ref_name, rr.rule_doc_id, rr.inactive
                                 FROM hearing_sub_rule_ref hsrr LEFT JOIN
                                 rule_ref rr ON hsrr.rule_ref_id = rr.rule_ref_id WHERE hsrr.hearing_sub_id = '{$hst_id}'
        ");
        $results = $statement->execute();
        $rule_ref = $results->getResource()->fetchAll();
        $sorted_list = array();
        $docs = array();

        if (!empty($rule_ref)) {

            for ($i = 0; $i < count($rule_ref); $i++) {

                if (!empty($rule_ref[$i]['Rule_Ref_Name'])) {

                    $statement = $this->dbAdapter->query("
                                SELECT rdm.rule_doc_id, rdm.rule_doc_name, c.Name, s.state_name
                                 FROM rule_doc_master rdm LEFT JOIN country c ON rdm.country_id = c.country_id LEFT JOIN
                                 state s ON s.state_id = rdm.state_id
                                 WHERE rdm.rule_doc_id = '{$rule_ref[$i]['rule_doc_id']}'
                  ");

                    $results = $statement->execute();
                    $doc = $results->getResource()->fetchAll();

                    $docs[$i]['Rule_Doc_Name'] = $doc[0]['rule_doc_name'];
                    $docs[$i]['Rule_Ref_Name'] = $rule_ref[$i]['rule_ref_name'];
                    $docs[$i]['inactive'] = $rule_ref[$i]['inactive'];
                    $docs[$i]['Country'] = $doc[0]['name'];
                    $docs[$i]['State'] = empty($doc[0]['state_name']) ? "[no_state]" : $doc[0]['state_name'];
                }
            }

            $used_countries = array();

            for ($j = 0; $j < count($docs); $j++) {

                if (!in_array($docs[$j]['Country'], $used_countries)) {
                   
                    $sorted_list[$j]['Country'] = $docs[$j]['Country'];

                    for ($u = 0; $u < count($docs); $u++) {

                        if ($docs[$j]['Country'] == $docs[$u]['Country']) {

                            $sorted_list[$j]['states'][$u] =
                                ['State' => $docs[$u]['State']];

                            $sorted_list[$j]['states'][$u]['docs'][$u] =
                                ['rule_doc_name' => $docs[$u]['rule_doc_name'],
                                    'rule_ref_name' => $docs[$u]['rule_ref_name'],
                                    'inactive' => $docs[$u]['inactive']];
                        }
                    }
                }

                $used_countries[] = $docs[$j]['Country'];
            }

            return $sorted_list;

        } else {
            return false;
        }
    }

    public function ruleRefListWithParamsHstSlave($param)
    {
        $statement = $this->dbAdapter->query("
                                SELECT rr.rule_ref_name, rr.rule_doc_id, rr.inactive
                                 FROM hearing_sub_rule_ref hsrr LEFT JOIN
                                 rule_ref rr ON hsrr.rule_ref_id = rr.rule_ref_id WHERE hsrr.hearing_sub_id = '{$param['hst_id']}'
        ");
        $results = $statement->execute();
        $rule_ref = $results->getResource()->fetchAll();

        if ($param['doc_name_id'] != 0) {

            foreach ($rule_ref as $key => $item):
                if ($item['Rule_Doc_ID'] != $param['doc_name_id']) unset($rule_ref[$key]);
            endforeach;

            $k = 0;

            $new_rule_ref = array();

            foreach ($rule_ref as $key => $item):
                $new_rule_ref[$k]['rule_doc_id'] = $item['rule_doc_id'];
                $new_rule_ref[$k]['rule_ref_name'] = $item['rule_ref_name'];
                $new_rule_ref[$k]['inactive'] = $item['inactive'];
                $k++;
            endforeach;
        } else {
            $new_rule_ref = $rule_ref;
        }


        $sorted_list = array();
        $docs = array();

        if (!empty($new_rule_ref)) {

            $doc_save = 0;

            for ($i = 0; $i < count($new_rule_ref); $i++) {

                if (!empty($new_rule_ref[$i]['Rule_Ref_Name'])) {

                    $statement = $this->dbAdapter->query("
                                SELECT rdm.rule_doc_id, rdm.rule_doc_name, rdm.rule_doc_type_id, c.Name, s.state_name, c.country_id, s.state_id
                                 FROM rule_doc_master rdm LEFT JOIN country c ON rdm.country_id = c.country_id LEFT JOIN
                                 state s ON s.state_id = rdm.state_id
                                 WHERE rdm.rule_doc_id = '{$new_rule_ref[$i]['rule_doc_id']}'
                  ");

                    $results = $statement->execute();
                    $doc = $results->getResource()->fetchAll();

                    $docs[$doc_save]['Rule_Doc_Name'] = $doc[0]['rule_doc_name'];
                    $docs[$doc_save]['Rule_Doc_Type_ID'] = $doc[0]['rule_doc_type_id'];
                    $docs[$doc_save]['Rule_Ref_Name'] = $new_rule_ref[$i]['rule_ref_name'];
                    $docs[$doc_save]['inactive'] = $new_rule_ref[$i]['inactive'];
                    $docs[$doc_save]['Country'] = $doc[0]['name'];
                    $docs[$doc_save]['State'] = empty($doc[0]['state_name']) ? "[no_state]" : $doc[0]['state_name'];
                    $docs[$doc_save]['State_ID'] = empty($doc[0]['state_name']) ? "[no_state]" : $doc[0]['state_name'];

                    $unset = false;

                    if ($param['DOC_TYPE_ID'] != 0 && $param['DOC_TYPE_ID'] != $doc[0]['rule_doc_type_id']) $unset = true;
                    if ($param['state_id'] != 0 && $param['state_id'] != $doc[0]['state_id']) $unset = true;
                    if ($param['country_id'] != 0 && $param['country_id'] != $doc[0]['country_id']) $unset = true;

                    if ($unset) {
                        unset($docs[$doc_save]);
                    } else {
                        $doc_save++;
                    }
                }
            }

            $used_countries = array();

            for ($j = 0; $j < count($docs); $j++) {

                if (!in_array($docs[$j]['Country'], $used_countries)) {

                    $sorted_list[$j]['Country'] = $docs[$j]['Country'];

                    for ($u = 0; $u < count($docs); $u++) {

                        if ($docs[$j]['Country'] == $docs[$u]['Country']) {

                            $sorted_list[$j]['states'][$u] =
                                ['State' => $docs[$u]['State']];

                            $sorted_list[$j]['states'][$u]['docs'][$u] =
                                ['rule_doc_name' => $docs[$u]['rule_doc_name'],
                                    'rule_ref_name' => $docs[$u]['rule_ref_name'],
                                    'inactive' => $docs[$u]['inactive']];
                        }
                    }
                }

                $used_countries[] = $docs[$j]['Country'];
            }

            return $sorted_list;

        } else {
            return false;
        }
    }

    public function ruleListHstSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "WHERE inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "WHERE inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                    SELECT rdm.rule_doc_id, rdm.rule_doc_name, c.name, s.state_name
                     FROM rule_doc_master rdm LEFT JOIN country c ON rdm.country_id = c.country_id LEFT JOIN
                     state s ON s.state_id = rdm.state_id
        ");

        $results = $statement->execute();
        $docs = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                    SELECT rule_ref_name, rule_doc_id, rule_ref_id, inactive
                     FROM rule_ref {$where}
        ");

        $results = $statement->execute();
        $rr = $results->getResource()->fetchAll();

        $used_countries = array();
        $sorted_list = array();

        for ($j = 0; $j < count($docs); $j++) {

            if (!in_array($docs[$j]['name'], $used_countries)) {

                $sorted_list[$j]['Country'] = $docs[$j]['name'];

                for ($u = 0; $u < count($docs); $u++) {

                    if ($docs[$j]['name'] == $docs[$u]['name']) {

                        $sorted_list[$j]['states'][$u] =
                            ['State' => empty($docs[$u]['state_name']) ? '[no_state]' : $docs[$u]['state_name']];

                        $sorted_list[$j]['states'][$u]['docs'][$u] =
                            [
                                'rule_doc_name' => $docs[$u]['rule_doc_name'],
                                'rule_doc_id' => $docs[$u]['rule_doc_id']
                            ];

                        $t = 0;
                        $temp = array();

                        for ($r = 0; $r < count($rr); $r++) {

                            if ($rr[$r]['rule_doc_id'] == $docs[$u]['rule_doc_id']) {
                                $temp[$t]['rule_ref_id'] = $rr[$r]['rule_ref_id'];
                                $temp[$t]['rule_ref_name'] = $rr[$r]['rule_ref_name'];
                                $temp[$t]['inactive'] = $rr[$r]['inactive'];
                                $t++;
                            }
                        }

                        if (!empty($temp)) {
                            $sorted_list[$j]['states'][$u]['docs'][$u]['rules'] = $temp;
                            $temp = array();
                        }

                    }
                }
            }

            $used_countries[] = $docs[$j]['name'];
        }


        return $sorted_list;
    }

    public function docRuleRefListWithParamsHstSlave($param)
    {
        $statement = $this->dbAdapter->query("
                    SELECT rdm.rule_doc_id, rdm.rule_doc_name, rdm.rule_doc_type_id, c.Name, s.state_name, c.country_id, s.state_id
                     FROM rule_doc_master rdm LEFT JOIN country c ON rdm.country_id = c.country_id LEFT JOIN
                     state s ON s.state_id = rdm.state_id
        ");

        $results = $statement->execute();
        $docs_all = $results->getResource()->fetchAll();

        $docs = array();
        $doc_saved = 0;

        foreach ($docs_all as $item):

            $docs[$doc_saved]['rule_doc_id'] = $item['rule_doc_id'];
            $docs[$doc_saved]['rule_doc_name'] = $item['rule_doc_name'];
            $docs[$doc_saved]['rule_doc_type_id'] = $item['rule_doc_type_id'];
            $docs[$doc_saved]['name'] = $item['name'];
            $docs[$doc_saved]['state_name'] = $item['state_name'];

            $unset = false;

            if ($param['doc_name_id'] != 0 && $param['doc_name_id'] != $item['rule_doc_id']) $unset = true;
            if ($param['doc_type_id'] != 0 && $param['doc_type_id'] != $item['rule_doc_type_id']) $unset = true;
            if ($param['state_id'] != 0 && $param['state_id'] != $item['state_id']) $unset = true;
            if ($param['country_id'] != 0 && $param['country_id'] != $item['country_id']) $unset = true;

            if ($unset) {
                unset($docs[$doc_saved]);
            } else {
                $doc_saved++;
            }

        endforeach;

        $where = "";

        if (isset($param['inactive'])) {
            if ($param['inactive'] == 0) {
                $where = "WHERE inactive = 0";
            } else if ($param['inactive'] == 1) {
                $where = "WHERE inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                    SELECT rule_ref_name, rule_doc_id, rule_ref_id, inactive
                     FROM rule_ref $where
        ");

        $results = $statement->execute();
        $rr = $results->getResource()->fetchAll();

        $used_countries = array();
        $sorted_list = array();

        for ($j = 0; $j < count($docs); $j++) {

            if (!in_array($docs[$j]['name'], $used_countries)) {

                $sorted_list[$j]['Country'] = $docs[$j]['name'];

                for ($u = 0; $u < count($docs); $u++) {

                    if ($docs[$j]['name'] == $docs[$u]['name']) {

                        $sorted_list[$j]['states'][$u] =
                            ['State' => empty($docs[$u]['state_name']) ? '[no_state]' : $docs[$u]['state_name']];

                        $sorted_list[$j]['states'][$u]['docs'][$u] =
                            [
                                'rule_doc_name' => $docs[$u]['rule_doc_name'],
                                'rule_doc_id' => $docs[$u]['rule_doc_id']
                            ];

                        $t = 0;
                        $temp = array();

                        for ($r = 0; $r < count($rr); $r++) {

                            if ($rr[$r]['rule_doc_id'] == $docs[$u]['rule_doc_id']) {
                                $temp[$t]['rule_ref_id'] = $rr[$r]['rule_ref_id'];
                                $temp[$t]['rule_ref_name'] = $rr[$r]['rule_ref_name'];
                                $temp[$t]['inactive'] = $rr[$r]['inactive'];
                                $t++;
                            }
                        }

                        if (!empty($temp)) {
                            $sorted_list[$j]['states'][$u]['docs'][$u]['rules'] = $temp;
                            $temp = array();
                        }

                    }
                }
            }

            $used_countries[] = $docs[$j]['name'];
        }
        return $sorted_list;
    }

    public function selectorsHstSlave()
    {
        $statement = $this->dbAdapter->query("
                                SELECT country_id, name
                                 FROM country
        ");

        $results = $statement->execute();
        $selectors['Country'] = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT state_id, state_name
                                 FROM state
        ");

        $results = $statement->execute();
        $selectors['State'] = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT rule_doc_type_id, rule_doc_type_name
                                 FROM rule_doc_type
        ");

        $results = $statement->execute();
        $selectors['Document_Type'] = $results->getResource()->fetchAll();


        $statement = $this->dbAdapter->query("
                                SELECT rule_doc_id, rule_doc_name
                                 FROM rule_doc_master
        ");

        $results = $statement->execute();
        $selectors['Document_Name'] = $results->getResource()->fetchAll();

        return $selectors;
    }

    public function createHstSlave($data)
    {
        $is_uniq = $this->isUniqHst($data['hst_name']);
        $addit_field = "";

        if ($data['hstc_id'] == 0) {
            $addit_field = " , hearing_sub_cat_id = NULL";
        } else {
            $addit_field = " , hearing_sub_cat_id = " . $data['hstc_id'];
        }

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO hearing_sub SET hearing_sub_name = '{$data['hst_name']}',
									hearing_type_id = '{$data['ht_id']}',
									inactive = '{$data['inactive']}' $addit_field
            ");
            $statement->execute();

            $last_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            if (!empty($data['hst_cat_id'])) {
                $statement = $this->dbAdapter->query("
									UPDATE dec_hearing SET Hearing_Subtype = '{$last_id}', Hearing_Subtype_OT = ''
									WHERE Dec_Hearing_ID = '{$data['hst_cat_id']}'
                ");
                $statement->execute();
            }

            return $last_id;

        } else {
            return "Exist";
        }
    }

    public function createRuleRefHstSlave($data)
    {
        $is_uniq = $this->isUniqHstRuleRef($data['rr_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO rule_ref SET Rule_Ref_Name = '{$data['rr_name']}',
									rule_doc_id = '{$data['rd_id']}', inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            $last_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            if (!empty($data['hst_cat_id'])) {

                $statement = $this->dbAdapter->query("
									INSERT INTO hearing_sub_rule_ref SET hearing_sub_id = '{$data['hst_id']}',
									rule_ref_id = '{$last_id}'
                ");
                $statement->execute();

                $hsrr_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

                $statement = $this->dbAdapter->query("
									UPDATE dec_hearing SET hearingsub_in_ruleref_id = '{$hsrr_id}', Hearing_ST_Rule_OT = ''
									WHERE Dec_Hearing_ID = '{$data['hst_cat_id']}'
                ");
                $statement->execute();
            }

            return $last_id;

        } else {
            return "Exist";
        }
    }

    public function createHearingRuleRefHstSlave($data)
    {
        $statement = $this->dbAdapter->query("
                                INSERT INTO hearing_sub_rule_ref SET hearing_sub_id = '{$data['hst_id']}',
                                rule_ref_id = '{$data['rr_id']}'
        ");
        $statement->execute();
    }

    public function updateHstSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE hearing_sub SET hearing_sub_name = '{$data['hst_name']}' WHERE
									hearing_sub_id = '{$data['hst_id']}'
            ");
        $statement->execute();
    }

    public function updateRuleRefHstSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE rule_ref SET rule_ref_name = '{$data['rr_name']}' WHERE
									rule_ref_id = '{$data['rr_id']}'
            ");
        $statement->execute();
    }

    public function moveHstSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE hearing_sub SET hearing_sub_cat_id = '{$data['hstc_id']}' WHERE
									hearing_sub_id = '{$data['hst_id']}'
            ");
        $statement->execute();
    }

    public function replaceHstSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE dec_hearing SET hearing_subtype = '{$data['hst_id']}', hearing_subtype_ot = ''
									WHERE dec_hearing_id = '{$data['hst_cat_id']}'
            ");
        $statement->execute();
    }

    public function replaceRuleRefHstSlave($data)
    {
        $statement = $this->dbAdapter->query("
									SELECT hearingsub_in_ruleref_id
									 FROM hearing_sub_rule_ref WHERE rule_ref_id = '{$data['rr_id']}'
            ");


        $results = $statement->execute();
        $row = $results->getResource()->fetch();

        $statement = $this->dbAdapter->query("
									UPDATE dec_hearing SET hearingsub_in_ruleref_id = '{$row['HearingSub_in_RuleRef_ID']}', hearing_st_rule_ot = ''
									WHERE dec_hearing_id = '{$data['hst_cat_id']}'
            ");
        $statement->execute();
    }

    public function isUniqHst($hst_name)
    {
        $where = "WHERE hearing_sub_name = '" . $hst_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT hearing_sub_name
									 FROM hearing_sub $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function isUniqHstRuleRef($rr_name)
    {
        $where = "WHERE rule_ref_name = '" . $rr_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT rule_ref_name
									 FROM rule_ref $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerHstSlave($params)
    {
        $error = "System can not inactive Hearing Type, used in the following tables : dec_hearing";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT hearing_subtype
									 FROM dec_hearing WHERE hearing_subtype = '{$params['hst_id']}'
            ");
            $results = $statement->execute();
            $dec = $results->getResource()->fetch();

            if (empty($dec)) {

                $statement = $this->dbAdapter->query("
									UPDATE hearing_sub SET inactive = 1 WHERE
									hearing_sub_id = '{$params['hst_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE hearing_sub SET inactive = 0 WHERE
									hearing_sub_id = '{$params['hst_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    public function inactiveHandlerRuleRefHstSlave($params)
    {
        $error = "System can not inactive Hearing Type, used in the following tables : hearing_sub_rule_ref";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT rule_ref_id
									 FROM hearing_sub_rule_ref WHERE rule_ref_id = '{$params['rr_id']}'
            ");
            $results = $statement->execute();
            $hear_sub = $results->getResource()->fetch();

            if (empty($hear_sub)) {

                $statement = $this->dbAdapter->query("
									UPDATE rule_ref SET inactive = 1 WHERE
									rule_ref_id = '{$params['rr_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE rule_ref SET inactive = 0 WHERE
									rule_ref_id = '{$params['rr_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    // Hearing Party Type

    public function crudHearingPartyTypeMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'HearingPartyTypeSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readHearingPartyTypeSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT hearing_party_type_id, hearing_party_type_name, inactive, hearing_party_type_level
                                 FROM hearing_party_type WHERE (hearing_party_type_name != NULL OR hearing_party_type_name != '') {$where}
        ORDER BY hearing_party_type_name");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function readOtherListHearingPartyTypeSlave($data)
    {
        $statement = $this->dbAdapter->query("
                                SELECT dec_hear_party_type_id, dec_hear_party_type_ot
                                 FROM dec_hearing_party_type WHERE dec_hear_party_type_ot != NULL OR dec_hear_party_type_ot != ''
        ");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function createHearingPartyTypeSlave($data)
    {
        $is_uniq = $this->isUniqHearingPartyType($data['hpt_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO hearing_party_type SET hearing_party_type_name = '{$data['hpt_name']}',
									inactive = '{$data['inactive']}', hearing_party_type_level = '{$data['level']}'
            ");
            $statement->execute();

            $hpt_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            if (!empty($data['dec_hpt_id'])) {

                $statement = $this->dbAdapter->query("
									UPDATE dec_hearing_party_type SET hearing_party_type_id = '{$hpt_id}',
									dec_hear_party_type_ot = '' WHERE dec_hear_party_type_id = '{$data['dec_hpt_id']}'
                ");
                $statement->execute();
            }

            return $hpt_id;
        } else {
            return "Exist";
        }

    }

    public function updateHearingPartyTypeSlave($data)
    {

        $statement = $this->dbAdapter->query("
									UPDATE hearing_party_type SET hearing_party_type_name = '{$data['hpt_name']}' WHERE
									hearing_party_type_id = '{$data['hpt_id']}'
            ");
        $statement->execute();
    }

    public function updateLevelHearingPartyTypeSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE hearing_party_type SET hearing_party_type_level = '{$data['level']}' WHERE
									hearing_party_type_id = '{$data['hpt_id']}'
            ");
        $statement->execute();
    }

    public function isUniqHearingPartyType($hpt_name)
    {
        $where = "WHERE hearing_party_type_name = '" . $hpt_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT hearing_party_type_name
									 FROM hearing_party_type $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerHearingPartyTypeSlave($params)
    {
        $error = "System can not inactive Hearing Type, used in the following tables : dec_hearing_party_type";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT hearing_party_type_id
									 FROM dec_hearing_party_type WHERE hearing_party_type_id = '{$params['hpt_id']}'
            ");
            $results = $statement->execute();
            $dec_hear = $results->getResource()->fetch();

            if (empty($dec_hear)) {

                $statement = $this->dbAdapter->query("
									UPDATE hearing_party_type SET inactive = 1 WHERE
									hearing_party_type_id = '{$params['hpt_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE hearing_party_type SET inactive = 0 WHERE
									hearing_party_type_id = '{$params['hpt_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    public function replaceHearingPartyTypeSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE dec_hearing_party_type SET hearing_party_type_id = '{$data['hpt_id']}', dec_hear_party_type_ot = ''
									WHERE dec_hear_party_type_id = '{$data['dec_hpt_id']}'
                ");
        $statement->execute();
    }

    // CRUD Hearing Date Other Options

    public function crudHearingDateMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'HearingDateSlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readHearingDateSlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT * FROM dec_hearing_date_other WHERE (dec_hearing_date_other_name != NULL OR dec_hearing_date_other_name != '') {$where}
         ORDER BY dec_hearing_date_other_name");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function readOtherListHearingDateSlave()
    {
        $statement = $this->dbAdapter->query("
                                SELECT hearing_date_id, hearing_date_other_ot
                                 FROM dec_hearing_date WHERE hearing_date_other_ot != NULL OR hearing_date_other_ot != ''
        ");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function createHearingDateSlave($data)
    {
        $is_uniq = $this->isUniqHearingDate($data['hdoo_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO dec_hearing_date_other SET dec_hearing_date_other_name = '{$data['hdoo_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            $hdoo_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            if (!empty($data['hdoo_o_id'])) {

                $statement = $this->dbAdapter->query("
									UPDATE dec_hearing_date SET hearing_date_other_id = '{$hdoo_id}',
									hearing_date_other_ot = '' WHERE hearing_date_id = '{$data['hdoo_o_id']}'
                ");
                $statement->execute();
            }

            return $hdoo_id;
        } else {
            return "Exist";
        }

    }

    public function updateHearingDateSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE dec_hearing_date_other SET dec_hearing_date_other_name = '{$data['hdoo_name']}' WHERE
									dec_hearing_date_other_id = '{$data['hdoo_id']}'
            ");
        $statement->execute();
    }

    public function isUniqHearingDate($hdoo_name)
    {
        $where = "WHERE dec_hearing_date_other_name = '" . $hdoo_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT dec_hearing_date_other_name
									 FROM dec_hearing_date_other $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerHearingDateSlave($params)
    {
        $error = "System can not inactive Hearing Type, used in the following tables : dec_hearing_date";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT hearing_date_other_id
									 FROM dec_hearing_date WHERE hearing_date_other_id = '{$params['hdoo_id']}'
            ");
            $results = $statement->execute();
            $hear_date = $results->getResource()->fetch();

            if (empty($hear_date)) {

                $statement = $this->dbAdapter->query("
									UPDATE dec_hearing_date_other SET inactive = 1 WHERE
									dec_hearing_date_other_id = '{$params['hdoo_id']}'
                ");
                $statement->execute();

                return "success";
            } else {
                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE dec_hearing_date_other SET inactive = 0 WHERE
									dec_hearing_date_other_id = '{$params['hdoo_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    public function replaceHearingDateSlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE dec_hearing_date SET hearing_date_other_id = '{$data['hdoo_id']}', hearing_date_other_ot = ''
									WHERE dec_hear_party_type_id = '{$data['dec_hpt_id']}'
                ");
        $statement->execute();
    }

    // End hearing section

    // Ruling section :  Damage Heads, Scale of Costs; 

    public function getDamageHeads()
    {
        $statement = $this->dbAdapter->query("
									SELECT dmg_head_id,dmg_head_name,inactive
									 FROM damage_heads WHERE  dmg_head_name != NULL OR dmg_head_name != ''
									 ORDER BY dmg_head_name

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getScaleOfCosts()
    {
        $statement = $this->dbAdapter->query("
									SELECT cac_id,cac_name,inactive
									 FROM cost_award_coverage WHERE  cac_name != NULL OR cac_name != ''
                                                                             ORDER BY cac_name
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    //User Config 
    public function getCustomerRole()
    {
        $statement = $this->dbAdapter->query("
									SELECT cust_role_id, cust_role_name, inactive
									 FROM customer_role WHERE cust_role_name != NULL OR cust_role_name != ''
                                                             ORDER BY cust_role_name
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    //Employee
    public function getEmployeeRole()
    {
        $statement = $this->dbAdapter->query("
									SELECT role_id,role_name,inactive
									 FROM role ORDER BY role_name

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getIndustryCatalogue()
    {
        $statement = $this->dbAdapter->query("
									SELECT *
									 FROM customers WHERE  industry_ot != NULL OR industry_ot != ''
                                                                       ORDER BY industry_ot
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getIndustry()
    {
        $statement = $this->dbAdapter->query("
									SELECT industry_id,industry_name,inactive
									 FROM industry WHERE  industry_name != NULL OR industry_name != ''
                                    ORDER BY industry_name
				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getGender()
    {
        $statement = $this->dbAdapter->query("
									SELECT gender_id,gender_name,inactive
									 FROM gender WHERE  gender_name != NULL OR gender_name != ''
									  ORDER BY gender_name

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getReferralSource()
    {
        $statement = $this->dbAdapter->query("
									SELECT *
									 FROM referral_source WHERE referral_source_name != NULL OR referral_source_name != ''
									 ORDER BY referral_source_name

				");

        $results = $statement->execute();

        return $results->getResource()->fetchAll();
    }

    public function getRelatedProc()
    {
        return $this->dbAdapter->query("
SELECT rel_proceeding_name, rel_proceeding_id FROM related_proceedings
WHERE rel_proceeding_name != NULL OR rel_proceeding_name != ''
    ")->execute()->getResource()->fetchAll();
    }

    public function getRelatedProceedings($results)
    {
        foreach ($results as $key => $value) {

            $rel = $this->dbAdapter->query("
    SELECT related_proceedings.rel_proceeding_id, rel_proceeding_name
    FROM related_proceedings
    JOIN related_proceedings_to_party_type
                    ON related_proceedings_to_party_type.rel_proceeding_id=related_proceedings.rel_proceeding_id
                    WHERE related_proceedings_to_party_type.party_type_id={$value["party_type_id"]}")->execute()->getResource()->fetchAll();
            if (!empty($rel)) {
                $results[$key]['relpreecid'] = $rel[0];
            } else {
                $results[$key]['relpreecid'] = false;
            }
        }
        return $results;
    }

    public function judgeThroughPracticeArea($id)
    {
        $q = "
            SELECT court_type_id
            FROM dec_main
            WHERE practice_area_id = $id
        ";

        $courtTypeIdArr = $this->getQueryResult($q, true);

        if (empty($courtTypeIdArr)) {
            return array(
                array('cj_id' => 1)
            );
        }

        $CTstr = implode(',', $courtTypeIdArr);
        $query = "
            SELECT * FROM cj_main AS cjm
            LEFT JOIN cj_history AS cjh
              ON cjh.cj_id = cjm.cj_id
            WHERE cjh.cj_type_id = (
              SELECT cj_type_id
              FROM cj_type
              WHERE cj_type_name = 'Judge'
            ) AND cjh.court_id IN (" . $CTstr . ")
              AND cjm.inactive = 0
            ORDER BY cjm.cj_ln
        ";

        print_r($query);

        return $this->getQueryResult($query, true);
    }

    public function updatePartySuffix($post)
    {
        $id = $post['PartySId'];
        $where = "WHERE party_suffix_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $query = "UPDATE party_suffix SET party_suffix_name = '$name'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    public function updateHearingsDateOption($post)
    {
        $id = $post['HearingOptionIdId'];
        $where = "WHERE dec_hearing_date_other_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $query = "UPDATE dec_hearing_date_other SET dec_hearing_date_other_name = '$name'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    public function updateHearingPatyType($post)
    {
        $id = $post['HearingPatyId'];
        $where = "WHERE hearing_party_type_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $Lvlv = $post['Lvlv'];
            $query = "UPDATE hearing_party_type SET hearing_party_type_name = '$name',hearing_party_type_level = '$Lvlv'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    public function updatePartyType($post)
    {
        $id = $post['partyTypeId'];
        $where = "WHERE party_type_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $PartyLvl = $post['PartyLvl'];
            $query = "UPDATE party_type SET party_type_name = '$name',party_type_level = '$PartyLvl'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    public function getCourtTypesLP($region = false, $mun = false, $offices = false, $rooms = false)
    {
        $courtTypes = $this->dbAdapter->query("SELECT court_id, court_name FROM court_type JOIN country ON court_type.country_id=country.country_id")
            ->execute()->getResource()->fetchAll();
        if ($region && !empty($courtTypes)) {
            foreach ($courtTypes as $court_key => $court_value) {
                $courtTypes[$court_key]["regions"] = $this->dbAdapter->query("SELECT court_reg_id, reg_name,inactive FROM court_region WHERE court_id={$court_value['court_id']}")
                    ->execute()->getResource()->fetchAll();


                if ($mun && !empty($courtTypes[$court_key]["regions"])) {
                    foreach ($courtTypes[$court_key]['regions'] as $region_key => $region_value) {
                        $courtTypes[$court_key]['regions'][$region_key]['muns'] = $this->dbAdapter->query("SELECT court_mun_id, mun_name,inactive FROM court_mun WHERE court_reg_id={$region_value['court_reg_id']}")
                            ->execute()->getResource()->fetchAll();


                        if ($offices && !empty($courtTypes[$court_key]['regions'][$region_key])) {
                            foreach ($courtTypes[$court_key]['regions'][$region_key]['muns'] as $mun_key => $mun_value) {
                                $courtTypes[$court_key]['regions'][$region_key]['muns'][$mun_key]["offices"] = $this->dbAdapter->query("SELECT court_office_id, court_office_name, inactive FROM court_office WHERE court_mun_id={$mun_value['court_mun_id']} AND inactive = 0")
                                    ->execute()->getResource()->fetchAll();

                                if (!empty($courtTypes[$court_key]['regions'][$region_key]['muns'][$mun_key]["offices"]) && $rooms) {
                                    foreach ($courtTypes[$court_key]['regions'][$region_key]['muns'][$mun_key]["offices"] as $office_key => $office_value) {
                                        $courtTypes[$court_key]['regions'][$region_key]['muns'][$mun_key]["offices"][$office_key]["rooms"] = $this->dbAdapter->query("SELECT court_office_room_id, court_office_room_name,inactive FROM court_office_room WHERE court_office_id={$office_value['court_office_id']}")
                                            ->execute()->getResource()->fetchAll();
                                    }
                                }
                            }
                        }
                    }
                }
            }

        }

        return $courtTypes;

    }

    public function getCourtOfficeOT()
    {
        return $this->dbAdapter->query("SELECT court_office_ot,Dec_ID FROM dec_main WHERE  court_office_ot != NULL OR court_office_ot != ''")->execute()->getResource()->fetchAll();
    }

    public function getPartyPositionOT()
    {
        return $this->dbAdapter->query("
SELECT party_position_ot FROM proceeding_party_position WHERE
party_position_ot != NULL OR party_position_ot != ''
ORDER BY party_position_ot
")->execute()->getResource()->fetchAll();
    }

    public function getPartySizeList()
    {
        return $this->dbAdapter->query("
         SELECT party_size_id,party_size_name,inactive
         FROM party_size
         WHERE  party_size_name != NULL OR party_size_name != ''
         ORDER BY party_size_name
         ")->execute()->getResource()->fetchAll();
    }


    public function updatePartySize($post)
    {
        $id = $post['PartySizeId'];
        $where = "WHERE party_size_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $query = "UPDATE party_size SET party_size_name = '$name'
                      $where";
        }

        
        $this->getQueryResult($query);

    }


    /**
     * @param $data
     * @return mixed
     */
    public function getNestedDropdown($data)
    {
        $string = "
         SELECT * FROM proceeding_type AS pt
         LEFT JOIN proceeding_subtype AS ps
         ON pt.proc_type_id = ps.proc_type_id
         WHERE pt.inactive = 0 AND pt.practice_area_id = '{$data->paId}' ORDER BY pt.proc_type_name
         ";
        
        $proc_type = $this->dbAdapter->query($string)->execute()->getResource()->fetchAll();
        return $proc_type;
    }

    public function getCaseCategory($prArea = null, $options = null)
    {
        if ($options && isset($options['options']) && count($options['options'])) {
            $where = 'AND proc_subtype_id IN (' . implode(',', $options['options']) . ')';
        } else {
            $where = '';
        }

        if ($prArea) {
            $sql = "
                SELECT * FROM proceeding_type AS pt
                LEFT JOIN proceeding_subtype AS ps
                  ON pt.proc_type_id = ps.proc_type_id
                  AND ps.inactive = 0
                WHERE pt.inactive = 0
                    AND pt.practice_area_id = " . (int)$prArea . "
                " . $where . "
                ORDER BY pt.proc_type_name, ps.proc_subtype_name
            ";
        } else {
            $sql = "
                SELECT pt.proc_type_id, pt.proc_type_name, pt.practice_area_id,
                  ps.proc_subtype_id, ps.proc_subtype_name, pa.practice_area_name
                FROM proceeding_type AS pt
                LEFT JOIN proceeding_subtype AS ps
                  ON pt.proc_type_id = ps.proc_type_id
                  AND ps.inactive = 0
                LEFT JOIN practice_area AS pa
                  ON pa.practice_area_id = pt.practice_area_id
                  AND pa.inactive = 0
                WHERE pt.inactive = 0
                " . $where . "
                ORDER BY pa.practice_area_id, pt.proc_type_name, ps.proc_subtype_name
            ";
        }

        return $this->dbAdapter->query($sql)->execute()->getResource()->fetchAll();
    }

    public function getNestedHearDropdown($courtId = null, $options = null)
    {

        if ($options && isset($options['options']) && count($options['options'])) {
            $where = 'AND hearing_sub_id IN (' . implode(',', $options['options']) . ')';
        } else {
            $where = '';
        }

        if ($courtId) {
            $string = "
                SELECT ht.*, ct.court_name,
                  hs.hearing_sub_name, hs.hearing_sub_id, 
                  hl.hearing_sub_cat_id, hl.hearing_sub_cat_name
                FROM hearing_type AS ht
                LEFT JOIN hearing_sub AS hs
                  ON hs.hearing_type_id = ht.hearing_type_id
                  AND hs.hearing_sub_id != 1 
                  AND hs.inactive = 0 
                LEFT JOIN hearing_sub_cat_label AS hl
                  ON hs.hearing_sub_cat_id = hl.hearing_sub_cat_id
                  AND hl.hearing_sub_cat_id != 1
                  AND hl.inactive = 0  
                LEFT JOIN court_type ct
                  ON ct.court_id = ht.court_id
                  AND ct.inactive = 0 
                WHERE ht.court_id = " . (int)$courtId . "
                    AND ht.hearing_type_id != 1 
                    AND ht.inactive = 0
                " . $where . "
                ORDER BY ht.hearing_name, hl.hearing_sub_cat_name, hs.hearing_sub_name
            ";
        } else {
            $string = "
                SELECT ht.*, ht.court_id AS courtId, ct.court_name,
                  hs.hearing_sub_name, hs.hearing_sub_id, 
                  hl.hearing_sub_cat_id, hl.hearing_sub_cat_name
                FROM hearing_type AS ht
                LEFT JOIN hearing_sub AS hs
                  ON hs.hearing_type_id = ht.hearing_type_id
                  AND hs.hearing_sub_id != 1 
                  AND hs.inactive = 0 
                LEFT JOIN hearing_sub_cat_label AS hl
                  ON hs.hearing_sub_cat_id = hl.hearing_sub_cat_id
                  AND hl.hearing_sub_cat_id != 1
                  AND hl.inactive = 0  
                LEFT JOIN court_type ct
                  ON ct.court_id = ht.court_id
                  AND ct.inactive = 0 
                WHERE ht.hearing_type_id != 1 
                  AND ht.inactive = 0 
                " . $where . "
                ORDER BY ct.court_name, ht.hearing_name, 
                  hl.hearing_sub_cat_name, hs.hearing_sub_name
            ";
        }

        return $this->dbAdapter->query($string)->execute()->getResource()->fetchAll();
    }

    public function getMultiDropdown($post, $options = null)
    {
        $type = $post['type'];
        $select = '*';
        $table = '';
        $where = '';
        $order = '';
        $join = '';

        if ($type == 'proc_subtype') {
            $dataPA = $post['dataPa'];
            $data = $post['id'];

            $where = "
                WHERE proceeding_subtype.inactive = 0
                AND  proceeding_subtype.proc_subtype_name IS NOT NULL 
            ";

            $table = 'proceeding_subtype';

            if ($data == '0' && $dataPA != 0) {
                $join = "LEFT JOIN proceeding_type ON proceeding_type.proc_type_id = proceeding_subtype.proc_type_id";
                $where .= " AND proceeding_type.practice_area_id IN ($dataPA) AND proceeding_type.inactive = 0 ";
            } elseif ($data != '0') {
                $where .= " AND proc_type_id IN ($data) AND proceeding_type.inactive = 0 ";
            }

            $order = " ORDER BY proc_subtype_name";

        } elseif ($type == 'court-name') {
            $countryId = $post['country'];
            $table = 'court_type';
            $state = $post['state'];

            $where = "
                WHERE country_id = " . (int)$countryId . "
                AND court_name IS NOT NULL AND inactive = 0
            ";

            $order = ' ORDER BY court_name';

            if ($state != '0') {
                $where .= " AND state_id IN($state)";
            }

        } elseif ($type == 'proceedingTypeByPractArea') {
            $table = 'proceeding_type';
            $data = $post['paId'];

            $where = "
                WHERE inactive = 0
                    AND proc_type_name IS NOT NULL
            ";

            if ($data != '0') {
                $where .= " AND practice_area_id IN($data)";
            }

            $order = 'ORDER BY proc_type_name';

        } elseif ($type == 'hearings_subtype') {
            $table = 'hearing_sub';
            $data = $post['hearingsTypeId'];

            $where = "
                WHERE inactive = 0
                    AND hearing_sub_name IS NOT NULL 
            ";

            if ($data != '0') {
                $where .= " AND hearing_type_id IN($data)";
            }

            $order = ' ORDER BY hearing_sub_name';

        } elseif ($type == 'cj-name') {
            if ($options && isset($options['options']) && count($options['options'])) {
                $judges = ' AND cj_main.cj_id IN (' . implode(',', $options['options']) . ')';
            } else {
                $judges = '';
            }

            if (empty($post['idCourt'])) {
                $data = isset($post['data']) ? $post['data'] : 0;

                $table = 'cj_main';

                $select = "
                    DISTINCT cj_main.cj_id, 
                    CONCAT (
                        COALESCE(cj_main.cj_ln, ''), ', ', 
                        COALESCE(cj_main.cj_fn, ''), ' ', 
                        COALESCE(cj_main.cj_mn, '')
                    ) AS judge
                ";

                $where = "
                    WHERE cj_main.inactive = 0 
                    AND cj_history.cj_type_id IN (
                        SELECT cj_type_id FROM cj_type 
                        WHERE cj_type_id IN (3,4,7)
                    ) 
                ";

                $join = "LEFT JOIN cj_history ON cj_history.cj_id = cj_main.cj_id";

                $order = "
                    GROUP BY cj_main.cj_id
                    ORDER BY cj_main.cj_ln
                ";

                if ($data != 0) {
                    $where .= " AND cj_history.court_id IN ($data)";
                }

                $where .= $judges;

            } else {
                if ($options && isset($options['options']) && count($options['options'])) {
                    $judges = ' AND cjm.cj_id IN (' . implode(',', $options['options']) . ')';
                } else {
                    $judges = '';
                }

                $idCourt = $post['idCourt'];
                $query = "
                    SELECT DISTINCT cjm.cj_id, 
                      CONCAT (
                        COALESCE(cjm.cj_ln, ''), ', ', 
                        COALESCE(cjm.cj_fn, ''), ' ', 
                        COALESCE(cjm.cj_mn, '')
                      ) AS judge
                    FROM dec_pres_auth dpa
                    INNER JOIN dec_main dm
                      ON dm.dec_id = dpa.dec_id
                      AND dm.inactive = 0
                    INNER JOIN cj_history cjh
                      ON cjh.cj_history_id = dpa.cj_history_id
                      AND cjh.inactive = 0
                    INNER JOIN cj_main cjm
                      ON cjm.cj_id = cjh.cj_id
                      AND cjm.inactive = 0
                    WHERE dm.court_type_id = " . (int)$idCourt . "
                    " . $judges . "
                    ORDER BY cjm.cj_ln ASC;
                ";

                
                return $this->getQueryResult($query, true);
            }

        } elseif ($type == 'outcome') {
            $table = "outcome";
            $where = "WHERE inactive = 0 AND outcome_name IS NOT NULL";

            if ($options && isset($options['options']) && count($options['options'])) {
                $where .= ' AND outcome_id IN (' . implode(',', $options['options']) . ')';
            }

        } else {
            die('Not defined');
        };

        $query = "
            SELECT " . $select . " 
            FROM " . $table . "
            " . $join . " 
            " . $where . " 
            " . $order . ";
        ";
        
        return $this->getQueryResult($query, true);
    }

    public function getAllProcStParaAll($options = null)
    {
        if ($options && isset($options['options']) && count($options['options'])) {
            $where = 'AND proc_st_para_id IN (' . implode(',', $options['options']) . ')';
        } else {
            $where = '';
        }

        $query = "
            SELECT * FROM proceeding_subtype_para
            WHERE proc_st_para IS NOT NULL
              AND proc_st_para != ''
              AND inactive = 0
            " . $where . "
            ORDER BY proc_st_para
        ";

        return $this->getQueryResult($query , true);
    }

    public function showinactivePartySuffix($status)
    {

        if ($status == 2) {

            $where = "";
        } else {

            $where = "WHERE party_suffix_name != NULL OR party_suffix_name != '' AND inactive = '$status' ";
        }

        $statement = $this->dbAdapter->query("SELECT * FROM party_suffix
                                              " . $where . "
                                              ORDER BY party_suffix_name");
        $results = $statement->execute();
        return $results->getResource()->fetchAll();

    }

    public function updateCjType($post)
    {
        $id = $post['cjTypeId'];
        $where = "WHERE cj_type_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $presidingPositionId = $post['presidingPositionId'];
            $query = "UPDATE cj_type SET cj_type_name = '$name',is_pres = '$presidingPositionId'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    public function updateCjTittle($post)
    {
        $id = $post['cjTitleId'];
        $where = "WHERE cj_title_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];

            $query = "UPDATE cj_title SET cj_title_name = '$name'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    //Geography section

    public function countryList($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT *  FROM country WHERE name != NULL OR name != ''  {$where}
        ");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function addCountry($data)
    {
        $is_uniq = $this->isUniqCountry($data['country_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO country SET name = '{$data['country_name']}',
									inactive = '{$data['inactive']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }
    }

    public function addState($data)
    {
        $is_uniq = $this->isUniqState($data['state_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO state SET state_name = '{$data['state_name']}',
									inactive = '{$data['inactive']}', country_id = '{$data['country_id']}'

            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }
    }

    public function addCity($data)
    {
        $is_uniq = $this->isUniqCity($data['city_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO city SET Ctate_name = '{$data['city_name']}',
									inactive = '{$data['inactive']}', state_id = '{$data['state_id']}'
            ");
            $statement->execute();

            return $this->dbAdapter->getDriver()->getLastGeneratedValue();
        } else {
            return "Exist";
        }
    }

    public function isUniqCountry($cn_name)
    {
        $where = "WHERE name = '" . $cn_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT name
									 FROM country $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function isUniqState($cn_name)
    {
        $where = "WHERE state_name = '" . $cn_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT state_name
									 FROM state $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function updateCountry($params)
    {
        $statement = $this->dbAdapter->query("
									UPDATE country SET name = '{$params['cn_name']}' WHERE country_id = '{$params['cn_id']}'
            ");
        $statement->execute();
    }

    public function updateState($params)
    {
        $statement = $this->dbAdapter->query("
									UPDATE state SET state_name = '{$params['st_name']}' WHERE state_id = '{$params['st_id']}'
            ");
        $statement->execute();
    }

    public function statelist($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0 ";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1 ";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("SELECT country_id, name FROM country ORDER BY name");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("SELECT state_id, state_name, country_id, inactive FROM state
                                              WHERE (state_name != NULL OR state_name != '') {$where} ORDER BY state_name");
        $results = $statement->execute();
        $states = $results->getResource()->fetchAll();

        $sorted_list = array();

        // Country -> State
        foreach ($countries as $country) {
            if (!empty($country['name'])) {
                $sorted_list[$country['country_id']] = array(
                    'country_id' => $country['country_id'],
                    'Country_Name' => $country['name'],
                    'states' => array()
                );

                $countryStates = array();
                foreach ($states as $state) {
                    if ($country['country_id'] == $state['country_id']) {
                        $countryStates[] = array(
                            'state_id' => $state['state_id'],
                            'state_name' => $state['state_name'],
                            'inactive' => $state['inactive']
                        );
                    }
                }

                $sorted_list[$country['country_id']]['states'] = $countryStates;
            }
        }

        return $sorted_list;
    }

    public function countryinactiveHandler($params)
    {
        $error = "System can not inactive Country, used in the following tables : ";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT country_id
									 FROM state WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $state = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM city WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $city = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM users WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $users = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM court_type WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $court_type = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM law_university WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $law_university = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM law_firm WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $law_firm = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM cj_type WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $cj_type = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM cj_title WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $cj_title = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM proceeding_main WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $proc = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT country_id
                                 FROM dec_main WHERE country_id = '{$params['cn_id']}'
            ");
            $results = $statement->execute();
            $dec_main = $results->getResource()->fetch();

            if (empty($state)
                && empty($city)
                && empty($users)
                && empty($court_type)
                && empty($law_university)
                && empty($law_firm)
                && empty($cj_type)
                && empty($cj_title)
                && empty($proc)
                && empty($dec_main)
            ) {

                $statement = $this->dbAdapter->query("
									UPDATE country SET inactive = 1 WHERE
									country_id = '{$params['cn_id']}'
                ");
                $statement->execute();

                return "success";
            } else {

                if (!empty($state)) $error .= "state ";
                if (!empty($city)) $error .= "city ";
                if (!empty($users)) $error .= "users ";
                if (!empty($court_type)) $error .= "court_type ";
                if (!empty($law_university)) $error .= "law_uneversity ";
                if (!empty($law_firm)) $error .= "law_firm ";
                if (!empty($cj_title)) $error .= "cj_title ";
                if (!empty($cj_type)) $error .= "cj_type ";
                if (!empty($proc)) $error .= "proceeding_main ";
                if (!empty($dec_main)) $error .= "dec_main ";

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE country SET inactive = 0 WHERE
									country_id = '{$params['cn_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    public function stateinactiveHandler($params)
    {
        $error = "System can not inactive State, used in the following tables : ";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT state_id
									 FROM city WHERE state_id = '{$params['st_id']}'
            ");
            $results = $statement->execute();
            $city = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT state_id
                                 FROM cj_history WHERE state_id = '{$params['st_id']}'
            ");
            $results = $statement->execute();
            $cj_history = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT state_id
                                 FROM users WHERE state_id = '{$params['st_id']}'
            ");
            $results = $statement->execute();
            $users = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT state_id
                                 FROM court_type WHERE state_id = '{$params['st_id']}'
            ");
            $results = $statement->execute();
            $court_type = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT state_id
                                 FROM law_university WHERE state_id = '{$params['st_id']}'
            ");
            $results = $statement->execute();
            $law_university = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT state_id
                                 FROM law_firm WHERE state_id = '{$params['st_id']}'
            ");
            $results = $statement->execute();
            $law_firm = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT state_id
                                 FROM proceeding_main WHERE state_id = '{$params['st_id']}'
            ");
            $results = $statement->execute();
            $proc = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
                                SELECT state_id
                                 FROM dec_main WHERE state_id = '{$params['st_id']}'
            ");
            $results = $statement->execute();
            $dec_main = $results->getResource()->fetch();

            if (empty($cj_history)
                && empty($city)
                && empty($users)
                && empty($court_type)
                && empty($law_university)
                && empty($law_firm)
                && empty($proc)
                && empty($dec_main)
            ) {

                $statement = $this->dbAdapter->query("
									UPDATE state SET inactive = 1 WHERE
									state_id = '{$params['st_id']}'
                ");
                $statement->execute();

                return "success";
            } else {

                if (!empty($cj_history)) $error .= "cj_history ";
                if (!empty($city)) $error .= "city ";
                if (!empty($users)) $error .= "users ";
                if (!empty($court_type)) $error .= "court_type ";
                if (!empty($law_university)) $error .= "law_uneversity ";
                if (!empty($law_firm)) $error .= "law_firm ";
                if (!empty($proc)) $error .= "proceeding_main ";
                if (!empty($dec_main)) $error .= "dec_main ";

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE state SET inactive = 0 WHERE
									state_id = '{$params['st_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    //CRUD City

    public function crudCityMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'CitySlave';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readCitySlave($data)
    {
        $where = "";

        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("SELECT country_id, name FROM country ORDER BY name");
        $results = $statement->execute();
        $countries = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("SELECT state_id, state_name, country_id, inactive FROM state
                                              WHERE (state_name != NULL OR state_name != '') ORDER BY state_name");
        $results = $statement->execute();
        $states = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("SELECT city_id, city_name, state_id, country_id, inactive FROM city
                                              WHERE (city_name != NULL OR city_name != '') {$where} ORDER BY city_name");
        $results = $statement->execute();

        $cities = $results->getResource()->fetchAll();
        $sorted_list = array();



        foreach ($countries as $country) {
            if (!empty($country['name'])) {
                $sorted_list[$country['country_id']] = array(
                    'country_id' => $country['country_id'],
                    'country_name' => $country['name'],
                    'states' => array()
                );

                $countryStates = array();
                foreach ($states as $state) {
                    if ($country['country_id'] == $state['country_id']) {
                        $countryStates[] = array(
                            'state_id' => $state['state_id'],
                            'state_name' => $state['state_name'],
                            'inactive' => $state['inactive'],
                            'cities' => array()
                        );

                        $stateCities = array();
                        foreach ($cities as $city) {
                            if ($state['state_id'] == $city['state_id']) {
                                $stateCities[] = array(
                                    'city_id' => $city['city_id'],
                                    'city_name' => $city['city_name'],
                                    'inactive' => $city['inactive']
                                 );
                            }
                        }

                        $countryStates[$state['state_id']]['cities'] = $stateCities;
                    }
                }

                $sorted_list[$country['country_id']]['states'] = $countryStates;
            }
        }


        return $sorted_list;
    }

    public function readOtherListCitySlave($data)
    {
        $statement = $this->dbAdapter->query("
                                SELECT city_name_ot, user_id
                                 FROM users WHERE city_name_ot != NULL OR city_name_ot != ''
        ");
        $results = $statement->execute();

        $users = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT  customer_id
                                 FROM customers
        ");
        $results = $statement->execute();

        $customers = $results->getResource()->fetchAll();

        return array('users' => $users, 'customers' => $customers);
    }

    public function createCitySlave($data)
    {
        $is_uniq = $this->isUniqCity($data['city_name']);

        if (!$is_uniq) {
            $statement = $this->dbAdapter->query("
									INSERT INTO city SET city_name = '{$data['city_name']}',
									inactive = '{$data['inactive']}', state_id = '{$data['state_id']}'
            ");
            $statement->execute();

            $city_id = $this->dbAdapter->getDriver()->getLastGeneratedValue();

            if (!empty($data['user_id'])) {

                $statement = $this->dbAdapter->query("
									UPDATE users SET city_id = '{$city_id}',
									city_name_ot = '' WHERE user_id = '{$data['user_id']}'
                ");
                $statement->execute();
            }

            if (!empty($data['customer_id'])) {

                $statement = $this->dbAdapter->query("
									UPDATE customers SET city_id = '{$city_id}'
                ");
                $statement->execute();
            }

            return $city_id;
        } else {
            return "Exist";
        }

    }

    public function updateCitySlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE city SET city_name = '{$data['city_name']}' WHERE
									city_id = '{$data['city_id']}'
            ");
        $statement->execute();
    }

    public function moveCitySlave($data)
    {
        $statement = $this->dbAdapter->query("
									UPDATE city SET state_id = '{$data['state_id']}' WHERE
									city_id = '{$data['city_id']}'
            ");
        $statement->execute();
    }

    public function isUniqCity($city_name)
    {
        $where = "WHERE city_name = '" . $city_name . "' ";

        $statement = $this->dbAdapter->query("
									SELECT city_name
									 FROM city $where
            ");


        $results = $statement->execute();
        $doc = $results->getResource()->fetch();
        return empty($doc) ? false : true;
    }

    public function inactiveHandlerCitySlave($params)
    {
        $error = "System can not inactive Hearing Type, used in the following tables : ";

        if ($params['status'] == 1) {

            $statement = $this->dbAdapter->query("
									SELECT city_id
									 FROM users WHERE city_id = '{$params['city_id']}'
            ");
            $results = $statement->execute();
            $users = $results->getResource()->fetch();

            $statement = $this->dbAdapter->query("
									SELECT city_id
									 FROM customers WHERE city_id = '{$params['city_id']}'
            ");
            $results = $statement->execute();
            $customers = $results->getResource()->fetch();

            if (empty($users) && empty($customers)) {

                $statement = $this->dbAdapter->query("
									UPDATE city SET inactive = 1 WHERE
									city_id = '{$params['city_id']}'
                ");
                $statement->execute();

                return "success";
            } else {

                if (empty($users)) $error .= " users";
                if (empty($customers)) $error .= " customers";

                return $error;
            }
        } else {

            $statement = $this->dbAdapter->query("
									UPDATE city SET inactive = 0 WHERE
									city_id = '{$params['city_id']}'
                ");
            $statement->execute();

            return "success";
        }
    }

    public function replaceCitySlave($data)
    {
        if (!empty($data['user_id'])) {

            $statement = $this->dbAdapter->query("
									UPDATE users SET city_id = '{$data['city_id']}',
									city_name_ot = '' WHERE user_id = '{$data['user_id']}'
                ");
            $statement->execute();
        }

        if (!empty($data['customer_id'])) {

            $statement = $this->dbAdapter->query("
									UPDATE customers SET city_id = '{$data['city_id']}'
                ");
            $statement->execute();
        }
    }

    public function moveState($data)
    {
        $statement = $this->dbAdapter->query("
                                UPDATE state SET country_id = '{$data['country_id']}' WHERE state_id = '{$data['state_id']}'
            ");
        $statement->execute();
    }

    public function updateReferral($post)
    {
        $id = $post['referralSourceId'];
        $where = "WHERE referral_source_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];

            $query = "UPDATE referral_source SET referral_source_name = '$name'
                      $where";
        }


        
        $this->getQueryResult($query);

    }

    public function updatePartyPosition($post)
    {
        $id = $post['partyPositionId'];
        $where = "WHERE party_position_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];
            $procType = $post['procType'];
            $procSubType = $post['procSubType'];

            $query = "UPDATE party_position SET party_position_name = '$name', proc_type_id = '$procType',proc_subtype_id = '$procSubType'
                      $where";
        }

        
        $this->getQueryResult($query);

    }

    public function updatePartyCategory($post)
    {
        $id = $post['partyCategoryId'];
        $where = "WHERE party_category_id = $id";
        if ($post['action'] == 'update') {
            $name = $post['name'];

            $query = "UPDATE party_category SET party_category_name = '$name'
                      $where";
        }

        //        return $query;
        $this->getQueryResult($query);

    }

    public function getCjTypePosition($countryId = null)
    {


        $statement = $this->dbAdapter->query("
				SELECT * FROM cj_type
				WHERE cj_type_name != NULL OR cj_type_name != '' AND inactive = 0
				ORDER BY cj_type_name
				");

        $results = $statement->execute();
        return $results->getResource()->fetchAll();
    }

    public function cjPosListMaster($CRUD, $data = null)
    {
        $slave = $CRUD . 'CjPos';
        return method_exists($this, $slave) ? $this->$slave($data) : false;
    }

    public function readCjPos($data)
    {
        $where = "";



        if (isset($data['inactive'])) {
            if ($data['inactive'] == 0) {
                $where = "AND inactive = 0";
            } else if ($data['inactive'] == 1) {
                $where = "AND inactive = 1";
            } else {
                $where = "";
            }
        }

        $statement = $this->dbAdapter->query("
                                SELECT cj_position_id, cj_position_name, cj_type,court_id, inactive
                                 FROM cj_position WHERE cj_position_name != NULL OR
                                 cj_position_name != '' {$where}
         ORDER BY cj_position_name");
        $results = $statement->execute();
        $cjPosList = $results->getResource()->fetchAll();

        $statement = $this->dbAdapter->query("
                                SELECT court_id, court_name
                                 FROM court_type WHERE court_name != NULL OR court_name != ''
       ORDER BY court_name ");

        $results = $statement->execute();
        $ht_list = $results->getResource()->fetchAll();
        $count = ['cjPosList' => count($cjPosList), 'ht_list' => count($ht_list)];

        $sorted_list = array();

        for ($i = 0; $i < $count['ht_list']; $i++) {

            $sorted_list[$i]['court_name'] = $ht_list[$i]['court_name'];
            $sorted_list[$i]['court_id'] = $ht_list[$i]['court_id'];
            $temp_arr = array();

            for ($j = 0; $j < $count['cjPosList']; $j++) {

                if ($cjPosList[$j]['court_id'] == $ht_list[$i]['court_id']) {
                    $temp_arr[$j]['cj_position_name'] = $cjPosList[$j]['cj_position_name'];
                    $temp_arr[$j]['cj_position_id'] = $cjPosList[$j]['cj_position_id'];
                    $temp_arr[$j]['inactive'] = $cjPosList[$j]['inactive'];
                }
            }

            $sorted_list[$i]['Sub_Cat'] = $temp_arr;

        }

        return $sorted_list;
    }
}
