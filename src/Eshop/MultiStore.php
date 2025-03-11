<?php declare(strict_types=1);

namespace BulkGate\CartSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;

class MultiStore implements Plugin\Eshop\MultiStore
{
	use Plugin\Strict;

	/**
	 * @param \Opencart\Admin\Model\Setting\Store $store
	 */
	public function __construct(private Configuration $configuration, private $store)
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