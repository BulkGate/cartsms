<?php

namespace Opencart\Catalog\Controller\Extension\OcCartsms\Event;

use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Cartsms extends \BulkGate\CartSms\Controller
{
	private array|null $order = null;

	private array|null $order_products = null;

	public function hookAddOrder(string $route, array $params, int $id_order)
	{
		$this->runHook('order', 'new', new Plugin\Event\Variables([
			'order_id' => $id_order,
			'data' => $params,
		]));
	}

	public function hookAddReturn(string $route, array $params, int $id_return)
	{
		$this->runHook('return', 'new', new Plugin\Event\Variables([
			'return_id' => $id_return,
			'data' => $params,
		]));
	}

	//todo: uprava objednavky v back office porad emituje hook - musim vyuzit stejneho principu jako u change status atd.. /before + /after pary
	public function hookProductOutOfStock(string $route, array $params)
	{
		[$id_order] = $params;

		$this->load->model('checkout/order');
		$this->load->model('catalog/product');

		if ($this->order_products === null) {
			return;
		}

		// zde je aktualni stav produktu
		foreach ($this->order_products as $order_product) {
			$product = $this->model_catalog_product->getProduct($order_product['product_id']);

			if ($order_product['quantity'] > 0 && $product['quantity'] < 1) {
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

		if ($this->order === null) {
			return;
		} elseif ((int) $this->order['order_status_id'] === (int) $id_order_status) {
			return;
		}

		$this->runHook('order', 'change-status', new Plugin\Event\Variables([
			'order_id' => $id_order,
			'order_status_id' => $id_order_status,
		]));
	}

	public function hookAddCustomer(string $route, array $params, int $id_customer)
	{
		$this->runHook('customer', 'new', new Plugin\Event\Variables([
			'customer_id' => $id_customer,
			'data' => $params,
		]));
	}

	public function hookContactForm(string $route)
	{
		$this->runHook('contact', 'form', new Plugin\Event\Variables([
			'route' => $route,
			'data' => $this->request->post,
		]));
	}

	public function loadOrder(string $route, array $params)
	{
		[$id_order] = $params;

		$this->load->model('checkout/order');

		$this->order = $this->model_checkout_order->getOrder($id_order);
	}

	public function loadOrderProducts(string $route, array $params)
	{
		[$id_order] = $params;

		$this->load->model('checkout/order');
		$this->load->model('catalog/product');
		$order_products = $this->model_checkout_order->getProducts($id_order);

		foreach ($order_products as $order_product)
		{
			$product = $this->model_catalog_product->getProduct($order_product['product_id']);

			$this->order_products[] = $product;
		}
	}
}