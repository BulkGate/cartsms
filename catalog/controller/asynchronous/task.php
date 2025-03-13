<?php

namespace Opencart\Catalog\Controller\Extension\OcCartsms\Asynchronous;

use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Task extends \BulkGate\CartSms\Controller
{
	public function index()
	{
		$settings = $this->di_container->getByClass(Plugin\Settings\Settings::class);

		if (in_array($settings->load('main:dispatcher'), [Plugin\Event\Dispatcher::Cron, Plugin\Event\Dispatcher::Asset])) {
			$count = $this->di_container->getByClass(Plugin\Event\Asynchronous::class)->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));

			echo "// Asynchronous task consumer has processed $count tasks";
		} else {
			echo '// Asynchronous task consumer is disabled';
		}
	}
}