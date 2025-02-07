<?php

namespace BulkGate\CartSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;
use BulkGate\Plugin\Strict;

class OrderStatus implements Plugin\Eshop\OrderStatus
{
	use Strict;

	public function __construct(private readonly \Opencart\Admin\Model\Localisation\OrderStatus | \Opencart\System\Engine\Proxy $order_status)
	{
	}

	public function load(): array
	{
		$status_list = [];

		foreach ($this->order_status->getOrderStatuses() as $status)
		{
			$status_list[$status['order_status_id']] = $status['name'];
		}

		return $status_list;
	}
}