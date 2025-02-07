<?php

namespace BulkGate\CartSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;
use BulkGate\Plugin\Strict;

class Language implements Plugin\Eshop\Language
{
	use Strict;

	public function __construct(private readonly \Opencart\Admin\Model\Localisation\Language | \Opencart\System\Engine\Proxy $language)
	{
	}

	public function load(): array
	{
		$language_list = [];

		foreach ($this->language->getLanguages() as $language)
		{
			$language_list[$language['code']] = $language['name'];
		}

		return $language_list;
	}


	public function get(?int $id = 1): string
	{
		return $this->language->getLanguage($id)['code'];
	}


	public function hasMultiLanguageSupport(): bool
	{
		return true;
	}
}