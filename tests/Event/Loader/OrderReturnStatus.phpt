<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use Mockery;
use Tester\{TestCase, Assert};
use BulkGate\Plugin;

require_once __DIR__ . '/../../bootstrap.php';


class OrderReturnStatusTest extends TestCase
{

	// todo: rozdelit do Admin a Catalog testu?

	/** @description Customer model should be called with integer parameter type */
	public function testOrderStatusId(): void
	{
		$order_return_status_model = Mockery::mock(\Opencart\Admin\Model\Localisation\ReturnStatus::class);
		$order_return_status_model->shouldReceive('getReturnStatus')->with(1)->once()->ordered()->andReturn(['name' => 'Pending']);
		$order_return_status_model->shouldReceive('getReturnStatus')->with(1)->once()->ordered()->andReturn(['name' => 'Pending']);

		$return_status_loader = new \BulkGate\CartSms\Event\Loader\OrderReturnStatus($order_return_status_model);
		$return_status_loader->load($variables = new Plugin\Event\Variables(['return_status_id' => 1]));
		$return_status_loader->load(new Plugin\Event\Variables(['return_status_id' => "1"]));

		Assert::same(['return_status_id' => 1, 'return_status' => 'Pending'], $variables->toArray());
	}

	public function testNoLoad(): void
	{
		$order_return_status_model = Mockery::mock(\Opencart\Admin\Model\Localisation\ReturnStatus::class);
		$order_return_status_model->shouldNotReceive('getReturnStatus');

		$return_status_loader = new \BulkGate\CartSms\Event\Loader\OrderReturnStatus($order_return_status_model);
		$return_status_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 2, 'lang_id' => 3]));
		$return_status_loader->load(new Plugin\Event\Variables(['return_status_id' => 0]));

		Assert::same(['shop_id' => 2, 'lang_id' => 3], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new OrderReturnStatusTest())->run();