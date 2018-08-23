<?php
namespace BulkGate\CartSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class HookExtension extends Extensions\Strict implements Extensions\Hook\IExtension
{
    public function extend(Extensions\Database\IDatabase $database, Extensions\Hook\Variables $variables)
    {
    }
}
