<?php

namespace BulkGate\CartSms\Eshop\Test;

use BulkGate\CartSms\Eshop\Configuration;
use BulkGate\CartSms\Eshop\MultiStore;
use Mockery;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class MultiStoreTest extends TestCase
{
	public function testMultiStore(): void
	{
		$configuration = Mockery::mock(Configuration::class);
		$configuration->shouldReceive('name')->withNoArgs()->andReturn('Store');

		$store_model = Mockery::mock(\Opencart\Admin\Model\Setting\Store::class);
		$store_model->shouldReceive('getStores')->withNoArgs()->andReturn([
			['store_id' => 1, 'name' => 'Store 2'],
		]);

		$multistore_loader = new MultiStore($configuration, $store_model);

		Assert::same([
			0 => 'Store',
			1 => 'Store 2',
		], $multistore_loader->load());
	}
}

(new MultiStoreTest())->run();