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
		$customer_data = [
			'store_id' => 1,
			'language_id' => 1,
			'telephone' => '777888999',
			'email' => 'user@example.com'
		];
		$customer_model = Mockery::mock(\Opencart\Catalog\Model\Account\Customer::class);
		$customer_model->shouldReceive('getCustomer')->with(1)->once()->ordered()->andReturn($customer_data);
		$customer_model->shouldReceive('getCustomer')->with(1)->once()->ordered()->andReturn($customer_data);

		$customer_loader = new \BulkGate\CartSms\Event\Loader\Customer($customer_model);
		$customer_loader->load($variables = new Plugin\Event\Variables(['customer_id' => 1]));
		$customer_loader->load(new Plugin\Event\Variables(['customer_id' => "1"]));

		Assert::same(['customer_id' => 1, 'shop_id' => 1, 'lang_id' => 1, 'customer_mobile' => '777888999', 'customer_email' => 'user@example.com'], $variables->toArray());
	}
}

(new CustomerTest())->run();