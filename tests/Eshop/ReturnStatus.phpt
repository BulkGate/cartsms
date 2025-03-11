<?php

namespace BulkGate\CartSms\Eshop\Test;

use BulkGate\CartSms\Eshop\ReturnStatus;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class ReturnStatusTest extends TestCase
{
	public function testReturnStatus(): void
	{
		$return_status_model = \Mockery::mock(\Opencart\Admin\Model\Localisation\ReturnStatus::class);
		$return_status_model->shouldReceive('getReturnStatuses')->withNoArgs()->andReturn([
			['return_status_id' => 1, 'name' => 'Pending'],
			['return_status_id' => 2, 'name' => 'Completed'],
		]);

		$return_status_loader = new ReturnStatus($return_status_model);

		Assert::same([
			1 => 'Pending',
			2 => 'Completed',
		], $return_status_loader->load());
	}
}

(new ReturnStatusTest())->run();