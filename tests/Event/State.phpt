<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Test;

use BulkGate\CartSms\Event\State;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class StateTest extends TestCase
{
	public function testState(): void
	{
		$number = 0;
		$state = new State(function() use (&$number) {
			$number++;

			return $number;
		});

		Assert::equal(null, $state->getInitial());
		Assert::equal(null, $state->getActual());
		Assert::equal(null, $state->getExpected());
		Assert::false($state->isChanged());
		Assert::false($state->shouldRunHook());
		Assert::true($state->isExpected());

		$state
			->captureInitial()
			->captureActual()
			->setExpected(2);

		Assert::equal(1, $state->getInitial());
		Assert::equal(2, $state->getActual());
		Assert::equal(2, $state->getExpected());
		Assert::true($state->isChanged());
		Assert::true($state->shouldRunHook());
		Assert::true($state->isExpected());

	}
}

(new StateTest())->run();