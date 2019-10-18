<?php
namespace BulkGate\CartSms;

use BulkGate\Extensions\Database\Result;
use BulkGate\Extensions\Json;
use BulkGate\Extensions\IModule;
use BulkGate\Extensions\ISettings;
use BulkGate\Extensions\Strict;
use BulkGate\Extensions\Escape;
use CartSms\Init;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class CartSMS extends Strict implements IModule
{
    const PRODUCT = 'oc';

    private $info = array(
        'store' => 'OpenCart',
        'store_version' => '3.0.0.0+',
        'name' => Init::NAME,
        'url' => 'http://www.cart-sms.com',
        'developer' => Init::AUTHOR,
        'version' => Init::VERSION,
        'developer_url' => 'http://www.topefekt.com/',
        'description' => 'CartSMS module extends your OpenCart store capabilities and creates new opportunities for your business. You can promote your products and sales via personalized bulk SMS. Make your customers happy by notifying them about order status change via SMS notifications. Receive an SMS whenever a new order is placed, a product is out of stock, and much more.',
    );

    /** @var ISettings */
    public $settings;

    /** @var Database */
    public $db;

    /** @var array */
    private $plugin_data = array();

    public function __construct(ISettings $settings, Database $db)
    {
        $this->settings = $settings;
        $this->db = $db;
    }

    public function getUrl($path = '')
    {
        if(defined('BULKGATE_DEBUG'))
        {
            return Escape::url(BULKGATE_DEBUG.$path);
        }
        else
        {
            return Escape::url('https://portal.bulkgate.com'.$path);
        }
    }

    public function statusLoad()
    {
        $actual = array();
        $status_list = (array) $this->settings->load(':order_status_list', null);

        $result = $this->db->execute(
            $this->db->prepare(
                "SELECT `order_status_id`, `name` FROM `{$this->db->table('order_status')}` WHERE `language_id` = %s ORDER BY `order_status_id` ASC",
                array($this->getLanguage())
            )
        );

        foreach ($result as $status)
        {
            $actual[$status->order_status_id] = $status->name;
        }

        if($status_list !== $actual)
        {
            $this->settings->set(':order_status_list', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }

    public function returnStatusLoad()
    {
        $actual = array();
        $status_list = (array) $this->settings->load(':return_status_list', null);

        $result = $this->db->execute(
            $this->db->prepare(
                "SELECT `return_status_id`, `name` FROM `{$this->db->table('return_status')}` WHERE `language_id` = %s ORDER BY `return_status_id` ASC",
                array($this->getLanguage())
            )
        );

        foreach ($result as $status)
        {
            $actual[$status->return_status_id] = $status->name;
        }

        if($status_list !== $actual)
        {
            $this->settings->set(':return_status_list', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }

    public function storeLoad()
    {
        $actual = array();

        $row = $this->db->execute("SELECT `value` FROM `{$this->db->table('setting')}` WHERE `key` = 'config_name' ORDER BY `store_id` ASC LIMIT 1")->getRow();

        if($row)
        {
            if(isset($row->value))
            {
                $actual[0] = $row->value;
            }
        }

        /** @var Result $stores */
        $result = $this->db->execute("SELECT `store_id`, `name` FROM `{$this->db->table('store')}`");

        foreach ($result as $store)
        {
            $actual[$store->store_id] = $store->name;
        }

        $stores = (array) $this->settings->load(':stores', null);

        if($stores !== $actual)
        {
            $this->settings->set(':stores', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }

    public function languageLoad()
    {
        if((bool) $this->settings->load('main:language_mutation', false))
        {
            $languages = (array) $this->settings->load(':languages', null);
            $actual = array();

            $result = $this->db->execute("SELECT `language_id`, `name`, `code` FROM `{$this->db->table('language')}` WHERE `status` = 1");

            foreach($result as $language)
            {
                $actual[$language->language_id] = $language->name.' ('.$language->code.')';
            }

            if($languages !== $actual)
            {
                $this->settings->set(':languages', Json::encode($actual), array('type' => 'json'));
                return true;
            }
            return false;
        }
        else
        {
            $this->settings->set(':languages', Json::encode(array('default' => 'Default')), array('type' => 'json'));
            return true;
        }
    }

    /** @var int|null */
    private $language_id = null;

    private function getLanguage()
    {
        if($this->language_id === null)
        {
            $row = $this->db->execute("SELECT `value` FROM `{$this->db->table('setting')}` WHERE `key` = 'config_admin_language' LIMIT 1")->getRow();

            if($row)
            {
                if(isset($row->value))
                {
                    $result_language = $this->db->execute($this->db->prepare("SELECT `language_id` FROM `{$this->db->table('language')}` WHERE `code` = %s AND `status` = 1 LIMIT 1", array($row->value)));

                    $row = $result_language->getRow();

                    if(isset($row->language_id))
                    {
                        $this->language_id = $row->language_id;
                    }
                }
            }

            if($this->language_id === null)
            {
                $row = $this->db->execute("SELECT `language_id` FROM `{$this->db->table('language')}` WHERE `status` = 1 LIMIT 1")->getRow();

                if($row && isset($row->language_id))
                {
                    $this->language_id = $row->language_id;
                }
                else
                {
                    $this->language_id = -1;
                }
            }
        }
        return $this->language_id;
    }

    public function product()
    {
        return self::PRODUCT;
    }

    public function url()
    {
        return HTTPS_SERVER;
    }

    public function info($key = null)
    {
        if(empty($this->plugin_data))
        {
            $this->plugin_data = array_merge(
                array(
                    'application_id' => $this->settings->load('static:application_id', -1),
                    'application_product' => $this->product(),
                    'delete_db' => $this->settings->load('main:delete_db', 0),
                    'language_mutation' => $this->settings->load('main:language_mutation', 0)
                ),
                $this->info
            );
        }
        if($key === null)
        {
            return $this->plugin_data;
        }
        return isset($this->plugin_data[$key]) ? $this->plugin_data[$key] : null;
    }
}
