<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class Admin implements Plugin\Event\DataLoader
{
	/**
	 * @param \Opencart\Admin\Model\User\User $admin_model
	 */
	public function __construct(private mixed $admin_model)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['employee_id'])) {
			return;
		}

		$admin = $this->admin_model->getUser((int) $variables['employee_id']);

		$variables['employee_email'] = $admin['email'];
		$variables['employee_firstname'] = $admin['firstname'];
		$variables['employee_lastname'] = $admin['lastname'];
	}
}