<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader\Test;

use BulkGate\Plugin;
use Mockery;
use Tester\{Assert, TestCase};

require_once __DIR__ . '/../../bootstrap.php';


class OrderTest extends TestCase
{

	// todo: rozdelit do Admin a Catalog testu?

	public function testOrderId(): void
	{
		$order_model = Mockery::mock(\Opencart\Catalog\Model\Checkout\Order::class);
		$order_model->shouldReceive('getOrder')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn([
			'store_id' => 1,
			'language_id' => 1,
			'customer_id' => 1,
			'shipping_address_id' => 1,
			'payment_address_id' => 1,
			'order_status_id' => 1,
			'shipping_firstname' => 'firstname_shipping',
			'shipping_lastname' => 'lastname_shipping',
			'shipping_company' => 'company_shipping',
			'shipping_address_1' => 'address_1_shipping',
			'shipping_city' => 'city_shipping',
			'shipping_zone' => 'zone_shipping',
			'shipping_postcode' => 'postcode_shipping',
			'shipping_country' => 'country_shipping',
			'shipping_iso_code_2' => 'iso_code_shipping',

			'payment_firstname' => 'firstname_payment',
			'payment_lastname' => 'lastname_payment',
			'payment_company' => 'company_payment',
			'payment_address_1' => 'address_1_payment',
			'payment_city' => 'city_payment',
			'payment_zone' => 'zone_payment',
			'payment_postcode' => 'postcode_payment',
			'payment_country' => 'country_payment',
			'payment_iso_code_2' => 'iso_code_payment',

			'telephone' => 'telephone',
			'email' => 'email',
			'currency_code' => 'USD',
			'total' => 100,
			'payment_method' => ['name' => 'Payment'],
			'shipping_method' => ['name' => 'Shipping', 'cost' => 5, 'code' => 'shipping_code'],
			'products' => [
				['order_product_id' => 'product_id', 'name' => 'name', 'model' => 'model', 'quantity' => 2, 'total' => 40, 'tax' => 4.8],
			],
			'tracking' => 'tracking',
			'comment' => 'comment',
			'date_added' => '2025-03-10 13:00:00'
		]);

		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);
		$formatter->shouldReceive('format')->with('price', 100.0, 'USD')->andReturn('price_locale');
		$formatter->shouldReceive('format')->with('date', '2025-03-10 13:00:00')->andReturn('date_locale');
		$formatter->shouldReceive('format')->with('datetime', '2025-03-10 13:00:00')->andReturn('datetime_locale');
		$formatter->shouldReceive('format')->with('time', '2025-03-10 13:00:00')->andReturn('time_locale');
		$formatter->shouldReceive('format')->with('price', 5.0, 'USD')->andReturn('carrier_price_locale');
		$formatter->shouldReceive('format')->with('price', 44.8, 'USD')->andReturn('product_price_locale');

		$order_loader = new \BulkGate\CartSms\Event\Loader\Order($order_model, $formatter);
		$order_loader->load($variables = new Plugin\Event\Variables(['order_id' => 1]));
		$order_loader->load(new Plugin\Event\Variables(['order_id' => '1']));

