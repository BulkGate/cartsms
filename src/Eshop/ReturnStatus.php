<?php declare(strict_types=1);

namespace BulkGate\CartSms\Eshop;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;

class ReturnStatus implements Plugin\Eshop\ReturnStatus
{
	use Plugin\Strict;

	/**
	 * @param \Opencart\Admin\Model\Localisation\ReturnStatus $return_status
	 */
	public function __construct(private $return_status)
	{
	}

	public function load(): array
	{
		$return_status_list = [];

		foreach ($this->return_status->getReturnStatuses() as $return_status) {
			$return_status_list[$return_status['return_status_id']] = $return_status['name'];
		}

		return $return_status_list;
	}
}