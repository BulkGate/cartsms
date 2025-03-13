<?php declare(strict_types=1);

namespace BulkGate\CartSms\Eshop\Test;

use BulkGate\CartSms\Eshop\Configuration;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class ConfigurationTest extends TestCase
{
	public function testConfiguration(): void
	{
		$configuration = new Configuration('1.0.0', 'https://www.example.com', 'Example Store');

		Assert::same('1.0.0', $configuration->version());
		Assert::same('https://www.example.com', $configuration->url());
		Assert::same('Example Store', $configuration->name());
		Assert::same('oc', $configuration->product());
	}
}

(new ConfigurationTest())->run();