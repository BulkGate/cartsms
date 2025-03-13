<?php declare(strict_types=1);

namespace BulkGate\CartSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;

class Configuration implements Plugin\Eshop\Configuration
{
	use Plugin\Strict;

	private string $version_number;

	private string $site_url;

	private string $site_name;


	public function __construct(string $version_number, string $site_url, string $site_name)
	{
		$this->version_number = $version_number;
		$this->site_url = $site_url;
		$this->site_name = $site_name;
	}


	public function url(): string
	{
		return $this->site_url;
	}


	public function product(): string
	{
		return 'oc';
	}


	public function version(): string
	{
		return $this->version_number;
	}


	public function name(): string
	{
		return $this->site_name;
	}
}