<?php declare(strict_types=1);

namespace BulkGate\CartSms\Ajax;

use BulkGate\Plugin;

class Authenticate
{
	use Plugin\Strict;

	public function __construct(private Plugin\Settings\Settings $settings, private Plugin\User\Sign $sign)
	{
	}

	/**
	 * @return array<string, mixed>
	 */
	public function run(string $invalid_redirect): array
	{

		return $this->settings->load('static:application_token') === null
			?
			['redirect' => $invalid_redirect]
			:
			['token' => $this->sign->authenticate(false, ['expire' => time() + 300])];
	}
}