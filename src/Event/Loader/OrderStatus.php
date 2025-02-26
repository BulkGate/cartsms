<?php

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class OrderStatus implements Plugin\Event\DataLoader
{
	public function __construct(private $order_status_model)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['order_status_id']) || (int) $variables['order_status_id'] === 0) {
			return;
		}

		$status = $this->order_status_model->getOrderStatus((int) $variables['order_status_id']);

		$variables['order_status'] = $status['name'];
	}
}