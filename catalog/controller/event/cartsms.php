<?php

namespace Opencart\Catalog\Controller\Extension\OcCartsms\Event;

use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Cartsms extends \BulkGate\CartSms\Controller
{
	private $order;

	public function hookAddOrder(string $route, array $params, int $id_order)
	{
		$this->runHook('order', 'new', new Plugin\Event\Variables([
			'order_id' => $id_order,
			'data' => $params,
		]));
	}

	public function hookProductOutOfStock(string $route, array $params)
	{
		[$id_order] = $params;
		$this->load->model('checkout/order');
		$this->load->model('catalog/product');
		$order_products = $this->model_checkout_order->getProducts($id_order);

		foreach ($order_products as $order_product)
		{
			$product = $this->model_catalog_product->getProduct($order_product['product_id']);

			if ($product['quantity'] < 1)
			{
				$this->runHook('product', 'out-of-stock', new Plugin\Event\Variables([
					'order_id' => $id_order,
					'product_id' => $order_product['product_id'],
					'data' => $params,
				]));
			}
		}
	}

	public function hookChangeOrderStatus(string $route, array $params)
	{
		[$id_order, $id_order_status] = $params;

		// check for status change
		if ($this->order['order_status_id'] == $id_order_status) {
			return;
		}

		$this->runHook('order', 'change-status', new Plugin\Event\Variables([
			'order_id' => $id_order,
			'order_status_id' => $id_order_status,
		]));
	}

	public function loadOrder(string $route, array $params)
	{
		[$id_order] = $params;

		$this->load->model('checkout/order');

		$this->order = $this->model_checkout_order->getOrder($id_order);
	}

	public function hookAddCustomer(string $route, array $params, int $id_customer)
	{
		$this->runHook('customer', 'new', new Plugin\Event\Variables([
			'customer_id' => $id_customer,
			'data' => $params,
		]));
	}
}