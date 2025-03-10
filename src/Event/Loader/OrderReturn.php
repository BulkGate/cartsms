<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class OrderReturn implements Plugin\Event\DataLoader
{
	/** @param \Opencart\Catalog\Model\Account\Returns | \Opencart\Admin\Model\Sale\Returns $order_return_model */
	public function __construct(private $order_return_model, private Plugin\Localization\Formatter $formatter)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['return_id'])) {
			return;
		}

		$order_return = $this->order_return_model->getReturn((int) $variables['return_id']);

		$variables['lang_id'] ??= $order_return['language_id'] ?? null;
		$variables['order_id'] ??= $order_return['order_id'] ?? null;
		$variables['customer_id'] ??= $order_return['customer_id'] ?? null;
		$variables['return_status_id'] ??= $order_return['return_status_id'] ?? null;

		$variables['return_customer_message'] = $order_return['comment'];
		$variables['return_date'] = $this->formatter->format('date', $order_return['date_added']);

		// pro customera + administratora
		$variables['return_action'] = $order_return['action'] ?? null;
		$variables['return_action_id'] = $order_return['return_action_id'];
		$variables['return_reason_id'] = $order_return['return_reason_id'];
		$variables['return_reason'] = $order_return['reason'] ?? null;

		//pouze pro administratora
		$variables['return_status'] = $order_return['return_status'] ?? null;

		$variables['return_products1'] = $order_return['quantity'] . 'x ' . $order_return['product'] . ' ' . $order_return['product_id'];
		$variables['return_products2'] = $order_return['quantity'] . 'x ' . $order_return['product'];
		$variables['return_products3'] = $order_return['quantity'] . 'x (' . $order_return['product_id'] . ') ' . $order_return['product'];
		$variables['return_products4'] = $order_return['quantity'] . 'x ' . $order_return['product_id'];
	}
}