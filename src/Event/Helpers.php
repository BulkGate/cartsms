<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;

class Helpers
{
	use Plugin\Strict;

	public static function priorityValues(array $priority, \ArrayAccess $values, $default = null)
	{
		foreach ($priority as $key)
		{
			if ($values[$key] ?? false)
			{
				return $values[$key];
			}
		}

		return $default;
	}
}
