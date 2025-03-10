<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use BulkGate\Plugin;
use Mockery;
use Tester\{Assert, TestCase};

require_once __DIR__ . '/../../bootstrap.php';


class ShopTest extends TestCase
{

	// todo: rozdelit do Admin a Catalog testu?

	public function testShopId(): void
	{
		$store_model = Mockery::mock(\Opencart\Catalog\Model\Setting\Setting::class);
		$store_model->shouldReceive('getSetting')->with('config', 1)->once()->ordered()->andReturn(['config_name' => 'Shop', 'config_url' => 'http://www.example.com', 'config_currency' => 'USD', 'config_language_catalog' => 'en-gb', 'config_telephone' => '777888999', 'config_email' => 'store@example.com']);
		$store_model->shouldReceive('getSetting')->with('config', 1)->once()->ordered()->andReturn(['config_name' => 'Shop', 'config_url' => 'http://www.example.com', 'config_currency' => 'USD', 'config_language_catalog' => 'en-gb', 'config_telephone' => '777888999', 'config_email' => 'store@example.com']);

		$language_model = Mockery::mock(\Opencart\Catalog\Model\Localisation\Language::class);
		$language_model->shouldReceive('getLanguageByCode')->with('en-gb')->times(2)->andReturn(['language_id' => 1, 'code' => 'en-gb']);
		$language_model->shouldReceive('getLanguage')->with(1)->times(2)->andReturn(['language_id' => 1, 'code' => 'en-gb']);

		$request = Mockery::mock(\Opencart\System\Library\Request::class);

		$shop_loader = new \BulkGate\CartSms\Event\Loader\Shop($store_model, $language_model, $request);
		$shop_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 1]));
		$shop_loader->load(new Plugin\Event\Variables(['shop_id' => 1, ]));

		Assert::same(['shop_id' => 1, 'shop_email' => 'store@example.com', 'shop_name' => 'Shop', 'shop_domain' => 'http://www.example.com', 'shop_currency' => 'USD', 'shop_phone' => '777888999', 'lang_id' => 1, 'lang_iso' => 'en-gb'], $variables->toArray());
	}

	public function testOverwrite()
	{
		$store_model = Mockery::mock(\Opencart\Catalog\Model\Setting\Setting::class);
		$store_model->shouldReceive('getSetting')->with('config', 1)->once()->ordered()->andReturn(['config_name' => 'Shop', 'config_url' => 'http://www.example.com', 'config_currency' => 'USD', 'config_language_catalog' => 'en-gb', 'config_telephone' => '777888999', 'config_email' => 'store@example.com']);

		$language_model = Mockery::mock(\Opencart\Catalog\Model\Localisation\Language::class);
		$language_model->shouldReceive('getLanguage')->with(2)->once()->andReturn(['language_id' => 2, 'code' => 'cs-cz']);

		$request = Mockery::mock(\Opencart\System\Library\Request::class);

		$shop_loader = new \BulkGate\CartSms\Event\Loader\Shop($store_model, $language_model, $request);
		$shop_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 1, 'lang_id' => 2, 'shop_domain' => 'https://www.alza.cz']));

		Assert::same(['shop_id' => 1, 'lang_id' => 2, 'shop_domain' => 'https://www.alza.cz', 'shop_email' => 'store@example.com', 'shop_name' => 'Shop', 'shop_currency' => 'USD', 'shop_phone' => '777888999', 'lang_iso' => 'cs-cz'], $variables->toArray());
	}

	public function testLanguagePriority(): void
	{
		$store_model = Mockery::mock(\Opencart\Catalog\Model\Setting\Setting::class);
		$store_model->shouldReceive('getSetting')->with('config', 1)->times(2)->ordered()->andReturn(['config_name' => 'Shop', 'config_url' => 'http://www.example.com', 'config_currency' => 'USD', 'config_language_catalog' => 'en-gb', 'config_telephone' => '777888999', 'config_email' => 'store@example.com']);

		$language_model = Mockery::mock(\Opencart\Catalog\Model\Localisation\Language::class);
		$language_model->shouldReceive('getLanguageByCode')->with('cs-cz')->once()->andReturn(['language_id' => 2, 'code' => 'cs-cz']);
		$language_model->shouldReceive('getLanguageByCode')->with('fr-fr')->once()->andReturn(['language_id' => 3, 'code' => 'fr-fr']);
		$language_model->shouldReceive('getLanguage')->with(2)->once()->andReturn(['language_id' => 2, 'code' => 'cs-cz']);
		$language_model->shouldReceive('getLanguage')->with(3)->once()->andReturn(['language_id' => 3, 'code' => 'fr-fr']);

		$request = Mockery::mock(\Opencart\System\Library\Request::class);
		$request->get = ['language' => 'fr-fr'];
		$request->cookie = ['language' => 'cs-cz'];

		$shop_loader = new \BulkGate\CartSms\Event\Loader\Shop($store_model, $language_model, $request);
		$shop_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 1]));

		Assert::same(3, $variables['lang_id']);
		Assert::same('fr-fr', $variables['lang_iso']);

		$request->get = [];

		$shop_loader = new \BulkGate\CartSms\Event\Loader\Shop($store_model, $language_model, $request);
		$shop_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 1]));

		Assert::same(2, $variables['lang_id']);
		Assert::same('cs-cz', $variables['lang_iso']);
	}

	public function testNoLoad(): void
	{
		$store_model = Mockery::mock(\Opencart\Catalog\Model\Setting\Setting::class);
		$store_model->shouldNotReceive('getSetting');

		$language_model = Mockery::mock(\Opencart\Catalog\Model\Localisation\Language::class);
		$request = Mockery::mock(\Opencart\System\Library\Request::class);

		$shop_loader = new \BulkGate\CartSms\Event\Loader\Shop($store_model, $language_model, $request);
		$shop_loader->load($variables = new Plugin\Event\Variables(['customer_id' => 2, 'lang_id' => 3]));

		Assert::same(['customer_id' => 2, 'lang_id' => 3], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ShopTest())->run();