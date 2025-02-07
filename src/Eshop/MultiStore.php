<?php

namespace BulkGate\CartSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;
use BulkGate\Plugin\Strict;

class MultiStore implements Plugin\Eshop\MultiStore
{
	use Strict;

	public function __construct(
		private readonly Configuration $configuration,
		private readonly \Opencart\Admin\Model\Setting\Store | \Opencart\System\Engine\Proxy $store
	)
	{
	}


	public function load(): array
	{
		$store_list = [0 => $this->configuration->name()];

		foreach($this->store->getStores() as $store)
		{
			$store_list[$store['store_id']] = $store['name'];
		}

		return $store_list;
	}
}