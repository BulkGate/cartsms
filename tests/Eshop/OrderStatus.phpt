<?php

namespace BulkGate\CartSms\Eshop\Test;

use BulkGate\CartSms\Eshop\OrderStatus;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class OrderStatusTest extends TestCase
{
	public function testOrderStatus(): void
	{
		$order_status_model = \Mockery::mock(\Opencart\Admin\Model\Localisation\OrderStatus::class);
		$order_status_model->shouldReceive('getOrderStatuses')->withNoArgs()->andReturn([
			['order_status_id' => 1, 'name' => 'Pending'],
			['order_status_id' => 2, 'name' => 'Completed'],
		]);

		$order_status_loader = new OrderStatus($order_status_model);

		Assert::same([
			1 => 'Pending',
			2 => 'Completed',
		], $order_status_loader->load());
	}
}

(new OrderStatusTest())->run();