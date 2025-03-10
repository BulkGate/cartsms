<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use BulkGate\Plugin;
use Mockery;
use Tester\{Assert, TestCase};

require_once __DIR__ . '/../../bootstrap.php';


class ProductTest extends TestCase
{

	// todo: rozdelit do Admin a Catalog testu?

	public function testProductId(): void
	{
		$product_model = Mockery::mock(\Opencart\Catalog\Model\Catalog\Product::class);
		$product_model->shouldReceive('getProduct')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn(['store_id' => 1, 'language_id' => 1, 'manufacturer_id' => 1, 'quantity' => 'quantity', 'minimum' => 'min_quantity', 'name' => 'Product', 'model' => 'Model', 'description' => 'description', 'price' => 20, 'ean' => 'ean', 'upc' => 'upc', 'isbn' => 'isbn', 'jan' => 'jan', 'mpn' => 'mpn', 'sku' => 'sku']);

		$manufacturer_model = Mockery::mock(\Opencart\Catalog\Model\Catalog\Manufacturer::class);
		$manufacturer_model->shouldReceive('getManufacturer')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn(['name' => 'Manufacturer']);

		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);
		$formatter->shouldReceive('format')->with('price', 20.0, 'USD')->andReturn('price_locale');

		$product_loader = new \BulkGate\CartSms\Event\Loader\Product($product_model, $manufacturer_model, $formatter);
		$product_loader->load($variables = new Plugin\Event\Variables(['product_id' => 1, 'shop_currency' => 'USD']));
		$product_loader->load(new Plugin\Event\Variables(['product_id' => '1', 'shop_currency' => 'USD']));

		Assert::same([
			'product_id' => 1,
			'shop_currency' => 'USD',
			'shop_id' => 1,
			'lang_id' => 1,
			'product_quantity' => 'quantity',
			'product_minimal_quantity' => 'min_quantity',
			'product_name' => 'Product',
			'product_model' => 'Model',
			'product_description' => 'description',
			'product_manufacturer' => 'Manufacturer',
			'product_price' => 20,
			'product_price_locale' => 'price_locale',
			'product_ean' => 'ean',
			'product_upc' => 'upc',
			'product_isbn' => 'isbn',
			'product_jan' => 'jan',
			'product_mpn' => 'mpn',
			'product_sku' => 'sku',
		], $variables->toArray());
	}

	public function testOverwrite()
	{
		$product_model = Mockery::mock(\Opencart\Catalog\Model\Catalog\Product::class);
		$product_model->shouldReceive('getProduct')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn(['store_id' => 1, 'language_id' => 1, 'manufacturer_id' => 1, 'quantity' => 'quantity', 'minimum' => 'min_quantity', 'name' => 'Product', 'model' => 'Model', 'description' => 'description', 'price' => 20, 'ean' => 'ean', 'upc' => 'upc', 'isbn' => 'isbn', 'jan' => 'jan', 'mpn' => 'mpn', 'sku' => 'sku']);

		$manufacturer_model = Mockery::mock(\Opencart\Catalog\Model\Catalog\Manufacturer::class);
		$manufacturer_model->shouldReceive('getManufacturer')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn(['name' => 'Manufacturer']);

		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);
		$formatter->shouldReceive('format')->with('price', 20.0, 'USD')->andReturn('price_locale');

		$product_loader = new \BulkGate\CartSms\Event\Loader\Product($product_model, $manufacturer_model, $formatter);
		$product_loader->load($variables = new Plugin\Event\Variables(['product_id' => 1, 'shop_id' => 2, 'lang_id' => 3, 'shop_currency' => 'USD']));

		Assert::same(2, $variables['shop_id']);
		Assert::same(3, $variables['lang_id']);
	}

	public function testNoLoad(): void
	{
		$product_model = Mockery::mock(\Opencart\Catalog\Model\Catalog\Product::class);
		$product_model->shouldNotReceive('getProduct');

		$manufacturer_model = Mockery::mock(\Opencart\Catalog\Model\Catalog\Manufacturer::class);
		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);

		$product_loader = new \BulkGate\CartSms\Event\Loader\Product($product_model, $manufacturer_model, $formatter);
		$product_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 2, 'lang_id' => 3]));

		Assert::same(['shop_id' => 2, 'lang_id' => 3], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ProductTest())->run();