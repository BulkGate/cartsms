<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

require_once __DIR__ . '/bootstrap.php';

//todo: toto nebude fungovat uvnitr image v automatizaci, protoze tam konfiguraky nejsou. Nejdriv se bude muset spustit kontejner a v nem install.php
//todo: lokalni vyvoj je v pohode, protoze mame prekopirovanou slozku "opencart", ktera je /var/www/html z nainstalovaneho opencartu
require_once __DIR__ . '/../opencart/config.php';
require_once __DIR__ . '/../opencart/system/startup.php';

//sytem/framework.php
$autoloader = new \Opencart\System\Engine\Autoloader();
$autoloader->register('Opencart\\' . APPLICATION, DIR_APPLICATION);
$autoloader->register('Opencart\Extension', DIR_EXTENSION);
$autoloader->register('Opencart\System', DIR_SYSTEM);

require_once DIR_SYSTEM . 'vendor.php';

// Registry
$registry = new \Opencart\System\Engine\Registry();
$registry->set('autoloader', $autoloader);

// Config
$config = new \Opencart\System\Engine\Config();
$config->addPath(DIR_CONFIG);
// Load the default config
$config->load('default');
$config->load(strtolower(APPLICATION));
$registry->set('config', $config);

// Set the default application
$config->set('application', APPLICATION);

// Factory
$registry->set('factory', new \Opencart\System\Engine\Factory($registry));

// Loader
$loader = new \Opencart\System\Engine\Loader($registry);
$registry->set('load', $loader);

// Event
$event = new \Opencart\System\Engine\Event($registry);
$registry->set('event', $event);

// Cache
//$registry->set('cache', new \Opencart\System\Library\Cache($config->get('cache_engine'), $config->get('cache_expire')));

//$db = new \Opencart\System\Library\DB($config->get('db_engine'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port'), $config->get('db_ssl_key'), $config->get('db_ssl_cert'), $config->get('db_ssl_ca'));
//$registry->set('db', $db);

return $registry;
