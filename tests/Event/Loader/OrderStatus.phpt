<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use Mockery;
use Tester\{TestCase, Assert};
use BulkGate\Plugin;

require_once __DIR__ . '/../../bootstrap.php';


class OrderStatusTest extends TestCase
{

	// todo: rozdelit do Admin a Catalog testu?

	/** @description Customer model should be called with integer parameter type */
	public function testOrderStatusId(): void
	{
		$order_status_model = Mockery::mock(\Opencart\Catalog\Model\Localisation\OrderStatus::class);
		$order_status_model->shouldReceive('getOrderStatus')->with(1)->once()->ordered()->andReturn(['name' => 'Pending']);
		$order_status_model->shouldReceive('getOrderStatus')->with(1)->once()->ordered()->andReturn(['name' => 'Pending']);

		$customer_loader = new \BulkGate\CartSms\Event\Loader\OrderStatus($order_status_model);
		$customer_loader->load($variables = new Plugin\Event\Variables(['order_status_id' => 1]));
		$customer_loader->load(new Plugin\Event\Variables(['order_status_id' => "1"]));

		Assert::same(['order_status_id' => 1, 'order_status' => 'Pending'], $variables->toArray());
	}

	public function testNoLoad(): void
	{
		$order_status_model = Mockery::mock(\Opencart\Catalog\Model\Localisation\OrderStatus::class);
		$order_status_model->shouldNotReceive('getOrderStatus');

		$customer_loader = new \BulkGate\CartSms\Event\Loader\OrderStatus($order_status_model);
		$customer_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 2, 'lang_id' => 3]));
		$customer_loader->load(new Plugin\Event\Variables(['order_status_id' => 0]));

		Assert::same(['shop_id' => 2, 'lang_id' => 3], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new OrderStatusTest())->run();