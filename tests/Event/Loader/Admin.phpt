<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use Mockery;
use Tester\{TestCase, Assert};
use BulkGate\Plugin;

require_once __DIR__ . '/../../bootstrap.php';


class AdminTest extends TestCase
{

	public function testEmployeeId(): void
	{
		$admin_model = Mockery::mock(\Opencart\Admin\Model\User\User::class);
		$admin_model->shouldReceive('getUser')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn([
			'firstname' => 'John',
			'lastname' => 'Doe',
			'email' => 'user@example.com'
		]);

		$admin_loader = new \BulkGate\CartSms\Event\Loader\Admin($admin_model);
		$admin_loader->load($variables = new Plugin\Event\Variables(['employee_id' => 1]));
		$admin_loader->load(new Plugin\Event\Variables(['employee_id' => "1"]));

		Assert::same([
			'employee_id' => 1,
			'employee_email' => 'user@example.com',
			'employee_firstname' => 'John',
			'employee_lastname' => 'Doe'
		], $variables->toArray());
	}

	public function testNoLoad(): void
	{
		$admin_model = Mockery::mock(\Opencart\Admin\Model\User\User::class);
		$admin_model->shouldNotReceive('getUser');

		$admin_loader = new \BulkGate\CartSms\Event\Loader\Admin($admin_model);
		$admin_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 2, 'lang_id' => 3]));

		Assert::same(['shop_id' => 2, 'lang_id' => 3], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new AdminTest())->run();