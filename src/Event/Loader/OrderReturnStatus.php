<?php

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class OrderReturnStatus implements Plugin\Event\DataLoader
{
	public function __construct(private $order_return_status_model)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['return_status_id']) || (int) $variables['return_status_id'] === 0) {
			return;
		}

		$return_status = $this->order_return_status_model->getReturnStatus((int) $variables['return_status_id']);

		$variables['return_status'] = $return_status['name'];
	}
}