<?php

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class Extension implements Plugin\Event\DataLoader
{
	use Plugin\Strict;

	//todo: Kdyz je uveden datovy typ, tak DI container hlasi ze neni uvedena sluzba (datovy typ). Stejny princip je pouzit
	//todo: napr. u BulkGate\CartSms\Eshop\Language
	public function __construct(private /*readonly \Opencart\System\Engine\Event*/ $event)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		// give chance to modify variables
		$this->event->trigger('cartsms.hook.extension', [$variables, $parameters]);
	}
}