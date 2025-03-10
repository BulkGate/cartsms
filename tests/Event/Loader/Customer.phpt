<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use Mockery;
use Tester\{TestCase, Assert};
use BulkGate\Plugin;

require_once __DIR__ . '/../../bootstrap.php';


class CustomerTest extends TestCase
{

	/** @description Customer model should be called with integer parameter type */
	public function testCustomerId(): void
	{
		$customer_model = Mockery::mock(\Opencart\Catalog\Model\Account\Customer::class);
		$customer_model->shouldReceive('getCustomer')->with(1)->once()->ordered()->andReturn(['store_id' => 1, 'language_id' => 1, 'telephone' => '777888999', 'email' => 'user@example.com']);
		$customer_model->shouldReceive('getCustomer')->with(1)->once()->ordered()->andReturn(['store_id' => 1, 'language_id' => 1, 'telephone' => '777888999', 'email' => 'user@example.com']);

		$customer_loader = new \BulkGate\CartSms\Event\Loader\Customer($customer_model);
		$customer_loader->load($variables = new Plugin\Event\Variables(['customer_id' => 1]));
		$customer_loader->load(new Plugin\Event\Variables(['customer_id' => "1"]));

		Assert::same(['customer_id' => 1, 'shop_id' => 1, 'lang_id' => 1, 'customer_mobile' => '777888999', 'customer_email' => 'user@example.com'], $variables->toArray());
	}

	public function testOverwrite()
	{
		$customer_model = Mockery::mock(\Opencart\Catalog\Model\Account\Customer::class);
		$customer_model->shouldReceive('getCustomer')->with(1)->once()->ordered()->andReturn(['store_id' => 1, 'language_id' => 1, 'telephone' => '777888999', 'email' => 'user@example.com']);

		$customer_loader = new \BulkGate\CartSms\Event\Loader\Customer($customer_model);
		$customer_loader->load($variables = new Plugin\Event\Variables(['customer_id' => 1, 'shop_id' => 2, 'lang_id' => 3, 'customer_mobile' => '111', 'customer_email' => 'test@opencart.com']));

		Assert::same(['customer_id' => 1, 'shop_id' => 2, 'lang_id' => 3, 'customer_mobile' => '111', 'customer_email' => 'test@opencart.com'], $variables->toArray());
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