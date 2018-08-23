<?php
namespace BulkGate\CartSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Customers extends Extensions\Customers
{
    public function getTotal()
    {
        return (int) $this->db->execute("SELECT COUNT(`customer_id`) AS `total` FROM `{$this->db->table('customer')}` WHERE `status` = 1 AND `telephone`")->getRow()->total;
    }


    public function getFilteredTotal(array $customers)
    {
        return (int) $this->db->execute("SELECT COUNT(`customer_id`) AS `total` FROM `{$this->db->table('customer')}` WHERE `status` = 1 AND `telephone` AND `customer_id` IN ('".implode("','", $customers)."')")->getRow()->total;
    }


    protected function loadCustomers(array $customers, $limit = null)
    {
        return $this->db->execute("
            SELECT 
                `{$this->db->table('customer')}`.`customer_id`, 
                `{$this->db->table('customer')}`.`store_id`, 
                `{$this->db->table('customer')}`.`email`,
                `{$this->db->table('customer')}`.firstname AS `first_name`, 
                `{$this->db->table('customer')}`.lastname AS `last_name`, 
                `{$this->db->table('customer')}`.telephone AS `phone_mobile`, 
                `{$this->db->table('address')}`.company AS `company_name`,
                `{$this->db->table('address')}`.address_1 AS `street1`, 
                `{$this->db->table('address')}`.address_2 AS `street2`, 
                `{$this->db->table('address')}`.`city`, 
                `{$this->db->table('address')}`.`postcode` AS `zip`, 
                LOWER(`{$this->db->table('country')}`.`iso_code_2`) AS `country`, 
                `{$this->db->table('zone')}`.`name` AS `state`
            FROM `{$this->db->table('customer')}`
            LEFT JOIN `{$this->db->table('address')}` ON `{$this->db->table('address')}`.`customer_id` = `{$this->db->table('customer')}`.`customer_id`
            LEFT JOIN `{$this->db->table('country')}` ON `{$this->db->table('address')}`.`country_id` = `{$this->db->table('country')}`.`country_id`
            LEFT JOIN `{$this->db->table('zone')}` ON `{$this->db->table('address')}`.`zone_id` = `{$this->db->table('zone')}`.`zone_id`
            WHERE 
                ". (count($customers) > 0 ? " `{$this->db->table('customer')}`.`customer_id` IN ('".implode("','", $customers)."') AND " : '') . "
                `{$this->db->table('customer')}`.`telephone`
                ". ($limit !== null ? "LIMIT $limit" : '')
        )->getRows();
    }


    protected function filter(array $filters)
    {
        $customers = array(); $filtered = false;

        foreach($filters as $key => $filter)
        {
            if(isset($filter['values']) && count($filter['values']) > 0 && !$this->empty)
            {
                switch ($key)
                {
                    case 'first_name':
                        $customers = $this->getCustomers($this->db->execute("SELECT `customer_id` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'firstname', 'customer')}"), $customers);
                        break;
                    case 'last_name':
                        $customers = $this->getCustomers($this->db->execute("SELECT `customer_id` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'lastname', 'customer')}"), $customers);
                        break;
                    case 'country':
                        $customers = $this->getCustomers($this->db->execute("SELECT `{$this->db->table('address')}`.`customer_id` FROM `{$this->db->table('address')}` LEFT JOIN `{$this->db->table('country')}` ON `{$this->db->table('address')}`.`country_id` = `{$this->db->table('country')}`.`country_id` WHERE {$this->getSql($filter, 'iso_code_2', 'country')}"), $customers);
                        break;
                    case 'city':
                        $customers = $this->getCustomers($this->db->execute("SELECT `customer_id` FROM `{$this->db->table('address')}` WHERE {$this->getSql($filter, 'city', 'address')}"), $customers);
                        break;
                    case 'zip':
                        $customers = $this->getCustomers($this->db->execute("SELECT `customer_id` FROM `{$this->db->table('address')}` WHERE {$this->getSql($filter, 'postcode', 'address')}"), $customers);
                        break;
                    case 'company_name':
                        $customers = $this->getCustomers($this->db->execute("SELECT `customer_id` FROM `{$this->db->table('address')}` WHERE {$this->getSql($filter, 'company', 'address')}"), $customers);
                        break;
                    case 'newsletter':
                        $customers = $this->getCustomers($this->db->execute("SELECT `customer_id` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'newsletter', 'customer')}"), $customers);
                        break;
                    case 'order_amount':
                        $customers = $this->getCustomers($this->db->execute("SELECT `{$this->db->table('order')}`.`customer_id`, MAX(`{$this->db->table('order')}`.`total`) AS `total` FROM `{$this->db->table('order')}` GROUP BY `{$this->db->table('order')}`.`customer_id` HAVING {$this->getSql($filter, 'total')}"), $customers);
                        break;
                    case 'all_orders_amount':
                        $customers = $this->getCustomers($this->db->execute("SELECT `{$this->db->table('order')}`.`customer_id`, SUM(`{$this->db->table('order')}`.`total`) AS `total` FROM `{$this->db->table('order')}` GROUP BY `{$this->db->table('order')}`.`customer_id` HAVING {$this->getSql($filter, 'total')}"), $customers);
                        break;
                    case 'product':
                        $customers = $this->getCustomers($this->db->execute("SELECT `{$this->db->table('order')}`.`customer_id` FROM `{$this->db->table('order')}` INNER JOIN `{$this->db->table('order_product')}` ON `{$this->db->table('order_product')}`.`order_id` = `{$this->db->table('order')}`.`order_id` WHERE {$this->getSql($filter, 'name', 'order_product')} GROUP BY `{$this->db->table('order')}`.`customer_id`"), $customers);
                        break;
                    case 'product_model':
                        $customers = $this->getCustomers($this->db->execute("SELECT `{$this->db->table('order')}`.`customer_id` FROM `{$this->db->table('order')}` INNER JOIN `{$this->db->table('order_product')}` ON `{$this->db->table('order_product')}`.`order_id` = `{$this->db->table('order')}`.`order_id` WHERE {$this->getSql($filter, 'model', 'order_product')} GROUP BY `{$this->db->table('order')}`.`customer_id`"), $customers);
                        break;
                    case 'registration_date':
                        $customers = $this->getCustomers($this->db->execute("SELECT `customer_id` FROM `{$this->db->table('customer')}` WHERE {$this->getSql($filter, 'date_added', 'customer')}"), $customers);
                        break;
                    case 'order_date':
                        $customers = $this->getCustomers($this->db->execute("SELECT `customer_id` FROM `{$this->db->table('order')}` WHERE {$this->getSql($filter, 'date_added', 'customer')}"), $customers);
                        break;
                }
                $filtered = true;
            }
        }

        return array(array_unique($customers), $filtered);
    }
}
