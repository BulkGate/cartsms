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
		bdump($parameters);
		['registry' => $registry] = $parameters;
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
		$container['debug.logger'] = Plugin\Debug\Logger::class;
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
		$container['event.loader.extension'] = ['factory' => Event\Loader\Extension::class, 'auto_wiring' => false];
		$container['event.loader.shop'] = ['factory' => Event\Loader\Shop::class, 'auto_wiring' => false];
		$container['event.loader.order'] = ['factory' => Event\Loader\Order::class, 'auto_wiring' => false];
		$container['event.loader.order_status'] = ['factory' => Event\Loader\OrderStatus::class, 'auto_wiring' => false];
		$container['event.loader.customer'] = ['factory' => Event\Loader\Customer::class, 'auto_wiring' => false];
		$container['event.loader.product'] = ['factory' => Event\Loader\Product::class, 'auto_wiring' => false];
		$container['event.loader.post'] = ['factory' => Event\Loader\Post::class, 'auto_wiring' => false];

		// Event
		$container['event.hook'] = ['factory' => Plugin\Event\Hook::class, 'parameters' => ['version' => $parameters['api_version'] ?? '1.0']];
		$container['event.asynchronous.repository'] = Plugin\Event\Repository\AsynchronousDatabase::class;
		$container['event.asynchronous'] = Plugin\Event\Asynchronous::class;
		$container['event.loader'] = ['factory' => Plugin\Event\Loader::class, 'factory_method' => fn () => new Plugin\Event\Loader([
			$container->getByClass(Event\Loader\Order::class),
			$container->getByClass(Event\Loader\OrderStatus::class),
			$container->getByClass(Event\Loader\Customer::class),
			$container->getByClass(Event\Loader\Shop::class),
			$container->getByClass(Event\Loader\Product::class),
			$container->getByClass(Event\Loader\Post::class),
			$container->getByClass(Event\Loader\Extension::class),
		])];
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