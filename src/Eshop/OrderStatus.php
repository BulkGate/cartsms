<?php declare(strict_types=1);

namespace BulkGate\CartSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;

class OrderStatus implements Plugin\Eshop\OrderStatus
{
	use Plugin\Strict;

	/** @param \Opencart\Admin\Model\Localisation\OrderStatus $order_status */
	public function __construct(private $order_status)
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