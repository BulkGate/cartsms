<?php

namespace Opencart\Admin\Controller\Extension\OcCartsms\Event;

use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Cartsms extends \BulkGate\CartSms\Controller
{
	private array|null $product = null;

	public function hookAddCustomer(string $route, array $params, int $id_customer)
	{
		$this->runHook('customer', 'new', new Plugin\Event\Variables([
			'customer_id' => $id_customer,
			'data' => $params,
		]));
	}

	public function hookProductOutOfStock(string $route, array $params)
	{
		[$id_product, $data] = $params;

		if ($this->product === null) {
			return;
		} else if (!isset($this->product['quantity']) || (int) $this->product['quantity'] === 0) {
			return;
		}

		$this->load->model('catalog/product');
		$product = $this->model_catalog_product->getProduct($id_product);

		if ((int) $product['quantity'] === 0) {
			$this->runHook('product', 'out-of-stock', new Plugin\Event\Variables([
				'product_id' => $id_product,
				'data' => $params,
			]));
		}
	}

	public function hookChangeReturnStatus(string $route, array $params)
	{
		[$id_return, $id_return_status] = $params;
		$this->load->model('sale/returns');

		$return = $this->model_sale_returns->getReturn($id_return);

		if ((int) $return['return_status_id'] === (int) $id_return_status) {
			return;
		}

		$this->runHook('return', 'change-status', new Plugin\Event\Variables([
			'return_id' => $id_return,
			'return_status_id' => $id_return_status,
		]));
	}

	public function loadProduct(string $route, array $params)
	{
		[$id_product] = $params;

		$this->load->model('catalog/product');

		$this->product = $this->model_catalog_product->getProduct($id_product);
	}
}