<?php

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class Customer implements Plugin\Event\DataLoader
{
	public function __construct(private $customer)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['customer_id'])) {
			return;
		}

		$customer = $this->customer->getCustomer($variables['customer_id']);

		$variables['customer_mobile'] = $customer['telephone'];
		$variables['customer_email'] = $customer['email'];

		$billing = $this->customer->getAddress((int) $variables['id_address_invoice']);
		$shipping = $this->customer->getAddress((int) $variables['id_address_delivery']);

		$variables['customer_firstname'] = Plugin\Event\Helpers::address('firstname', $shipping, $billing);
		$variables['customer_lastname'] = Plugin\Event\Helpers::address('lastname', $shipping, $billing);
		$variables['customer_company'] = Plugin\Event\Helpers::address('company', $shipping, $billing);
		$variables['customer_address'] = Plugin\Event\Helpers::joinStreet('address_1', 'address_2', $shipping, $billing);
		$variables['customer_city'] = Plugin\Event\Helpers::address('city', $shipping, $billing);
		$variables['customer_state'] = Plugin\Event\Helpers::address('zone', $shipping, $billing);
		$variables['customer_postcode'] = Plugin\Event\Helpers::address('postcode', $shipping, $billing);
		$variables['customer_country'] = Plugin\Event\Helpers::address('country', $shipping, $billing);
		$variables['customer_country_id'] = Plugin\Utils\Strings::lower(Plugin\Event\Helpers::address('iso_code_2', $shipping, $billing));

		$variables['customer_invoice_firstname'] = Plugin\Event\Helpers::address('firstname', $billing, $shipping);
		$variables['customer_invoice_lastname'] = Plugin\Event\Helpers::address('lastname', $billing, $shipping);
		$variables['customer_invoice_company'] = Plugin\Event\Helpers::address('company', $billing, $shipping);
		$variables['customer_invoice_address'] = Plugin\Event\Helpers::joinStreet('address_1', 'address_2', $billing, $shipping);
		$variables['customer_invoice_city'] = Plugin\Event\Helpers::address('city', $billing, $shipping);
		$variables['customer_invoice_state'] = Plugin\Event\Helpers::address('zone', $billing, $shipping);
		$variables['customer_invoice_postcode'] = Plugin\Event\Helpers::address('postcode', $billing, $shipping);
		$variables['customer_invoice_country'] = Plugin\Event\Helpers::address('country', $billing, $shipping);
		$variables['customer_invoice_country_id'] = Plugin\Utils\Strings::lower(Plugin\Event\Helpers::address('iso_code_2', $billing, $shipping));

	}
}