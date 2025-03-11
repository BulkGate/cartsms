<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use ArrayAccess;
use BulkGate\Plugin;

class Helpers
{
	use Plugin\Strict;

	/**
	 * @param list<string> $priority
	 * @param ArrayAccess<string, mixed>|array<string, mixed> $values
	 */
	public static function priorityValues(array $priority, ArrayAccess|array $values, mixed $default = null): mixed
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
