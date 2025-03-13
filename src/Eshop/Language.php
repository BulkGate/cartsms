<?php declare(strict_types=1);

namespace BulkGate\CartSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;

class Language implements Plugin\Eshop\Language
{
	use Plugin\Strict;

	/**
	 * @param \Opencart\Admin\Model\Localisation\Language $language
	 */
	public function __construct(private mixed $language)
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
		return $this->language->getLanguage((int) $id)['code'];
	}


	public function hasMultiLanguageSupport(): bool
	{
		return true;
	}
}