<?php

namespace BulkGate\CartSms\Eshop;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;
use BulkGate\Plugin\Strict;

class ReturnStatus implements Plugin\Eshop\ReturnStatus
{
	use Strict;

	public function __construct(private readonly \Opencart\Admin\Model\Localisation\ReturnStatus | \Opencart\System\Engine\Proxy $return_status)
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