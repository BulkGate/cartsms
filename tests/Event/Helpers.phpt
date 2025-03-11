<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Test;

use BulkGate\CartSms\Event\Helpers;
use BulkGate\Plugin;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class HelpersTest extends TestCase
{
	public function testPriorityValues(): void
	{
		$value_array = [
			'key1' => 'value1',
			'key2' => null,
		];
		$value_array_access = new Plugin\Event\Variables($value_array);

		Assert::same('value1', Helpers::priorityValues(['key1', 'key2'], $value_array));
		Assert::same('value1', Helpers::priorityValues(['key2', 'key1'], $value_array));
		Assert::same('value1', Helpers::priorityValues(['key3', 'key1'], $value_array));
		Assert::same('default_value', Helpers::priorityValues(['key2', 'key3'], $value_array, 'default_value'));

		Assert::same('value1', Helpers::priorityValues(['key1', 'key2'], $value_array_access));
		Assert::same('value1', Helpers::priorityValues(['key2', 'key1'], $value_array_access));
		Assert::same('value1', Helpers::priorityValues(['key3', 'key1'], $value_array_access));
		Assert::same('default_value', Helpers::priorityValues(['key2', 'key3'], $value_array_access, 'default_value'));
	}
}

(new HelpersTest())->run();