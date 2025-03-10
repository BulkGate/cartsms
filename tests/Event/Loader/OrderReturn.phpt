<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use BulkGate\Plugin;
use Mockery;
use Tester\{Assert, TestCase};

require_once __DIR__ . '/../../bootstrap.php';


class OrderReturnTest extends TestCase
{

	// todo: rozdelit do Admin a Catalog testu?

	public function testReturnId(): void
	{
		$return_model = Mockery::mock(\Opencart\Catalog\Model\Account\Returns::class);
		$return_model->shouldReceive('getReturn')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn(['store_id' => 1, 'language_id' => 1, 'order_id' => 1, 'customer_id' => 1, 'return_status_id' => 1, 'quantity' => 1, 'product' => 'Product', 'product_id' => 1, 'return_status' => 'status', 'comment' => 'test', 'date_added' => '2025-03-10 11:00:00', 'action' => 'action', 'return_action_id' => 1, 'return_reason_id' => 1, 'reason' => 'reason']);

		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);
		$formatter->shouldReceive('format')->with('date', '2025-03-10 11:00:00')->andReturn('date');

		$order_return_loader = new \BulkGate\CartSms\Event\Loader\OrderReturn($return_model, $formatter);
		$order_return_loader->load($variables = new Plugin\Event\Variables(['return_id' => 1]));
		$order_return_loader->load(new Plugin\Event\Variables(['return_id' => "1"]));

		Assert::same([
			'return_id' => 1,
			'lang_id' => 1,
			'order_id' => 1,
			'customer_id' => 1,
			'return_status_id' => 1,
			'return_customer_message' => 'test',
			'return_date' => 'date',
			'return_action' => 'action',
			'return_action_id' => 1,
			'return_reason_id' => 1,
			'return_reason' => 'reason',
			'return_status' => 'status',
			'return_products1' => '1x Product 1',
			'return_products2' => '1x Product',
			'return_products3' => '1x (1) Product',
			'return_products4' => '1x 1'
		], $variables->toArray());
	}

	public function testOverwrite()
	{
		$return_model = Mockery::mock(\Opencart\Catalog\Model\Account\Returns::class);
		$return_model->shouldReceive('getReturn')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn(['store_id' => 1, 'language_id' => 1, 'order_id' => 1, 'customer_id' => 1, 'return_status_id' => 1, 'quantity' => 1, 'product' => 'Product', 'product_id' => 1, 'return_status' => 'status', 'comment' => 'test', 'date_added' => '2025-03-10 11:00:00', 'action' => 'action', 'return_action_id' => 1, 'return_reason_id' => 1, 'reason' => 'reason']);

		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);
		$formatter->shouldReceive('format')->with('date', '2025-03-10 11:00:00')->andReturn('date');

		$order_return_loader = new \BulkGate\CartSms\Event\Loader\OrderReturn($return_model, $formatter);
		$order_return_loader->load($variables = new Plugin\Event\Variables(['return_id' => 1, 'lang_id' => 2, 'order_id' => 3, 'customer_id' => 4, 'return_status_id' => 5]));

		Assert::same(1, $variables['return_id']);
		Assert::same(2, $variables['lang_id']);
		Assert::same(3, $variables['order_id']);
		Assert::same(4, $variables['customer_id']);
		Assert::same(5, $variables['return_status_id']);
	}

	public function testNoLoad(): void
	{
		$return_model = Mockery::mock(\Opencart\Catalog\Model\Account\Returns::class);
		$return_model->shouldNotReceive('getReturn');

		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);

		$order_return_loader = new \BulkGate\CartSms\Event\Loader\OrderReturn($return_model, $formatter);
		$order_return_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 2, 'lang_id' => 3]));

		Assert::same(['shop_id' => 2, 'lang_id' => 3], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new OrderReturnTest())->run();