		Assert::same([
			'order_id' => 1,
			'customer_firstname' => 'firstname_shipping',
			'customer_lastname' => 'lastname_shipping',
			'customer_company' => 'company_shipping',
			'customer_address' => 'address_1_shipping',
			'customer_city' => 'city_shipping',
			'customer_state' => 'zone_shipping',
			'customer_postcode' => 'postcode_shipping',
			'customer_country' => 'country_shipping',
			'customer_country_id' => 'iso_code_shipping',
			'customer_invoice_firstname' => 'firstname_payment',
			'customer_invoice_lastname' => 'lastname_payment',
			'customer_invoice_company' => 'company_payment',
			'customer_invoice_address' => 'address_1_payment',
			'customer_invoice_city' => 'city_payment',
			'customer_invoice_state' => 'zone_payment',
			'customer_invoice_postcode' => 'postcode_payment',
			'customer_invoice_country' => 'country_payment',
			'customer_invoice_country_id' => 'iso_code_payment',
			'customer_mobile' => 'telephone',
			'customer_email' => 'email',
			'shop_id' => 1,
			'lang_id' => 1,
			'customer_id' => 1,
			'id_address_delivery' => 1,
			'id_address_invoice' => 1,
			'order_status_id' => 1,
			'order_currency' => 'USD',
			'long_order_id' => '000001',
			'order_total_locale' => 'price_locale',
			'order_total_paid' => 100,
			'order_payment' => 'Payment',
			'order_tracking' => 'tracking',
			'order_message' => 'comment',
			'order_date' => 'date_locale',
			'order_date1' => '10.03.2025',
			'order_date2' => '10/03/2025',
			'order_date3' => '10-03-2025',
			'order_date4' => '2025-03-10',
			'order_date5' => '03.10.2025',
			'order_date6' => '03/10/2025',
			'order_date7' => '03-10-2025',
			'order_datetime' => 'datetime_locale',
			'order_time' => 'time_locale',
			'order_time1' => '13:00:00',
			'order_carrier_name' => 'Shipping',
			'order_carrier_price' => 5,
			'order_carrier_price_locale' => 'carrier_price_locale',
			'order_carrier_code' => 'shipping_code',
			'order_products1' => '2x name model product_price_locale',
			'order_products2' => '2x name product_price_locale',
			'order_products3' => '2x (product_id) name model product_price_locale',
			'order_products4' => '2x model product_price_locale',
			'order_products5' => '2x name model product_price_locale',
			'order_products6' => '2x name product_price_locale',
			'order_products7' => '2x (product_id) name model product_price_locale',
			'order_products8' => '2x model product_price_locale',
			'order_smsprinter1' => '2,name,44.8',
			'order_smsprinter2' => '2;name;44.8',
		], $variables->toArray());
	}

	public function testOverwrite()
	{
		$order_model = Mockery::mock(\Opencart\Catalog\Model\Checkout\Order::class);
		$order_model->shouldReceive('getOrder')->with(Mockery::on(fn ($arg) => $arg === 1))->andReturn([
			'store_id' => 1,
			'language_id' => 1,
			'customer_id' => 1,
			'shipping_address_id' => 1,
			'payment_address_id' => 1,
			'order_status_id' => 1,
			'shipping_firstname' => 'firstname_shipping',
			'shipping_lastname' => 'lastname_shipping',
			'shipping_company' => 'company_shipping',
			'shipping_address_1' => 'address_1_shipping',
			'shipping_city' => 'city_shipping',
			'shipping_zone' => 'zone_shipping',
			'shipping_postcode' => 'postcode_shipping',
			'shipping_country' => 'country_shipping',
			'shipping_iso_code_2' => 'iso_code_shipping',

			'payment_firstname' => 'firstname_payment',
			'payment_lastname' => 'lastname_payment',
			'payment_company' => 'company_payment',
			'payment_address_1' => 'address_1_payment',
			'payment_city' => 'city_payment',
			'payment_zone' => 'zone_payment',
			'payment_postcode' => 'postcode_payment',
			'payment_country' => 'country_payment',
			'payment_iso_code_2' => 'iso_code_payment',

			'telephone' => 'telephone',
			'email' => 'email',
			'currency_code' => 'USD',
			'total' => 100,
			'payment_method' => ['name' => 'Payment'],
			'shipping_method' => ['name' => 'Shipping', 'cost' => 5, 'code' => 'shipping_code'],
			'products' => [
				['order_product_id' => 'product_id', 'name' => 'name', 'model' => 'model', 'quantity' => 2, 'total' => 40, 'tax' => 4.8],
			],
			'tracking' => 'tracking',
			'comment' => 'comment',
			'date_added' => '2025-03-10 13:00:00'
		]);
		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);
		$formatter->shouldReceive('format')->with('price', 100.0, 'USD')->andReturn('price_locale');
		$formatter->shouldReceive('format')->with('date', '2025-03-10 13:00:00')->andReturn('date_locale');
		$formatter->shouldReceive('format')->with('datetime', '2025-03-10 13:00:00')->andReturn('datetime_locale');
		$formatter->shouldReceive('format')->with('time', '2025-03-10 13:00:00')->andReturn('time_locale');
		$formatter->shouldReceive('format')->with('price', 5.0, 'USD')->andReturn('carrier_price_locale');
		$formatter->shouldReceive('format')->with('price', 44.8, 'USD')->andReturn('product_price_locale');

		$order_loader = new \BulkGate\CartSms\Event\Loader\Order($order_model, $formatter);
		$order_loader->load($variables = new Plugin\Event\Variables(['order_id' => 1, 'shop_id' => 2, 'lang_id' => 3, 'customer_id' => 4, 'id_address_delivery' => 5, 'id_address_invoice' => 6, 'order_status_id' => 7]));

		Assert::same(2, $variables['shop_id']);
		Assert::same(3, $variables['lang_id']);
		Assert::same(4, $variables['customer_id']);
		Assert::same(5, $variables['id_address_delivery']);
		Assert::same(6, $variables['id_address_invoice']);
		Assert::same(7, $variables['order_status_id']);
	}

	public function testNoLoad(): void
	{
		$order_model = Mockery::mock(\Opencart\Catalog\Model\Checkout\Order::class);
		$order_model->shouldNotReceive('getOrder');

		$formatter = Mockery::mock(\BulkGate\Plugin\Localization\Formatter::class);

		$order_loader = new \BulkGate\CartSms\Event\Loader\Order($order_model, $formatter);
		$order_loader->load($variables = new Plugin\Event\Variables(['shop_id' => 2, 'lang_id' => 3]));

		Assert::same(['shop_id' => 2, 'lang_id' => 3], $variables->toArray());
	}

	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new OrderTest())->run();