<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use BulkGate\Plugin;
use Mockery;
use Tester\{Assert, TestCase};

require_once __DIR__ . '/../../bootstrap.php';


class CustomerTest extends TestCase
{

	public function testCustomerId(): void
	{
		$customer_model = Mockery::mock(\Opencart\Catalog\Model\Account\Customer::class);
		$customer_model->shouldReceive('getCustomer')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn([
			'store_id' => 1,
			'language_id' => 1,
			'telephone' => '777888999',
			'email' => 'user@example.com'
		]);

		$customer_loader = new \BulkGate\CartSms\Event\Loader\Customer($customer_model);
		$customer_loader->load($variables = new Plugin\Event\Variables(['customer_id' => 1]));
		$customer_loader->load(new Plugin\Event\Variables(['customer_id' => "1"]));

		Assert::same([
			'customer_id' => 1,
			'shop_id' => 1,
			'lang_id' => 1,
			'customer_mobile' => '777888999',
			'customer_email' => 'user@example.com'
		], $variables->toArray());
	}

	public function testOverwrite()
	{
		$customer_model = Mockery::mock(\Opencart\Catalog\Model\Account\Customer::class);
		$customer_model->shouldReceive('getCustomer')->with(1)->once()->andReturn([
			'store_id' => 1,
			'language_id' => 1,
			'telephone' => '777888999',
			'email' => 'user@example.com'
		]);

		$customer_loader = new \BulkGate\CartSms\Event\Loader\Customer($customer_model);
		$customer_loader->load($variables = new Plugin\Event\Variables([
			'customer_id' => 1,
			'shop_id' => 2,
			'lang_id' => 3,
			'customer_mobile' => '111',
			'customer_email' => 'test@opencart.com'
		]));

		Assert::same(1, $variables['customer_id']);
		Assert::same(2, $variables['shop_id']);
		Assert::same(3, $variables['lang_id']);
	}

	public function testNoLoad(): void
	{
		$customer_model = Mockery::mock(\Opencart\Catalog\Model\Account\Customer::class);
		$customer_model->shouldNotReceive('getCustomer');

		$customer_loader = new \BulkGate\CartSms\Event\Loader\Customer($customer_model);
		$customer_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 2, 'lang_id' => 3]));

		Assert::same(['shop_id' => 2, 'lang_id' => 3], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new CustomerTest())->run();