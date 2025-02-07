<?php

namespace BulkGate\CartSms\DI;

use BulkGate\Plugin;
use BulkGate\Plugin\Settings;
use BulkGate\Plugin\Debug\Logger;
use BulkGate\Plugin\Debug\Repository\LoggerSettings;
use BulkGate\Plugin\Debug\Requirements;
use BulkGate\Plugin\IO;
use BulkGate\CartSms\Eshop;
use BulkGate\Plugin\DI\{Container, FactoryStatic, Factory as DIFactory};
use BulkGate\CartSms\Database\Connection;
use Tracy\Debugger;

class Factory implements DIFactory
{
	use FactoryStatic;

	protected static function createContainer(array $parameters = []): Container
	{
		bdump($parameters);
		['registry' => $registry] = $parameters;

		$registry->load->model('localisation/order_status');
		$registry->load->model('localisation/return_status');
		$registry->load->model('localisation/language');
		$registry->load->model('setting/store');

		$container = new Container($parameters['mode'] ?? 'strict');

		if (($parameters['debug'] ?? false) && class_exists(Debugger::class))
		{
			Debugger::$strictMode = true;
			Debugger::$maxDepth = 10;
			Debugger::$logDirectory = DIR_LOGS;
			Debugger::enable(Debugger::Development);
		}

		// Database
		$container['database.connection'] = ['factory' => Connection::class, 'parameters' => ['db' => $parameters['db']]];

		// Debug
		$container['debug.repository.logger'] = ['factory' => LoggerSettings::class, 'factory_method' => function () use ($container, $parameters): LoggerSettings
		{
			$service = new LoggerSettings($container->getByClass(Settings\Settings::class));
			$service->setup(is_int($parameters['logger_limit'] ?? null) ? $parameters['logger_limit'] : 100);
			return $service;
		}];
		$container['debug.logger'] = Logger::class;
		$container['debug.requirements'] = Requirements::class;

		// Eshop
		$container['eshop.configuration'] = ['factory' => Eshop\Configuration::class, 'factory_method' => fn () => new Eshop\Configuration($parameters['module_version'], $parameters['url'], $parameters['name'] ?? 'Store')];
		$container['eshop.synchronizer'] = Plugin\Eshop\EshopSynchronizer::class;
		$container['eshop.order_status'] = ['factory' => Eshop\OrderStatus::class, 'factory_method' => fn () => new Eshop\OrderStatus($registry->model_localisation_order_status)];
		$container['eshop.return_status'] = ['factory' => Eshop\ReturnStatus::class, 'factory_method' => fn () => new Eshop\ReturnStatus($registry->model_localisation_return_status)];
		$container['eshop.language'] = ['factory' => Eshop\Language::class, 'factory_method' => fn () => new Eshop\Language($registry->model_localisation_language)];
		$container['eshop.multistore'] = ['factory' => Eshop\MultiStore::class, 'factory_method' => fn () => new Eshop\MultiStore($container->getService('eshop.configuration'), $registry->model_setting_store)];

		// IO
		$container['io.connection.factory'] = ['factory' => IO\ConnectionFactory::class, 'factory_method' => function () use ($container): IO\ConnectionFactory
		{
			/**
			 * @var Eshop\Configuration $configuration
			 */
			$configuration = $container->getByClass(Eshop\Configuration::class);

			return new IO\ConnectionFactory($configuration->url(), $configuration->product(), $container->getByClass(Settings\Settings::class));
		}];
		$container['io.connection'] = ['factory' => IO\Connection::class, 'factory_method' => fn () => $container->getByClass(IO\ConnectionFactory::class)->create()];
		$container['io.url'] = ['factory' => IO\Url::class, 'parameters' => ['url' => $parameters['gate_url'] ?? 'https://portal.bulkgate.com']];

		// Settings
		$container['settings.repository.database'] = Settings\Repository\SettingsDatabase::class;
		$container['settings.settings'] = Settings\Settings::class;
		$container['settings.repository.synchronizer'] = Settings\Repository\SynchronizationDatabase::class;
		$container['settings.synchronizer'] = Settings\Synchronizer::class;

		return $container;
	}
}