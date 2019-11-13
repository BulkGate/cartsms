<?php
namespace BulkGate\CartSms;

use BulkGate;
use BulkGate\Extensions\Hook\Variables;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class HookLoad extends BulkGate\Extensions\Strict implements BulkGate\Extensions\Hook\ILoad
{
    /** @var Database */
    private $db;

    /** @var BulkGate\Extensions\ILocale */
    private $locale;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->locale = new BulkGate\Extensions\LocaleSimple();
    }

    public function language(Variables $variables)
    {
        if($variables->get('language_id'))
        {
            $row = $this->db->execute($this->db->prepare("SELECT `language_id`, `name`, `code`, `directory` FROM `{$this->db->table('language')}` WHERE `language_id` = %s AND `status` = 1", array(
                $variables->get('language_id')
            )))->getRow();

            if($row)
            {
                $language = new \Language($row->directory);
                $language->load($row->code);

                if(extension_loaded('intl'))
                {
                    $this->locale = new BulkGate\Extensions\LocaleIntl($row->code);
                }
                else
                {
                    $this->locale = new BulkGate\Extensions\LocaleSimple($language->get('date_format_short') !== 'date_format' ? $language->get('date_format_short') : 'd/m/Y');
                }
            }
        }
    }

    public function order(Variables $variables)
    {
        if($variables->get('order_id'))
        {
            $order = $this->db->execute(
                $this->db->prepare("
                    SELECT `{$this->db->table('order')}` .*, LOWER(`payment_country`.`iso_code_2`) AS `payment_country_iso`, LOWER(`shipping_country`.`iso_code_2`) AS `shipping_country_iso` 
                    FROM `{$this->db->table('order')}` 
                    LEFT JOIN `{$this->db->table('country')}` AS `payment_country` ON `{$this->db->table('order')}`.`payment_country_id` = `payment_country`.`country_id`  
                    LEFT JOIN `{$this->db->table('country')}` AS `shipping_country` ON `{$this->db->table('order')}`.`payment_country_id` = `shipping_country`.`country_id` 
                    WHERE `order_id` = %s",
                    array(
                        $variables->get('order_id')
                    )
                )
            )->getRow();

            if($order)
            {
                $variables->set('store_id', (int) $order->store_id);

                $variables->set('long_order_id', sprintf("%06d", $variables->get('order_id')));

                $variables->set('customer_id', (int) $order->customer_id);
                $date = new \DateTime($order->date_added);

                $variables->set('customer_mobile', $order->telephone, '', false);
                $variables->set('customer_fax', $order->fax);

                $variables->set('order_payment', $order->payment_method);
                $variables->set('order_tracking', $order->tracking);
                $variables->set('order_currency', $order->currency_code);
                $variables->set('order_total_paid', number_format($order->total, 2));
                $variables->set('order_total_locale', $this->locale->price($order->total, $variables->get('order_currency')));

                $variables->set('order_message', $order->comment);

                $variables->set('order_datetime', $this->locale->datetime($date));
                $variables->set('order_date', $this->locale->date($date));
                $variables->set('order_date1', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3.\\2.\\1', $order->date_added));
                $variables->set('order_date2', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3/\\2/\\1', $order->date_added));
                $variables->set('order_date3', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3-\\2-\\1', $order->date_added));
                $variables->set('order_date4', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\1-\\2-\\3', $order->date_added));
                $variables->set('order_date5', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2.\\3.\\1', $order->date_added));
                $variables->set('order_date6', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2/\\3/\\1', $order->date_added));
                $variables->set('order_date7', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2-\\3-\\1', $order->date_added));
                $variables->set('order_time',  preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\4:\\5',     $order->date_added));
                $variables->set('order_time1', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\4:\\5:\\6', $order->date_added));

                $this->address($variables, $order);
                $this->address($variables, $order, 'invoice_', 'payment_');

                $this->orderProducts($variables);
            }
        }
    }

    public function orderProducts(Variables $variables)
    {
        if($variables->get('order_id'))
        {
            $result = $this->db->execute($this->db->prepare("SELECT `order_product_id`, `value` FROM `{$this->db->table('order_option')}` WHERE `order_id` = %s", array(
                $variables->get('order_id')
            )));

            $options = array();

            foreach($result as $row)
            {
                $options[$row->order_product_id][] = $row->value;
            }

            $p1 = $p2 = $p3 = $p4 = $pr1 = $pr2 = $pr3 = $pr4 = array();

            $list = $this->db->execute($this->db->prepare("SELECT * FROM `{$this->db->table('order_product')}` WHERE `order_id` = %s", array(
                $variables->get('order_id')
            )));

            $filter = $variables->get('filter_products', array());

            foreach($list as $row)
            {
                if(empty($filter) || in_array($row->order_product_id, $filter))
                {
                    $option_string = $option_string_new_line = '';

                    if(isset($options[$row->order_product_id]))
                    {
                        $option_string .= '('.implode(',', $options[$row->order_product_id]).')';
                        $option_string_new_line .= implode("\n -", $options[$row->order_product_id]);
                    }

                    $p1[] = $row->quantity.'x '.$row->name.$option_string.' '.$row->model;
                    $p2[] = $row->quantity.'x '.$row->name.$option_string;
                    $p3[] = $row->quantity.'x ('.$row->product_id.')'.$row->name.$option_string.' '.$row->model;
                    $p4[] = $row->quantity.'x '.$row->model.$option_string;

                    $pr1[] = $row->quantity.','.$row->name.','.$this->locale->price($row->total, $variables->get('order_currency')).(strlen($option_string_new_line) ? "\n -".$option_string_new_line : '');
                    $pr2[] = $row->quantity.';'.$row->name.';'.$this->locale->price($row->total, $variables->get('order_currency')).(strlen($option_string_new_line) ? "\n -".$option_string_new_line : '');
                    $pr3[] = $row->quantity.','.$row->model.','.$this->locale->price($row->total, $variables->get('order_currency')).(strlen($option_string_new_line) ? "\n -".$option_string_new_line : '');
                    $pr4[] = $row->quantity.';'.$row->model.';'.$this->locale->price($row->total, $variables->get('order_currency')).(strlen($option_string_new_line) ? "\n -".$option_string_new_line : '');
                }
            }

            $variables->set('order_products1', implode('; ', $p1));
            $variables->set('order_products2', implode('; ', $p2));
            $variables->set('order_products3', implode('; ', $p3));
            $variables->set('order_products4', implode('; ', $p4));


            $variables->set('order_products5', implode("\n", $p1));
            $variables->set('order_products6', implode("\n", $p2));
            $variables->set('order_products7', implode("\n", $p3));
            $variables->set('order_products8', implode("\n", $p4));

            $variables->set('order_smsprinter1', implode(';', $pr1));
            $variables->set('order_smsprinter2', implode(';', $pr2));
            $variables->set('order_smsprinter3', implode(';', $pr3));
            $variables->set('order_smsprinter4', implode(';', $pr4));

            $variables->set('filter_products', '-');
        }
    }

    public function address(Variables $variables, \stdClass $address, $prefix = '', $original_prefix = 'shipping_')
    {
        $variables->set('customer_'.$prefix.'firstname', $address->{$original_prefix.'firstname'}, '', false);
        $variables->set('customer_'.$prefix.'lastname', $address->{$original_prefix.'lastname'}, '', false);

        $variables->set('customer_'.$prefix.'country_id', $address->{$original_prefix.'country_iso'}, '', false);

        $variables->set('customer_'.$prefix.'company', $address->{$original_prefix.'company'}, '', false);

        if(strlen(trim($address->{$original_prefix.'address_1'})) > 0)
        {
            $variables->set('customer_'.$prefix.'address', $address->{$original_prefix.'address_1'} . ', ' . $address->{$original_prefix.'address_2'}, '', false);
        }
        else
        {
            $variables->set('customer_'.$prefix.'address', $address->{$original_prefix.'address_1'}, '', false);
        }

        $variables->set('customer_'.$prefix.'postcode', $address->{$original_prefix.'postcode'}, '', false);
        $variables->set('customer_'.$prefix.'city', $address->{$original_prefix.'city'}, '', false);

        $variables->set('customer_'.$prefix.'country', $address->{$original_prefix.'country'}, '', false);

        $variables->set('customer_'.$prefix.'state', $address->{$original_prefix.'zone'}, '', false);
    }

    public function customer(Variables $variables)
    {
        if($variables->get('customer_id'))
        {
            $customer = $this->db->execute($this->db->prepare("
                SELECT `email`, `lastname`, `firstname`, `telephone`, `fax`, `store_id`, `address_id` 
                FROM `{$this->db->table('customer')}` 
                WHERE `customer_id` = %s", [$variables->get('customer_id')]))->getRow();

            if ($customer)
            {
                $variables->set('customer_email', $customer->email, '', false);
                $variables->set('customer_lastname', $customer->lastname, '', false);
                $variables->set('customer_firstname', $customer->firstname, '', false);
                $variables->set('customer_mobile', $customer->telephone, '', false);
                $variables->set('customer_fax', $customer->fax);
                $variables->set('store_id', (int)$customer->store_id);

                if ($customer->address_id > 0)
                {
                    $address = $this->db->execute($this->db->prepare("
                        SELECT 
                          `{$this->db->table('address')}`.*, 
                          LOWER(`{$this->db->table('country')}`.`iso_code_2`) AS `country_iso`, 
                          `{$this->db->table('country')}`.`name` AS `country`, 
                          `{$this->db->table('zone')}`.`name` AS `zone`
                        FROM `{$this->db->table('address')}` 
                        LEFT JOIN `{$this->db->table('country')}` ON `{$this->db->table('address')}`.`country_id` = `{$this->db->table('country')}`.`country_id`
                        LEFT JOIN `{$this->db->table('zone')}` ON `{$this->db->table('address')}`.`zone_id` = `{$this->db->table('zone')}`.`zone_id`
                        WHERE `address_id` = %s", [$customer->address_id]))->getRow();
                }
                else
                {
                    $address = $this->db->execute($this->db->prepare("
                        SELECT 
                          `{$this->db->table('address')}`.*, 
                          LOWER(`{$this->db->table('country')}`.`iso_code_2`) AS `country_iso`, 
                          `{$this->db->table('country')}`.`name` AS `country`, 
                          `{$this->db->table('zone')}`.`name` AS `zone`
                        FROM `{$this->db->table('address')}`
                        LEFT JOIN `{$this->db->table('country')}` ON `{$this->db->table('address')}`.`country_id` = `{$this->db->table('country')}`.`country_id`
                        LEFT JOIN `{$this->db->table('zone')}` ON `{$this->db->table('address')}`.`zone_id` = `{$this->db->table('zone')}`.`zone_id`
                        WHERE `customer_id` = %s 
                        ORDER BY `address_id` 
                        DESC LIMIT 1", [$variables->get('customer_id')]))->getRow();
                }

                if ($address)
                {
                    $this->address($variables, $address, '', '');
                }
            }
        }
    }

    public function orderStatus(Variables $variables)
    {
        if($variables->get('order_status_id'))
        {
            $state = $this->db->execute($this->db->prepare("SELECT `name` FROM `{$this->db->table('order_status')}` WHERE `order_status_id` = %s AND `language_id` = %s",
                array(
                    $variables->get('order_status_id'),
                    $variables->get('language_id')
                )
            ))->getRow();

            if($state)
            {
                $variables->set('order_status', $state->name);
            }
        }
        else if($variables->get('order_id'))
        {
            $state = $this->db->execute($this->db->prepare("
                SELECT `{$this->db->table('order_history')}`.`order_status_id`, `{$this->db->table('order_history')}`.`comment`, `{$this->db->table('order_status')}`.`name`
                FROM `{$this->db->table('order_history')}`
                LEFT JOIN `{$this->db->table('order_status')}` ON `{$this->db->table('order_history')}`.`order_status_id` = `{$this->db->table('order_status')}`.`order_status_id`
                WHERE `order_id` = %s AND `{$this->db->table('order_status')}`.`language_id` = %s
                ORDER BY `{$this->db->table('order_history')}`.`date_added` DESC
                LIMIT 1",
                array(
                    $variables->get('order_id'),
                    $variables->get('language_id')
                )
            ))->getRow();

            if($state)
            {
                $variables->set('order_status_id', (int) $state->order_status_id);
                $variables->set('order_status_message', $state->comment);
                $variables->set('order_status', $state->name);
            }
        }

    }

    public function returnOrder(Variables $variables)
    {
        if($variables->get('return_id'))
        {
            $row = $this->db->execute($this->db->prepare("SELECT * FROM `{$this->db->table('return')}` WHERE `return_id` = %s", array(
                $variables->get('return_id')
            )))->getRow();

            if($row)
            {
                $variables->set('order_id', (int) $row->order_id, '', false);
                $variables->set('product_id', (int) $row->product_id, '', false);
                $variables->set('customer_id', (int) $row->customer_id, '', false);
                $variables->set('customer_lastname', $row->lastname);
                $variables->set('customer_firstname', $row->firstname);
                $variables->set('customer_email', $row->email);
                $variables->set('customer_mobile', $row->telephone, '', false);
                $variables->set('product_name', $row->product);
                $variables->set('product_model', $row->model);
                $variables->set('product_quantity', (int) $row->quantity);
                $variables->set('return_reason_id', (int) $row->return_reason_id);
                $variables->set('return_action_id', (int) $row->return_action_id);
                $variables->set('return_status_id', (int) $row->return_status_id, '', false);
                $variables->set('return_customer_message', $row->comment, '', false);
                $variables->set('order_date', $this->locale->datetime(new \DateTime($row->date_ordered)));
                $variables->set('return_date', $this->locale->datetime(new \DateTime($row->date_added)));

                foreach(['return_action', 'return_status', 'return_reason'] as $type)
                {
                    $row_translate = $this->db->execute($this->db->prepare("SELECT `name` FROM `{$this->db->table($type)}` WHERE `{$type}_id` = %s AND `language_id` = %s",
                        array(
                            $variables->get($type.'_id'),
                            $variables->get('language_id')
                        )
                    ))->getRow();

                    $variables->set($type, $row_translate ? $row_translate->name : '-');
                }
            }
        }
    }

    public function product(Variables $variables)
    {
        if($variables->get('product_id'))
        {
            $product = $this->db->execute($this->db->prepare("
                SELECT `{$this->db->table('product')}`.*, `{$this->db->table('product_description')}`.`name`, `{$this->db->table('product_description')}`.`description`
                FROM `{$this->db->table('product')}` 
                LEFT JOIN `{$this->db->table('product_description')}` ON `{$this->db->table('product')}`.`product_id` = `{$this->db->table('product_description')}`.`product_id` 
                WHERE `{$this->db->table('product')}`.`product_id` = %s AND `{$this->db->table('product')}`.`status` = 1",
                array(
                    $variables->get('product_id')
                )
            ))->getRow();

            if($product)
            {
                $variables->set('product_name', $product->name, '', false);
                $variables->set('product_model', $product->model, '', false);
                $variables->set('product_description', trim(strip_tags(html_entity_decode($product->description, ENT_QUOTES))), '', false);
                $variables->set('product_price', number_format($product->price, 2), '', false);
                $variables->set('product_price_locale', $this->locale->price($product->price, $variables->get('order_currency')), '', false);

                $variables->set('product_quantity', (int) $product->quantity, '', false);
                $variables->set('product_minimal_quantity', (int) $product->minimum, '', false);

                $variables->set('product_sku', $product->sku, '', false);
                $variables->set('product_upc', $product->upc, '', false);
                $variables->set('product_ean', $product->ean, '', false);
                $variables->set('product_jan', $product->jan, '', false);
                $variables->set('product_isbn', $product->isbn, '', false);
                $variables->set('product_mpn', $product->mpn, '', false);
            }
        }
    }

    public function shop(Variables $variables)
    {
        $result = $this->db->execute("SELECT `store_id`, `key`, `value` FROM `{$this->db->table('setting')}` WHERE `key` IN ('config_name','config_email','config_telephone', 'config_currency')");

        foreach($result as $row)
        {
            if((int) $row->store_id === $variables->get('store_id', 0))
            {
                if($row->key === 'config_name')
                {
                    $variables->set('shop_name', $row->value);
                }

                if($row->key === 'config_email')
                {
                    $variables->set('shop_email', $row->value);
                }

                if($row->key === 'config_telephone')
                {
                    $variables->set('shop_phone', $row->value);
                }

                if($row->key === 'config_currency')
                {
                    $variables->set('shop_currency', $row->value);
                }
            }
        }

        $variables->set('shop_domain', HTTPS_SERVER);
    }

    public function extension(Variables $variables)
    {
        if(class_exists('BulkGate\CartSMS\HookExtension'))
        {
            $hook = new HookExtension();
            $hook->extend($this->db, $variables);
        }
    }

    public function load(Variables $variables)
    {
        $this->language($variables);
        $this->returnOrder($variables);
        $this->order($variables);
        $this->orderStatus($variables);
        $this->customer($variables);
        $this->shop($variables);
        $this->product($variables);
        $this->extension($variables);
    }
}
