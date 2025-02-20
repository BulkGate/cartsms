<?php

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class Admin implements Plugin\Event\DataLoader
{
	public function __construct(private $admin_model)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['employee_id'])) {
			return;
		}

		$admin = $this->admin_model->getUser($variables['employee_id']);

		$variables['employee_email'] = $admin['email'];
		$variables['employee_firstname'] = $admin['firstname'];
		$variables['employee_lastname'] = $admin['lastname'];
	}
}