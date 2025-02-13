<?php

namespace BulkGate\CartSms;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

use BulkGate\Plugin;
use BulkGate\CartSms\DI\Factory;

class Controller extends \Opencart\System\Engine\Controller
{
	protected Plugin\DI\Container $di_container;

	public function __construct(...$args)
	{
		parent::__construct(...$args);

		Factory::setup(fn () => [
			'registry' => $this->registry,
			'db' => $this->db,
			'debug' => false,
			'dispatcher' => Plugin\Event\Dispatcher::Asset,
			'api_version' => '1.0',
			'module_version' => '4.0',
			'name' => $this->model_setting_setting->getValue('config_name'),
			'url' => '',// HTTP_CATALOG,
			'gate_url' => 'http://192.168.16.1',
			'default_settings' => [
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

	protected function runHook(string $category, string $endpoint, Plugin\Event\Variables $variables, array $parameters = [], ?callable $success_callback = null): void
	{
		$dispatcher = $this->di_container->getByClass(Plugin\Event\Dispatcher::class);

		$dispatcher->dispatch($category, $endpoint, $variables, $parameters, $success_callback);
	}
}