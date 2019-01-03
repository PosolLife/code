<?php
namespace Customer\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class CustomerTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    // Get all rows from the customers table
    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    // Get all customers (could be filtered by ac_active field) from the customers table
    public function getAllCustomers($active = null)
    {
        $sql = "            
            SELECT c.customer_id, c.cust_fn, c.cust_ln, c.prim_email, c.ac_active,  
              c.del_account, sc.status, sc.stripe_customer_id, sc.inactive, sc.stripe_id,
              t2c.tos_id, t2c.created_at AS tos_accepted, t2c.tos_to_customer_id,
              (
                SELECT COUNT(cust_device_id)
                FROM device_token
                WHERE cust_device_id IN (
                  SELECT cust_device_id FROM cust_device
                  WHERE customer_id = c.customer_id
                  AND inactive = 0
                ) AND inactive = 0 
              ) AS devices
            FROM customers c
            LEFT JOIN stripe_customer sc
              ON sc.customer_id = c.customer_id
            LEFT JOIN tos_to_customer t2c
              ON t2c.tos_to_customer_id = (
                  SELECT tos_to_customer_id FROM tos_to_customer
                    WHERE customer_id = c.customer_id
                    ORDER BY tos_to_customer_id DESC 
                    LIMIT 1
              )            
        ";

        // Activated via email
        if (!is_null($active)) {
            $sql .= " WHERE c.ac_active = " . (int)$active;
        }

        $sql .= " GROUP BY c.customer_id";

        $statement = $this->tableGateway->adapter->query($sql);
        $rows = $statement->execute();

        $customers = array();

        foreach ($rows as $row) {
            $customers[$row['customer_id']] = $row;
        }

        return $customers;
    }

    // Get last inserted ID in the customers table
    public function getLastInsertId()
    {
        return $this->tableGateway->getLastInsertValue();
    }

    // Get customer by ID from the customers table
    public function getCustomer($id, $includeDeleted = false)
    {
        if (!$includeDeleted) {
            $options['del_account'] = 0;
        }

        $sql = "
            SELECT c.*, 
                i.stripe_invoice_id, i.invoice_id, i.subscription_id, i.stripe_customer_id,
                i.amount AS invoice_amount, i.paid, i.charge_id, i.attempt_count, i.next_payment_attempt,
                i.canceled_at, i.ended_at, i.period_start, i.period_end, i.created_at, i.updated_at,
                p.stripe_plan_id, p.plan_id, p.name, p.amount, p.currency, p.sub_interval, p.description,            
                ca.stripe_card_id, ca.stripe_card_id, ca.card_id, ca.inactive, ca.name AS customer_name, 
                ca.address_zip, ca.address_country, ca.address_state, ca.address_city, ca.address_street,             
                sc.status, sc.stripe_id, p.amount AS plan_amount, ec.is_ent_admin, ec.ent_id, ec.ent_cust_id,
                TIMESTAMPDIFF(MINUTE, now(), period_end) AS min_left, -- subscription minutes left
                                
                IF(
                    t2c.tos_id = (
                        SELECT tos_id FROM tos
                        WHERE inactive = 0
                        ORDER BY tos_id DESC
                        LIMIT 1
                    ), -- current version of Terms of Service
                1, 0) AS accept_tos,  -- does the customer accept the new Terms of Service
                
                (SELECT tos_id FROM tos
                    WHERE inactive = 0
                    ORDER BY tos_id DESC
                    LIMIT 1    
                ) AS current_tos, -- current version of Terms of Service
                
                IF(
                    ((TIMESTAMPDIFF(MINUTE, now(), period_end) > 0 AND i.paid) -- paid subscription
                      OR (TIMESTAMPDIFF(MINUTE, period_start, now()) < 120) -- additional time for payment
                    ) AND (sc.status = 'active' OR sc.status = 'trialing'),        
                1, 0) AS active,  -- is active subscription
                
                (SELECT -- find the latest paid subscription and add a week to the end of the period
                    IF(TIMESTAMPDIFF(MINUTE, now(), DATE_ADD(i2.period_end, INTERVAL 6 DAY)) > 0 
                        AND period_end < now()
                        AND ca.card_id IS NOT NULL
                        AND (ca.inactive != 1 OR ca.inactive IS NULL), -- check for inactive card
                    1, 0) -- returns 1 if less than 6 days have passed since the last successful payment
                    FROM stripe_invoice i2
                    WHERE c.customer_id = " . (int)$id . "
                        AND i2.paid = 1                        
                    ORDER BY i2.stripe_invoice_id DESC
                    LIMIT 1
                ) AS extra_active -- active status a week after the end of the subscription period
               
            FROM customers c
            LEFT JOIN stripe_customer sc
              ON sc.customer_id = c.customer_id    
            LEFT JOIN stripe_invoice i
                ON i.stripe_customer_id = sc.stripe_customer_id
            LEFT JOIN stripe_plan p
              ON i.plan_id = p.stripe_plan_id
            LEFT JOIN stripe_card ca
              ON i.stripe_customer_id = ca.stripe_customer_id
              AND ca.inactive != 1
            LEFT JOIN ent_cust ec
              ON ec.customer_id = c.customer_id
	            AND ec.disabled = 0
              
            LEFT JOIN tos_to_customer t2c
              ON t2c.tos_to_customer_id = (
                SELECT tos_to_customer_id FROM tos_to_customer
                WHERE customer_id = c.customer_id
                  AND inactive = 0
                ORDER BY tos_to_customer_id DESC 
                LIMIT 1
              ) -- latest accepted version of Terms of Service  
                
            WHERE c.customer_id = " . (int)$id . "
                AND " . ((!$includeDeleted) ? 'del_account = 0' : ' 1 = 1') . "
            ORDER BY stripe_invoice_id DESC
            LIMIT 1
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $row = new ResultSet;
        $row->initialize($result);

        $customer = $row->current();
        return $customer ? (object)$customer : false;
    }

    // Get customer by hash from the customers table
    public function getCustomerByHash($hashObject)
    {
        if (is_object($hashObject)) {
            $row = $this->tableGateway->select(
                function (\Zend\Db\Sql\Select $select) use ($hashObject) {
                    $select->where(array(
                        'customer_id' => (int)$hashObject->id,
                        'hash' => $hashObject->hash,
                        'del_account' => 0
                    ));
                }
            );

            return $row->current();
        }

        return false;
    }

    // Save customer (add or update) in the customers table
    public function saveCustomer($data, $customerId = 0)
    {
        $customerId = (int)$customerId;

        if (isset($data['birthday']) && $data['birthday']) {
            $data['birthday'] = $this->formatDate($data['birthday']);
        }

        if ($customerId == 0) {
            $this->tableGateway->insert($data);
            $customerId = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getCustomer($customerId)) {
                $this->tableGateway->update($data, array('customer_id' => $customerId));
            } else {
                throw new \Exception('Customer does not found');
            }
        }

        return $customerId;
    }

    // Get customer by prim_email field from the customers table
    public function getCustomerByEmail($email, $deleted = 0)
    {
        $row = $this->tableGateway->select(
            function (\Zend\Db\Sql\Select $select) use ($email, $deleted) {
                $select->where(array(
                    'prim_email' => $email,
                    'del_account' => $deleted
                ));
            }
        );

        return $row->current();
    }

    // Get customer by sec_email field from the customers table
    public function getCustomerBySecondaryEmail($email)
    {
        $row = $this->tableGateway->select(
            function (\Zend\Db\Sql\Select $select) use ($email) {
                $select->where(array(
                    'sec_email' => $email,
                    'del_account' => 0
                ));
            }
        );

        return $row->current();
    }

    // Get customers by one of the fields (prim_email, cust_fn, cust_ln) from the customers table
    public function getCustomerByQuery($query, $field)
    {
        if ($field == 'email') {
            $where = "WHERE prim_email LIKE '%" . addslashes($query) . "%'";
        } elseif  ($field == 'first') {
            $where = "WHERE cust_fn LIKE '%" . addslashes($query) . "%'";
        } elseif  ($field == 'middle') {
            $where = "WHERE cust_mn LIKE '%" . addslashes($query) . "%'";
        } elseif  ($field == 'last') {
            $where = "WHERE cust_ln LIKE '%" . addslashes($query) . "%'";
        } else {
            $where = "
                WHERE prim_email LIKE '%" . addslashes($query) . "%'
                    OR cust_fn LIKE '%" . addslashes($query) . "%'
                    OR cust_ln LIKE '%" . addslashes($query) . "%'
            ";
        }

        $sql = "
            SELECT customer_id, cust_fn, cust_ln, prim_email
            FROM customers
            " . $where . "
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $rows = new ResultSet;
        $rows->initialize($result);

        return $rows;
    }

    // Get all rows from the certain table
    public function getOptions($table)
    {
        $sql = new \Zend\Db\Sql\Sql($this->tableGateway->adapter);

        $select = $sql->select($table);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute($statement);
        $rows = new ResultSet();

        $rows->initialize($result);
        return $rows->toArray();
    }

    // Add new row for the certain customer in the customer_practice_area table
    public function setCustomerPracticeArea($id, $sublist)
    {
        if ($id && $id > 0 && $sublist) {
            // Do not allow zero value
            $zero = array_search('0', $sublist);
            if ($zero !== false) unset($sublist[$zero]);

            // Rewrite customer practice area
            $this->deleteCustomerPracticeArea($id);

            $sql = "INSERT INTO `customer_practice_area` (`customer_id`, `practice_area_id`) VALUES";

            foreach ($sublist as $list) {
                $sql .= " ('{$id}','{$list}'),";
            }

            $sql = rtrim($sql, ',');

            $statement = $this->tableGateway->adapter->query($sql);
            $statement->execute();
        }
    }

    // Delete all rows for the certain customer from the customer_practice_area table
    public function deleteCustomerPracticeArea($id)
    {
        $sql = "DELETE FROM `customer_practice_area` WHERE `customer_id` = " . (int)$id;

        $statement = $this->tableGateway->adapter->query($sql);
        $statement->execute();
    }

    // Get all rows for the certain customer from the customer_practice_area table
    public function getCustomerPracticeArea($id)
    {
        $sql = "SELECT * FROM `customer_practice_area` WHERE `customer_id` = " . (int)$id;

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $rows = new ResultSet;
        $rows->initialize($result);

        return $rows;
    }

    // Get all states for certain country from the state table
    public function getCountryStates($countryId)
    {
        $sql = new Sql($this->tableGateway->adapter);

        $select = $sql->select('state')->where("country_id = '" . $countryId . "'");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute($statement);
        $rows = new ResultSet;
        $rows->initialize($result);
        return $rows->toArray();
    }

    // Get all cities for certain state from the city table
    public function getStateCities($stateId)
    {
        $sql = new Sql($this->tableGateway->adapter);

        $select = $sql->select('city')->where("state_id = '" . $stateId . "'");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute($statement);
        $rows = new ResultSet;
        $rows->initialize($result);
        return $rows->toArray();
    }

    // Get city_name by city_id
    public function getCityName($id)
    {
        $sql = "SELECT city_name FROM `city` WHERE `city_id` = " . (int)$id;

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $row = new ResultSet;
        $row->initialize($result);

        $current = $row->current();
        return ($current) ? $current->city_name : false;
    }

    // Get state_name by state_id
    public function getStateName($id)
    {
        $sql = "SELECT state_name FROM `state` WHERE `state_id` = " . (int)$id;

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $row = new ResultSet;
        $row->initialize($result);

        $current = $row->current();
        return ($current) ? $current->state_name : false;
    }

    // Get country name by country_id
    public function getCountryName($id)
    {
        $sql = "SELECT name FROM country WHERE country_id = " . (int)$id;

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $row = new ResultSet;
        $row->initialize($result);

        $current = $row->current();
        return ($current) ? $current->name : false;
    }

    // ----- Terms of Service -----

    // Get Terms of Service
    public function getTOS($id = null)
    {
        if ($id) {
            $sql = "
                SELECT * FROM tos
                WHERE tos_id = " . (int)$id . "
                    AND inactive = 0
            ";

            $statement = $this->tableGateway->adapter->query($sql);
            $result = $statement->execute();
            $row = new ResultSet;
            $row->initialize($result);

            return $row->current();
        } else {
            $sql = "
                SELECT * FROM tos
                WHERE inactive = 0
                ORDER BY created_at DESC
            ";

            $statement = $this->tableGateway->adapter->query($sql);
            return $statement->execute();
        }
    }

    // Get latest Terms of Service
    public function getLatestTOS()
    {
        $sql = "
            SELECT * FROM tos
            WHERE inactive = 0
            ORDER BY tos_id DESC
            LIMIT 1
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $row = new ResultSet;
        $row->initialize($result);

        $tos = $row->current();
        return $tos ? (object)$tos : false;
    }

    // Add or Update Terms of Service
    public function saveTOS($data)
    {
        if (isset($data['tos_id']) && $data['tos_id'] > 0) {
            $sql = "
                UPDATE tos SET                
                  text = '" . addslashes($data['text']) . "',
                  updated_at = now()
                WHERE tos_id = " . (int)$data['tos_id'] . ";
            ";
        } else {
            $sql = "
                INSERT INTO tos SET                
                  text = '" . addslashes($data['text']) . "';
            ";
        }

        $statement = $this->tableGateway->adapter->query($sql);
        $statement->execute();
    }

    // Add record of customer acceptance of the Terms of Service agreement
    public function acceptTos($customerId, $tosId)
    {
        $sql = "
            INSERT INTO tos_to_customer SET                
              customer_id = " . (int)$customerId . ",
              tos_id = " . (int)$tosId . ";
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $statement->execute();

        return $this->tableGateway->getLastInsertValue();
    }

    // ---------------------

    // Get available reasons for unsubscribing
    public function getCancelReasons()
    {
        $sql = "
            SELECT * FROM cancel_reason
            WHERE inactive = 0
            ORDER BY display_order
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        return $statement->execute();
    }

    // ---------------------

    // Get list of Partner Organizations
    public function getPartnerOrganizations($all = false)
    {
        $sql = "
            SELECT * FROM partner_org
            WHERE inactive = 0              
        ";

        if (!$all) {
            $sql .= " AND cust_visible = 1";
        }

        $statement = $this->tableGateway->adapter->query($sql);
        return $statement->execute();
    }

    // Get Partner Organization by id
    public function getPartnerOrganization($id)
    {
        $sql = "
            SELECT * FROM partner_org
            WHERE partner_org_id = " . (int)$id . "
              AND inactive = 0
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();

        $row = new ResultSet;
        $row->initialize($result);

        return $row->current();
    }

    // Get Coupons by Partner id
    public function getPartnerCoupons($partnerId)
    {
        $sql = "
            SELECT * FROM stripe_coupon
            WHERE partner_org_id = " . (int)$partnerId . "
              AND inactive = 0              
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        return $statement->execute();
    }

    // Get list of all active coupons
    public function getCoupons()
    {
        $sql = "
            SELECT * FROM stripe_coupon
	          WHERE inactive = 0
	            AND redeem_by > NOW()
              OR redeem_by IS NULL
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        return $statement->execute();
    }

    // Add or Update Partner Organization
    public function savePartner($data)
    {
        if (isset($data['partner_org_id']) && $data['partner_org_id'] > 0) {
            $sql = "
                UPDATE partner_org SET            
                  full_name = '" . addslashes($data['full_name']) . "',
                  abbreviation = '" . addslashes($data['abbreviation']) . "'
                WHERE partner_org_id = " . (int)$data['partner_org_id'] . ";
            ";
        } else {
            $sql = "
                INSERT INTO partner_org SET                
                  full_name = '" . $data['full_name'] . "',
                  abbreviation = '" . $data['abbreviation'] . "';
            ";
        }

        $statement = $this->tableGateway->adapter->query($sql);
        $statement->execute();
    }

    public function getPartnerCoupon($partnerId, $coupon)
    {
        $sql = "
            SELECT * FROM stripe_coupon
            WHERE partner_org_id = " . (int)$partnerId . "
              AND coupon_id = '" . addslashes($coupon) . "'          
              AND inactive = 0              
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $row = new ResultSet;
        $row->initialize($result);

        return $row->current();
    }

    public function makePartnerMemberInactive($customerId)
    {
        try {
            $query = "
                UPDATE partner_org_member SET inactive = 1 
                WHERE customer_id = " . (int)$customerId . ";
            ";

            $statement = $this->tableGateway->adapter->query($query);
            $statement->execute();

            return array('id' => $customerId);
        } catch (\Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    public function getLatestStripeCustomerDiscount($stripeCustomerId, $stripeCouponId)
    {
        $sql = "
            SELECT * FROM stripe_discount
            WHERE stripe_customer_id = " . (int)$stripeCustomerId . "
              AND stripe_coupon_id = " . (int)$stripeCouponId . "      
              AND inactive = 0
            ORDER BY stripe_discount_id DESC
            LIMIT 1
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $row = new ResultSet;
        $row->initialize($result);

        return $row->current();
    }

    public function getDiscountBySubscription($subscriptionId)
    {
        $sql = "
            SELECT * FROM stripe_discount
            WHERE subscription_id = '" . $subscriptionId . "' 
              AND inactive = 0
            ORDER BY stripe_discount_id DESC
            LIMIT 1
        ";

        $statement = $this->tableGateway->adapter->query($sql);
        $result = $statement->execute();
        $row = new ResultSet;
        $row->initialize($result);

        return $row->current();
    }

    // ---------------------

    // Get all rows from the referral_source table
    public function getReferralOptions()
    {
        $rows = $this->getOptions('referral_source');
        $referrals = array();
        foreach ($rows as $row) {
            if ($row['referral_source_name'] != null) {
                $referrals[$row['referral_source_id']] = $row['referral_source_name'];
            }
        }
        return $referrals;
    }

    // Get all rows visible to customers from the practice_area table
    public function getPracticeAreaOptions()
    {
        $rows = $this->getOptions('practice_area');
        $practiceAreas = array();
        foreach ($rows as $row) {
            if ($row['cust_visible'] == 1) {
                if ($row['practice_area_name'] != null) {
                    $practiceAreas[$row['practice_area_id']] = $row['practice_area_name'];
                }
            }
        }
        return $practiceAreas;
    }

    // Get all rows from the industry table
    public function getIndustryOptions()
    {
        $rows = $this->getOptions('industry');
        $industries = array();
        $industries['-1'] = 'Other';
        foreach ($rows as $row) {
            if ($row['industry_name'] != null) {
                $industries[$row['industry_id']] = $row['industry_name'];
            }
        }
        return $industries;
    }

    // Get all rows from the gender table
    public function getGenderOptions()
    {
        $rows = $this->getOptions('gender');
        $genders = array();
        foreach ($rows as $row) {
            if ($row['gender_name'] != null) {
                $genders[$row['gender_id']] = $row['gender_name'];
            }
        }
        return $genders;
    }

    // Get all rows from the country table
    public function getCountryOptions()
    {
        $rows = $this->getOptions('country');
        $countries = array();
        foreach ($rows as $row) {
            if ($row['name'] != null) {
                $countries[$row['country_id']] = $row['name'];
            }
        }
        return $countries;
    }

    // Get states by country_id
    public function getStateOptions($countryId)
    {
        $rows = $this->getCountryStates($countryId);
        $states = array();
        foreach ($rows as $row) {
            if ($row['state_name'] != null) {
                $states[$row['state_id']] = $row['state_name'];
            }
        }
        return $states;
    }

    // Get cities by state_id
    public function getCityOptions($stateId)
    {
        $rows = $this->getStateCities($stateId);
        $cities = array();
        foreach ($rows as $row) {
            if ($row['city_name'] != null) {
                $cities[$row['city_id']] = $row['city_name'];
            }
        }
        return $cities;
    }

    // Get randomly generated hash string
    public function getHash($length = 6)
    {
        $chars = str_split('123456789ABCDEFGHIJKLMNPQRSTUVWXYZ');
        shuffle($chars);
        $hash = '';
        foreach (array_rand($chars, $length) as $k) {
            $hash .= $chars[$k];
        }
        return $hash;
    }

    // Make refer to first DB record (which is NULL)
    public function checkEmptyId($field)
    {
        $field = abs($field);
        return ($field) ? $field : 1;
    }

    // ---------------------

    // Format date like 0000-00-00
    protected function formatDate($date)
    {
        if (strpos($date, '/') !== false) {
            $date = str_replace('/', '.', $date);
        }
        $timestamp = strtotime($date);
        return date('Y-m-d', $timestamp);
    }
}
 