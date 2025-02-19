<?php

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class Order implements Plugin\Event\DataLoader
{
	public function __construct(private $order, private Plugin\Localization\Formatter $formatter)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['order_id'])) {
			return;
		}

		$order = $this->order->getOrder($variables['order_id']);

		$variables['id_address_delivery'] = $order['shipping_address_id'];
		$variables['id_address_invoice'] = $order['payment_address_id'];
		$variables['order_status_id'] = $order['order_status_id']; //todo: je v poradku, ze status nastavujeme zde? Za me ano, protoze vzdycky tyto informace nacteme...

		$variables['order_currency'] = $order['currency_code'];
		$variables['long_order_id'] = sprintf("%06d", $variables['order_id']);
		$variables['order_total_locale'] = $this->formatter->format('price', $order['total'], $variables['order_currency']);
		$variables['order_total_paid'] = $order['total'];
		$variables['order_payment'] = $order['payment_method']['name'];
		$variables['order_tracking'] = $order['tracking'];
		$variables['order_message'] = $order['comment'];

		$timestamp = strtotime($order['date_added']) ?: time();

		$variables['order_date'] = $this->formatter->format('date', $order['date_added']);
		$variables['order_date1'] = date('d.m.Y', $timestamp);
		$variables['order_date2'] = date('d/m/Y', $timestamp);
		$variables['order_date3'] = date('d-m-Y', $timestamp);
		$variables['order_date4'] = date('Y-m-d', $timestamp);
		$variables['order_date5'] = date('m.d.Y', $timestamp);
		$variables['order_date6'] = date('m/d/Y', $timestamp);
		$variables['order_date7'] = date('m-d-Y', $timestamp);
		$variables['order_datetime'] = $this->formatter->format('datetime', $order['date_added']);
		$variables['order_time'] = $this->formatter->format('time', $order['date_added']);
		$variables['order_time1'] = date('H:i:s', $timestamp);

		$variables['order_carrier_name'] = $order['shipping_method']['name'] ?? null;
		$variables['order_carrier_price'] = $order['shipping_method']['cost'] ?? null;
		$variables['order_carrier_price_locale'] = $this->formatter->format('price', $variables['order_carrier_price']);
		$variables['order_carrier_code'] = $order['shipping_method']['code'] ?? null;

		$v1 = $v2 = $v3 = $v4 = $p1 = $p2 = [];

		foreach ($order['products'] as $product)
		{
			$qty = $product['quantity'];
			$name = $product['name'];
			$model = $product['model'];
			$total = $product['total'] + $product['tax'];

			$product_id = $product['order_product_id'];
			$total_formatted = $this->formatter->format('price', $total, $variables['order_currency']);

			$v1[] = "{$qty}x $name $model $total_formatted";
			$v2[] = "{$qty}x $name $total_formatted";
			$v3[] = "{$qty}x ($product_id) $name $model $total_formatted";
			$v4[] = "{$qty}x $model $total_formatted";

			$p1[] = "$qty,$name,$total";
			$p2[] = "$qty;$name;$total";
		}

		$variables['order_products1'] = implode('; ', $v1);
		$variables['order_products2'] = implode('; ', $v2);
		$variables['order_products3'] = implode('; ', $v3);
		$variables['order_products4'] = implode('; ', $v4);

		$variables['order_products5'] = implode("\n", $v1);
		$variables['order_products6'] = implode("\n", $v2);
		$variables['order_products7'] = implode("\n", $v3);
		$variables['order_products8'] = implode("\n", $v4);

		$variables['order_smsprinter1'] = implode(';', $p1);
		$variables['order_smsprinter2'] = implode(';', $p2);
	}
}