<?php

require_once '/var/www/html/admin/config.php';
require_once '/var/www/html/system/startup.php';

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

define('IS_4_1_x', class_exists('Opencart\System\Engine\Factory'));

// Factory - opencart 4.1.x
if (IS_4_1_x) {
	$registry->set('factory', new \Opencart\System\Engine\Factory($registry));
}

// Loader
$loader = new \Opencart\System\Engine\Loader($registry);
$registry->set('load', $loader);

// Event
$event = new \Opencart\System\Engine\Event($registry);
$registry->set('event', $event);

// Cache
//$registry->set('cache', new \Opencart\System\Library\Cache($config->get('cache_engine'), $config->get('cache_expire')));

$db = new \Opencart\System\Library\DB($config->get('db_engine'), $config->get('db_hostname'), $config->get('db_username'), $config->get('db_password'), $config->get('db_database'), $config->get('db_port'), $config->get('db_ssl_key'), $config->get('db_ssl_cert'), $config->get('db_ssl_ca'));
$registry->set('db', $db);


$response = new \Opencart\System\Library\Response();
$response->addHeader('Content-Type: text/plain; charset=utf-8');
$registry->set('response', $response);

$registry->load->model('setting/extension');

/** @var \Opencart\Admin\Model\Setting\Extension $install*/
$install = $registry->get('model_setting_extension');
$install_info = json_decode(file_get_contents('/var/www/html/extension/oc_cartsms/install.json'), true);

$id = $install->addInstall([
	'extension_id'          => 0,
	'extension_download_id' => 0,
	'name'                  => $install_info['name'],
	'code'              	=> 'oc_cartsms',
	'description'         	=> $install_info['description'],
	'version'               => $install_info['version'],
	'author'                => $install_info['author'],
	'link'                  => $install_info['link']
]);

$install->addPath($id, 'oc_cartsms/admin/controller/module/cartsms.php');
$install->editStatus($id, true);

$response->setOutput('Extension ' . $install_info['name'] . ' installed successfully.');

$response->output();