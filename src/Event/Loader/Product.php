<?php

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class Product implements Plugin\Event\DataLoader
{
	public function __construct(private $product_model, private $manufacturer_model, private Plugin\Localization\Formatter $formatter)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['product_id'])) {
			return;
		}

		$product = $this->product_model->getProduct($variables['product_id']);

		$variables['shop_id'] ??= $product['store_id'] ?? null;
		$variables['lang_id'] ??= $product['language_id'] ?? null;

		$variables['product_quantity'] = $product['quantity'];
		$variables['product_minimal_quantity'] = $product['minimum'];
		$variables['product_name'] = $product['name'];
		$variables['product_model'] = $product['model'];
		$variables['product_description'] = \strip_tags(html_entity_decode($product['description']));
		$manufacturer_id = (int) $product['manufacturer_id'];

		if ($manufacturer_id) {
			$variables['product_manufacturer'] = $this->manufacturer_model->getManufacturer($manufacturer_id)['name'];
		}

		//todo: TAX - ceny jsou uvedeny bez DPH
		$variables['product_price'] = $product['price'];
		$variables['product_price_locale'] = $this->formatter->format('price', (float) $product['price'], $variables['shop_currency']);

		$variables['product_ean'] = $product['ean'];
		$variables['product_upc'] = $product['upc'];
		$variables['product_isbn'] = $product['isbn'];
		$variables['product_jan'] = $product['jan'];
		$variables['product_mpn'] = $product['mpn'];
		$variables['product_sku'] = $product['sku'];
	}
}