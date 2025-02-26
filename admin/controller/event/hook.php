<?php

namespace Opencart\Admin\Controller\Extension\OcCartsms\Event;

use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Hook extends \BulkGate\CartSms\Controller
{
	private array|null $product = null;

	public function hookMenu(string $route, array &$data)
	{
		$data['menus'][] = [
			'id' => 'menu-cartsms',
			'icon' => 'fas fa-envelope',
			'name' => 'BulkGate SMS',
			'href' => $this->url->link('extension/oc_cartsms/module/cartsms', 'user_token=' . $this->session->data['user_token']),
			'children' => []
		];
	}

	public function hookRenderSendMessageBox(string $route, array &$data)
	{
		//todo: pridat do data['extensions][] ?
		$data['tabs'][] = [
			'title' => 'BulkGate SMS',
			'code' => 'bulkgate_message_box',
			'content' => $this->load->controller('extension/oc_cartsms/module/send_message', (int) $data['order_id'])
		];
	}

	public function hookSendSms(array $params)
	{
		$number = $params['number'] ?? null;
		$template = $params['template'] ?? null;
		$variables = $params['variables'] ?? [];
		$settings = $params['settings'] ?? [];

		$hook = $this->di_container->getByClass(Plugin\Event\Hook::class);

		$hook->send('/api/2.0/advanced/transactional', [
			'number' => $number,
			'application_product' => 'oc',
			'tag' => 'module_custom',
			'variables' => $variables,
			'country' => $settings['country'] ?? null,
			'channel' => [
				'sms' => [
					'sender_id' => $settings['senderType'] ?? 'gSystem',
					'sender_id_value' => $settings['senderValue'] ?? '',
					'unicode' => $settings['unicode'] ?? false,
					'text' => $template,
				],
			],
		]);
	}

	//OK
	public function hookAddCustomer(string $route, array $params, int $id_customer)
	{
		$this->runHook('customer', 'new', new Plugin\Event\Variables([
			'customer_id' => $id_customer,
			'data' => $params,
		]));
	}

	//OK
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
				'shop_id' => $data['product_store'][0],
				'data' => $params,
			]));
		}
	}

	//OK
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