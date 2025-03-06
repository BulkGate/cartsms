<?php

namespace BulkGate\CartSms\DI;

use BulkGate\Plugin;
use BulkGate\CartSms\Ajax;
use BulkGate\CartSms\Eshop;
use BulkGate\CartSms\Event;
use BulkGate\CartSms\Database;
use Tracy\Debugger;

class Factory implements Plugin\DI\Factory
{
	use Plugin\DI\FactoryStatic;

	protected static function createContainer(array $parameters = []): Plugin\DI\Container
	{
		['registry' => $registry] = $parameters;
		//bdump($parameters);
		//bdump($registry->has('user'), 'HAS_USER');

		$iso = $registry->language->get('code');

		$container = new Plugin\DI\Container($parameters['mode'] ?? 'strict');

		if (($parameters['debug'] ?? false) && class_exists(Debugger::class))
		{
			Debugger::$strictMode = true;
			Debugger::$maxDepth = 10;
			Debugger::$logDirectory = DIR_LOGS;
			Debugger::enable(Debugger::Development);
		}

		// Database
		$container['database.connection'] = ['factory' => Database\Connection::class, 'parameters' => ['db' => $parameters['db']]];

		// Debug
		$container['debug.repository.logger'] = ['factory' => Plugin\Debug\Repository\LoggerSettings::class, 'factory_method' => function () use ($container, $parameters): Plugin\Debug\Repository\LoggerSettings
		{
			$service = new Plugin\Debug\Repository\LoggerSettings($container->getService('settings.settings'));
			$service->setup(is_int($parameters['logger_limit'] ?? null) ? $parameters['logger_limit'] : 100);
			return $service;
		}];
		$container['debug.logger'] = ['factory' => Plugin\Debug\Logger::class, 'parameters' => ['platform_version' => $parameters['platform_version'], 'module_version' => $parameters['module_version']], 'factory_method' => fn (...$args) => new Plugin\Debug\Logger(...$args)];

		$container['debug.requirements'] = Plugin\Debug\Requirements::class;

		// Ajax
		$container['ajax.authenticate'] = Ajax\Authenticate::class;
		$container['ajax.plugin_settings'] = Ajax\PluginSettings::class;

		// Eshop
		$container['eshop.configuration'] = ['factory' => Eshop\Configuration::class, 'factory_method' => fn () => new Eshop\Configuration($parameters['module_version'], $parameters['url'], $parameters['name'] ?? 'Store')];
		$container['eshop.synchronizer'] = Plugin\Eshop\EshopSynchronizer::class;
		$container['eshop.order_status'] = ['factory' => Eshop\OrderStatus::class, 'factory_method' => function() use ($registry)
		{
			$registry->load->model('localisation/order_status');

			return new Eshop\OrderStatus($registry->model_localisation_order_status);
		}];
		$container['eshop.return_status'] = ['factory' => Eshop\ReturnStatus::class, 'factory_method' => function() use ($registry)
		{
			$registry->load->model('localisation/return_status');

			return new Eshop\ReturnStatus($registry->model_localisation_return_status);
		}];
		$container['eshop.language'] = ['factory' => Eshop\Language::class, 'factory_method' => function() use ($registry)
		{
			$registry->load->model('localisation/language');

			return new Eshop\Language($registry->model_localisation_language);
		}];
		$container['eshop.multistore'] = ['factory' => Eshop\MultiStore::class, 'factory_method' => function() use ($registry, $container)
		{
			$registry->load->model('setting/store');

			return new Eshop\MultiStore($container->getService('eshop.configuration'), $registry->model_setting_store);
		}];

		// Event loaders
		$container['event.loader.extension'] = ['factory' => Event\Loader\Extension::class, 'auto_wiring' => false, 'parameters' => ['event' => $registry->event]/*, 'factory_method' => fn () => new Event\Loader\Extension($registry->event)*/];
		$container['event.loader.shop'] = ['factory' => Event\Loader\Shop::class, 'auto_wiring' => false, 'factory_method' => function() use ($registry)
		{
			$registry->load->model('setting/setting');
			$registry->load->model('localisation/language');

			return new Event\Loader\Shop($registry->model_setting_setting, $registry->model_localisation_language, $registry->request);
		}];
		$container['event.loader.order'] = ['factory' => Event\Loader\Order::class, 'auto_wiring' => false, 'factory_method' => function() use ($registry, $container)
		{
			if ($registry->has('user')) //admin
			{
				$registry->load->model('sale/order');
				$order_model = $registry->model_sale_order;
			}
			else
			{
				$registry->load->model('checkout/order');
				$order_model = $registry->model_checkout_order;
			}

			return new Event\Loader\Order($order_model, $container->getService('localization.formatter'));
		}];
		$container['event.loader.order_return'] = ['factory' => Event\Loader\OrderReturn::class, 'auto_wiring' => false, 'factory_method' => function() use ($registry, $container)
		{
			if ($registry->has('user')) //admin
			{
				$registry->load->model('sale/returns');
				$return_model = $registry->model_sale_returns;
			}
			else
			{
				$registry->load->model('account/returns');
				$return_model = $registry->model_account_returns;
			}

			return new Event\Loader\OrderReturn($return_model, $container->getService('localization.formatter'));
		}];
		$container['event.loader.order_return_status'] = ['factory' => Event\Loader\OrderReturnStatus::class, 'auto_wiring' => false, 'factory_method' => function() use ($registry)
		{
			$registry->load->model('localisation/return_status');

			return new Event\Loader\OrderReturnStatus($registry->model_localisation_return_status);
		}];
		$container['event.loader.order_status'] = ['factory' => Event\Loader\OrderStatus::class, 'auto_wiring' => false, 'factory_method' => function() use ($registry)
		{
			$registry->load->model('localisation/order_status');

			return new Event\Loader\OrderStatus($registry->model_localisation_order_status);
		}];
		$container['event.loader.customer'] = ['factory' => Event\Loader\Customer::class, 'auto_wiring' => false, 'factory_method' => function() use ($registry)
		{
			if ($registry->has('user')) //admin
			{
				$registry->load->model('customer/customer');
				$customer_model = $registry->model_customer_customer;
			}
			else
			{
				$registry->load->model('account/customer');
				$registry->load->model('account/address');

				$customer_model = new class ($registry->model_account_customer, $registry->model_account_address) {
					private $customer_id;

					public function __construct(private $customer_model, private $address_model)
					{
					}

					public function getCustomer(int $customer_id): array
					{
						$this->customer_id = $customer_id;
						return $this->customer_model->getCustomer($customer_id);
					}

					public function getAddress(int $address_id): array
					{
						return $this->address_model->getAddress($this->customer_id, $address_id);
					}
				};
			}

			return new Event\Loader\Customer($customer_model);
		}];
		$container['event.loader.admin'] = ['factory' => Event\Loader\Admin::class, 'auto_wiring' => false, 'factory_method' => function() use ($registry)
		{
			$registry->load->model('user/user');

			return new Event\Loader\Admin($registry->model_user_user);
		}];
		$container['event.loader.product'] = ['factory' => Event\Loader\Product::class, 'auto_wiring' => false, 'factory_method' => function() use ($registry, $container)
		{
			$registry->load->model('catalog/product');
			$registry->load->model('catalog/manufacturer');

			return new Event\Loader\Product($registry->model_catalog_product, $registry->model_catalog_manufacturer, $container->getService('localization.formatter'));
		}];

		// Event
		$container['event.hook'] = ['factory' => Plugin\Event\Hook::class, 'parameters' => ['version' => $parameters['api_version'] ?? '1.0']];
		$container['event.asynchronous.repository'] = Plugin\Event\Repository\AsynchronousDatabase::class;
		$container['event.asynchronous'] = Plugin\Event\Asynchronous::class;

		$container['event.loader'] = ['factory' => Plugin\Event\Loader::class, 'factory_method' => function () use ($registry, $container)
		{
			$loaders = [
				$container->getByClass(Event\Loader\OrderReturn::class),
				$container->getByClass(Event\Loader\Order::class),
				$container->getByClass(Event\Loader\OrderStatus::class),
				$container->getByClass(Event\Loader\Customer::class),
				$container->getByClass(Event\Loader\Product::class),
				$container->getByClass(Event\Loader\Shop::class),
			];

			if ($registry->has('user')) //admin
			{
				$loaders[] = $container->getByClass(Event\Loader\OrderReturnStatus::class);
				$loaders[] = $container->getByClass(Event\Loader\Admin::class);
			}

			$loaders[] = $container->getByClass(Event\Loader\Extension::class);

			return new Plugin\Event\Loader($loaders);
		}];
		$container['event.dispatcher'] = Plugin\Event\Dispatcher::class;

		// IO
		$container['io.connection.factory'] = ['factory' => Plugin\IO\ConnectionFactory::class, 'factory_method' => function () use ($container): Plugin\IO\ConnectionFactory
		{
			/**
			 * @var Eshop\Configuration $configuration
			 */
			$configuration = $container->getService('eshop.configuration');

			return new Plugin\IO\ConnectionFactory($configuration->url(), $configuration->product(), $container->getService('settings.settings'));
		}];
		$container['io.connection'] = ['factory' => Plugin\IO\Connection::class, 'factory_method' => fn () => $container->getByClass(Plugin\IO\ConnectionFactory::class)->create()];
		$container['io.url'] = ['factory' => Plugin\IO\Url::class, 'parameters' => ['url' => $parameters['gate_url'] ?? 'https://portal.bulkgate.com']];

		// Localization
		$container['localization.language'] = ['factory' => Plugin\Localization\LanguageSettings::class, 'parameters' => ['iso' => $iso]];
		$container['localization.translator'] = Plugin\Localization\TranslatorSettings::class;
		$container['localization.formatter'] = extension_loaded('intl') ? ['factory' => Plugin\Localization\FormatterIntl::class, 'factory_method' => fn () => new Plugin\Localization\FormatterIntl($iso)] : Plugin\Localization\FormatterBasic::class;

		// Settings
		$container['settings.repository.database'] = Plugin\Settings\Repository\SettingsDatabase::class;
		$container['settings.settings'] = ['factory' => Plugin\Settings\Settings::class, 'factory_method' => function () use ($container, $parameters): Plugin\Settings\Settings
		{
			$settings = new Plugin\Settings\Settings($container->getByClass(Plugin\Settings\Repository\SettingsDatabase::class));
			$settings->setDefaultSettings($parameters['default_settings']);

			return $settings;
		}];
		$container['settings.repository.synchronizer'] = Plugin\Settings\Repository\SynchronizationDatabase::class;
		$container['settings.synchronizer'] = Plugin\Settings\Synchronizer::class;

		// User
		$container['user.sign'] = Plugin\User\Sign::class;

		return $container;
	}
}