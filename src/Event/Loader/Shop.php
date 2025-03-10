<?php declare(strict_types=1);

namespace BulkGate\CartSms\Event\Loader;

use BulkGate\Plugin;

class Shop implements Plugin\Event\DataLoader
{
	/** @param \Opencart\Catalog\Model\Setting\Setting | \Opencart\Admin\Model\Setting\Setting $settings_model */
	/** @param \Opencart\Catalog\Model\Localisation\Language | \Opencart\Admin\Model\Localisation\Language $language_model */
	public function __construct(private $settings_model, private $language_model, private $request)
	{
	}

	public function load(Plugin\Event\Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['shop_id'])) {
			return;
		}

		$settings = $this->settings_model->getSetting('config', (int) $variables['shop_id']);

		$variables['shop_email'] = $settings['config_email'];
		$variables['shop_name'] = $settings['config_name'];
		$variables['shop_domain'] ??= $settings['config_url'] ?? ""; //?? HTTP_CATALOG; //todo: shop 0 tuto polozku nema ... tady musime vzit aktualni url?
		$variables['shop_currency'] = $settings['config_currency'];
		$variables['shop_phone'] = $settings['config_telephone'];
		$variables['lang_id'] ??= ($this->language_model->getLanguageByCode($this->request->get['language'] ?? $this->request->cookie['language'] ?? $settings['config_language_catalog']))['language_id'];
		$variables['lang_iso'] = $this->language_model->getLanguage($variables['lang_id'])['code'];
	}
}