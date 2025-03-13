<?php

namespace Opencart\Catalog\Controller\Extension\OcCartsms\Event;

use BulkGate\CartSms\Event\State;
use BulkGate\Plugin;

require_once DIR_EXTENSION . 'oc_cartsms/vendor/autoload.php';

class Hook extends \BulkGate\CartSms\Controller
{
	private State $product_out_of_stock_state;

	private State|null $order_status_state = null;

	public function hookAsynchronousAsset(string $route, array &$data)
	{
		$data['analytics'][] = '<script type="text/javascript" async src="'. $this->url->link('extension/oc_cartsms/asynchronous/task') .'"></script>'; //todo: udelat twig template?
	}

	// OK
	public function hookCustomFields(string $route, array $params, array &$data)
	{
		$settings = $this->di_container->getByClass(Plugin\Settings\Settings::class);

		if (!$settings->load('main:marketing_message_opt_in_enabled')) {
			return;
		}

		$data[] = [
			'custom_field_id' => 'bulkgate_marketing_message_opt_in',
			'location' => 'account',
			'type' => 'checkbox',
			'required' => false,
			//'name' => 'abc',
			'custom_field_value' => [[
				'custom_field_value_id' => 'bulkgate_marketing_message_opt_in', // ==
				'custom_field_id' => 'bulkgate_marketing_message',
				'name' => 'I consent to receiving marketing communications via SMS, Viber, RCS, WhatsApp, and other similar channels.'
			]]
		];
	}

	//OK
	public function hookAddOrderCheckoutSuccess(string $route)
	{
		if (!isset($this->session->data['order_id'])) {
			return;
		}

		$this->runHook('order', 'new', new Plugin\Event\Variables([
			'order_id' => $this->session->data['order_id'],
			//'data' => $this->session->data, todo: tady jeste muzeme ziskat data o adrese v pripade, ze uzivatel neni prihlaseny. Prednastavime promenne? Chtelo by to asi nejaky helper...
		]));
	}

	public function hookAddOrderApi(string $route)
	{
		if ($this->request->get['call'] !== 'confirm') {
			return;
		}

		$output = Plugin\Utils\JsonArray::decode($this->response->getOutput());

		if (!$output || (int) $this->request->post['order_id'] === (int) $output['order_id']) {
			return;
		}

		$this->runHook('order', 'new', new Plugin\Event\Variables([
			'order_id' => $output['order_id'],
			//'response' => $output,
			//'_GET' => $this->request->get,
			//'_POST' => $this->request->post,
			//'data' => $data, //todo: api/order.index neprebira zadne parametry...
			//'output' => $output, todo: provolava se pres API, takze $outptut neni nastaveny, protoze api/order.index nic nevraci. Muzeme maximalne vyuzit response objektu
			//'store_url' => $shop_domain,
		]));
	}

	//OK
	public function hookAddReturn(string $route, array $params, int $id_return)
	{
		$this->runHook('return', 'new', new Plugin\Event\Variables([
			'return_id' => $id_return,
		]));
	}

	public function hookProductOutOfStockBefore(string $route, array $params)
	{
		[$id_order] = $params;

		$this->load->model('checkout/order');
		$this->load->model('catalog/product');

		$products = $this->model_checkout_order->getProducts($id_order);

		$this->product_out_of_stock_state = (new State(fn () => array_map(fn($item) => (int) $this->model_catalog_product->getProduct($item['product_id'])['quantity'] ?? 0, array_combine(array_column($products, 'product_id'), $products))))
			->captureInitial();
	}

	//OK
	public function hookProductOutOfStockAfter(string $route, array $params)
	{
		[$id_order] = $params;

		$this->product_out_of_stock_state->captureActual();

		if (!$this->product_out_of_stock_state->isChanged()) {
			return;
		}

		$initial_products = $this->product_out_of_stock_state->getInitial();
		$actual_products = $this->product_out_of_stock_state->getActual();

		foreach($actual_products as $product_id => $quantity) {
			$initial_quantity = $initial_products[$product_id];

			if ($initial_quantity && $initial_quantity !== $quantity && $quantity === 0) {
				$this->runHook('product', 'out-of-stock', new Plugin\Event\Variables([
					'order_id' => $id_order,
					'product_id' => $product_id,
					//'data' => $params,
				]));
			}
		}
	}

	//OK
	public function hookChangeOrderStatusBefore(string $route, array $params)
	{
		if ($this->request->get['call'] !== 'history_add') {
			return;
		}

		$this->load->model('checkout/order');

		$this->order_status_state = (new State(fn() => (int) $this->model_checkout_order->getOrder($this->request->post['order_id'])['order_status_id']))
			->captureInitial()
			->setExpected((int) $this->request->post['order_status_id']);
	}

	public function hookChangeOrderStatusAfter(string $route, array $params)
	{
		if ($this->order_status_state === null) {
			return;
		}

		$this->order_status_state->captureActual();

		if (!$this->order_status_state->shouldRunHook()) {
			return;
		}

		$this->runHook('order', 'change-status', new Plugin\Event\Variables([
			'order_id' => $this->request->post['order_id'],
			'order_status_id' => $this->request->post['order_status_id'],
			//'debug' => $this->order_status_state->debug(),
		]));
	}

	//OK
	public function hookAddCustomer(string $route, array $params, int $id_customer)
	{
		$this->runHook('customer', 'new', new Plugin\Event\Variables([
			'customer_id' => $id_customer,
			//'data' => $params,
		]));
	}

	//OK
	public function hookContactForm(string $route)
	{
		$this->runHook('contact', 'form', new Plugin\Event\Variables([
			'shop_id' => $this->config->get('config_store_id'),
			'customer_email' => $this->request->post['email'],
			'customer_name' => $this->request->post['name'],
			'customer_message' => $this->request->post['enquiry'],
			//'data' => $this->request->post,
		]));
	}
}