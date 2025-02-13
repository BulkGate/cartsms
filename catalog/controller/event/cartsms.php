<?php

namespace Opencart\Catalog\Controller\Extension\OcCartsms\Event;

use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Cartsms extends \BulkGate\CartSms\Controller
{
	public function hookAddOrder(string $route, array $params, int $id_order)
	{
		$this->runHook('order', 'new', new Plugin\Event\Variables([
			'order_id' => $id_order,
			'data' => $params,
		]));
	}

	public function hookAddOrderHistory(string $route, array $params)
	{
		[$id_order, $id_order_status] = $params;

		$this->runHook('order', 'change-status', new Plugin\Event\Variables([
			'order_id' => $id_order,
			'order_status_id' => $id_order_status,
		]));
	}

	public function hookAddCustomer(string $route, array $params, int $id_customer)
	{
		$this->runHook('customer', 'new', new Plugin\Event\Variables([
			'customer_id' => $id_customer,
			'data' => $params,
		]));
	}
}