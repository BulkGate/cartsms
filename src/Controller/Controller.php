<?php

namespace BulkGate\CartSms;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

use BulkGate\CartSms\DI\Factory;
use BulkGate\Plugin\DI\Container;
use BulkGate\Plugin\Event\Dispatcher;

class Controller extends \Opencart\System\Engine\Controller
{
	protected Container $di_container;

	public function __construct(...$args)
	{
		parent::__construct(...$args);

		//$this->load->model('setting/setting');
		//$this->load->model('setting/store');
		//$config = $this->model_setting_setting->getSetting('config');

		//bdump($this->model_setting_setting->getSetting('config', 1));

		Factory::setup(fn () => [
			'registry' => $this->registry,
			'db' => $this->db,
			'debug' => true,
			'dispatcher' => Dispatcher::Asset,
			'api_version' => '1.0',
			'module_version' => '4.0',
			'name' => $this->model_setting_setting->getValue('config_name'),
			'url' => HTTP_SERVER,
			'settings_defaults' => [
				"main:dispatcher" => 'asset',
    			"main:synchronization" => 'all',
				"main:language" => 'auto',
				"main:language_mutation" => false,
    			"main:delete_db" => false,
    			"main:address_preference" => 'delivery',
    			"main:marketing_message_opt_in_enabled" => false,
				"main:marketing_message_opt_in_label" => '',
    			"main:marketing_message_opt_in_default" => false,
    			"main:marketing_message_opt_in_url" => ''
			]
		]);

		$this->di_container = Factory::get();
	}
}