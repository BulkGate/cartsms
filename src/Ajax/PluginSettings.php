<?php declare(strict_types=1);

namespace BulkGate\CartSms\Ajax;

use BulkGate\Plugin;

class PluginSettings
{
	public function __construct(private Plugin\Settings\Settings $settings, private Plugin\Settings\Synchronizer $synchronizer)
	{
	}

	/**
	 * @param array<array-key, mixed> $unsafe_post_data
	 * @return array{data:array<array-key, mixed>}
	 */
	public function run(array $unsafe_post_data, callable $on_language_change): array
	{
		$output = [];

		$actual_language = $this->settings->load('main:language');

		if (isset($unsafe_post_data['marketing_message_opt_in_url']) && is_scalar($unsafe_post_data['marketing_message_opt_in_url']))
		{
			$unsafe_post_data['marketing_message_opt_in_url'] = self::formatUrl((string) $unsafe_post_data['marketing_message_opt_in_url']);
		}

		$this->change('dispatcher', $unsafe_post_data, $output);
		$this->change('synchronization', $unsafe_post_data, $output);
		$this->change('language', $unsafe_post_data, $output);
		$this->change('language_mutation', $unsafe_post_data, $output, 'bool');
		$this->change('delete_db', $unsafe_post_data, $output, 'bool');
		$this->change('address_preference', $unsafe_post_data, $output);
		$this->change('marketing_message_opt_in_enabled', $unsafe_post_data, $output, 'bool');
		$this->change('marketing_message_opt_in_label', $unsafe_post_data, $output);
		$this->change('marketing_message_opt_in_default', $unsafe_post_data, $output, 'bool');
		$this->change('marketing_message_opt_in_url', $unsafe_post_data, $output);

		$this->synchronizer->synchronize(true);

		if (isset($unsafe_post_data['language']) && $actual_language !== $unsafe_post_data['language']) {
			// language change requires hard reload for re-initialization languages.
			// we are using reload_lang parameter to handle url change and effectively reloads the page
			return ['data' => ['redirect' => $on_language_change($unsafe_post_data['language'])]];
		}

		return ['data' => ['layout' => ['server' => ['application_settings' => $output]]], 'success' => ['saved']];
	}

	/**
	 * @param array<array-key, mixed> $unsafe_data
	 * @param array<array-key, string> $output
	 */
	private function change(string $key, array $unsafe_data, array &$output, string $type = 'string'): void
	{
		if (isset($unsafe_data[$key]) && \is_scalar($unsafe_data[$key])) {
			$value = Plugin\Settings\Helpers::deserializeValue((string) $unsafe_data[$key], $type);

			$this->settings->set("main:$key", $output[$key] = $value, ['type' => $type]);
		}
	}

	private static function formatUrl(string $url): string
	{
		return ((int) preg_match('~^[A-Za-z]+?://~', $url)) !== 0 ? $url : "https://$url";
	}
}