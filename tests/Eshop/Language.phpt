<?php

namespace BulkGate\CartSms\Eshop\Test;

use BulkGate\CartSms\Eshop\Language;
use Mockery;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class LanguageTest extends TestCase
{
	public function testLanguage(): void
	{
		$language_model = Mockery::mock(\Opencart\Admin\Model\Localisation\Language::class);
		$language_model->shouldReceive('getLanguages')->withNoArgs()->andReturn([
			['code' => 'en-gb', 'name' => 'English'],
			['code' => 'cs-cz', 'name' => 'Czech'],
		]);

		$language_loader = new Language($language_model);

		Assert::same([
			'en-gb' => 'English',
			'cs-cz' => 'Czech',
		], $language_loader->load());
	}

	public function testMultiLanguageSupport(): void
	{
		$language_model = Mockery::mock(\Opencart\Admin\Model\Localisation\Language::class);
		$language_loader = new Language($language_model);

		Assert::true($language_loader->hasMultiLanguageSupport());
	}

	public function testGetLanguage(): void
	{
		$language_model = Mockery::mock(\Opencart\Admin\Model\Localisation\Language::class);
		$language_model->shouldReceive('getLanguage')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn(['code' => 'en-gb']);

		$language_loader = new Language($language_model);

		Assert::same('en-gb', $language_loader->get());
		Assert::same('en-gb', $language_loader->get(1));
		Assert::same('en-gb', $language_loader->get("1"));
	}
}

(new LanguageTest())->run();