<?php

namespace Opencart\Admin\Controller\Extension\OcCartsms\Module;

use BulkGate\Plugin;
use BulkGate\CartSms\Event\Helpers;

class SendMessage extends \BulkGate\CartSms\Controller
{
	public function index(int $order_id)
	{
		$sign = $this->di_container->getByClass(Plugin\User\Sign::class);
		$url = $this->di_container->getByClass(Plugin\IO\Url::class);
		$loader = $this->di_container->getByClass(Plugin\Event\Loader::class);

		$this->load->model('sale/order');

		$order = $this->model_sale_order->getOrder($order_id);

		$variables = new Plugin\Event\Variables([
			'order_id' => $order_id,
			'customer_id' => $order['customer_id'],
			'lang_id' => $order['language_id'],
		]);

		$loader->load($variables);

		return $this->load->view('extension/oc_cartsms/module/send_message', [
			'token' => $sign->authenticate(),
			'url' => $url,
			'variables' => [
				...$variables->toArray(),
				// these variables are for web component
				'first_name' => Helpers::priorityValues(['customer_firstname', 'customer_invoice_firstname'], $variables),
				'last_name' => Helpers::priorityValues(['customer_lastname', 'customer_invoice_lastname'], $variables),
				'phone_mobile' => Helpers::priorityValues(['customer_mobile', 'customer_phone', 'customer_invoice_mobile', 'customer_invoice_phone'], $variables),
				'phone_number_iso' => Helpers::priorityValues(['customer_country_id', 'customer_invoice_country_id'], $variables)
			]
		]);
	}
}