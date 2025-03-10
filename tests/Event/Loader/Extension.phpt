<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use Mockery;
use Tester\{TestCase, Assert};
use BulkGate\Plugin;
use BulkGate\CartSms\Event\Loader;

require_once __DIR__ . '/../../bootstrap.php';


class ExtensionTest extends TestCase
{

	/** @description Customer model should be called with integer parameter type */
	public function testHook(): void
	{
		$event = Mockery::mock(\Opencart\System\Engine\Event::class);
		$event->shouldReceive('trigger')
			->with('cartsms.hook.extension', Mockery::on(fn ($args) => $args[0] instanceof Plugin\Event\Variables && $args[1] === []))
			->once();

		$admin_loader = new Loader\Extension($event);
		$admin_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 1, 'lang_id' => 1]));

		Assert::same(['shop_id' => 1, 'lang_id' => 1], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ExtensionTest())->run();