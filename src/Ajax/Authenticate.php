<?php

namespace BulkGate\CartSms\Ajax;

use BulkGate\Plugin;

class Authenticate
{
	use Plugin\Strict;

	public function __construct(private readonly Plugin\Settings\Settings $settings, private readonly Plugin\User\Sign $sign)
	{
	}

	public function run(string $invalid_redirect): array
	{

		return $this->settings->load('static:application_token') === null
			?
			['redirect' => $invalid_redirect]
			:
			['token' => $this->sign->authenticate(false, ['expire' => time() + 300])];
	}
}