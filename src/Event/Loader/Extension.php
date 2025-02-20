<?php

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class Extension implements Plugin\Event\DataLoader
{
	use Plugin\Strict;

	public function __construct(private \Opencart\System\Engine\Event $event)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		// give chance to modify variables
		$this->event->trigger('cartsms.hook.extension', [$variables, $parameters]);
	}
}