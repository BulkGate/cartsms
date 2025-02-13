<?php

namespace Opencart\Admin\Controller\Extension\OcCartsms\Event;

use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Cartsms extends \BulkGate\CartSms\Controller
{
	public function hookAddCustomer(string $route, array $params, int $id_customer)
	{
		$this->runHook('customer', 'new', new Plugin\Event\Variables([
			'customer_id' => $id_customer,
			'data' => $params,
		]));
	}
}