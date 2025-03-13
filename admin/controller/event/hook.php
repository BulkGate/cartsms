<?php

namespace Opencart\Admin\Controller\Extension\OcCartsms\Event;

use BulkGate\CartSms\Event\State;
use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Hook extends \BulkGate\CartSms\Controller
{
	private State $product_out_of_stock_state;
	private State $return_status_state;

	public function hookMenu(string $route, array &$data)
	{
		$this->language->load('extension/oc_cartsms/module/cartsms');

		$data['menus'][] = [
			'id' => 'menu-cartsms',
			'icon' => 'fas fa-envelope',
			'name' => $this->language->get('extension_name_menu'),
			'href' => $this->url->link('extension/oc_cartsms/module/cartsms', 'user_token=' . $this->session->data['user_token']),
			'children' => []
		];
	}

	public function hookRenderSendMessageBox(string $route, array &$data)
	{
		$order_id = (int) $data['order_id'];

		if ($order_id === 0 || !$this->di_container->getByClass(Plugin\Settings\Settings::class)->load('static:application_token')) {
			return;
		}

		$data['extensions'][] = $this->load->controller('extension/oc_cartsms/module/send_message', $order_id);
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
			//'data' => $params,
		]));
	}

	public function hookProductOutOfStockBefore(string $route, array $params)
	{
		[$id_product] = $params;
		$this->load->model('catalog/product');

		$this->product_out_of_stock_state = (new State(fn() => (int) $this->model_catalog_product->getProduct($id_product)['quantity']))
			->captureInitial()
			->setExpected(0);
	}

	//OK
	public function hookProductOutOfStockAfter(string $route, array $params)
	{
		[$id_product, $data] = $params;

		$this->product_out_of_stock_state->captureActual();

		if (!$this->product_out_of_stock_state->shouldRunHook()) {
			return;
		}

		$this->runHook('product', 'out-of-stock', new Plugin\Event\Variables([
			'product_id' => $id_product,
			'shop_id' => $data['product_store'][0],
			//'data' => $params,
		]));
	}

	public function hookChangeReturnStatusBefore(string $route, array $params)
	{
		[$id_return, $id_return_status] = $params;

		$this->load->model('sale/returns');

		$this->return_status_state = (new State(fn() => (int) $this->model_sale_returns->getReturn($id_return)['return_status_id']))
			->captureInitial()
			->setExpected((int) $id_return_status);
	}

	public function hookChangeReturnStatusAfter(string $route, array $params)
	{
		[$id_return, $id_return_status] = $params;

		$this->return_status_state->captureActual();

		if (!$this->return_status_state->shouldRunHook()) {
			return;
		}

		$this->runHook('return', 'change-status', new Plugin\Event\Variables([
			'return_id' => $id_return,
			'return_status_id' => $id_return_status,
		]));
	}
}