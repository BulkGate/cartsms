<?php

namespace BulkGate\CartSms\Event;

use BulkGate\Plugin;

class State
{
	use Plugin\Strict;

	private mixed $expected = null; // value to be actual value compared with

	private mixed $actual = null; // value to be used as result of loader after some event.

	private mixed $initial = null; // value to be used for comparison with actual value

	private $loader;


	public function __construct(callable $loader)
	{
		$this->loader = $loader;
	}

	public function setExpected(mixed $expected): self
	{
		$this->expected = $expected;

		return $this;
	}

	public function captureInitial(): self
	{
		$this->initial = $this->callLoader();

		return $this;
	}

	public function captureActual(): self
	{
		$this->actual = $this->callLoader();

		return $this;
	}

	public function shouldRunHook(): bool
	{
		return $this->isExpected() && $this->isChanged();
	}

	public function getActual(): mixed
	{
		return $this->actual;
	}

	public function getInitial(): mixed
	{
		return $this->initial;
	}

	public function getExpected(): mixed
	{
		return $this->expected;
	}

	private function callLoader(...$loader_params): mixed
	{
		return call_user_func($this->loader, ...$loader_params);
	}

	public function isExpected(): bool
	{
		return $this->expected === $this->actual;
	}

	public function isChanged(): bool
	{
		return $this->initial !== $this->actual;
	}

	public function debug(): array
	{
		return [
			'initial' => $this->initial,
			'expected' => $this->expected,
			'actual' => $this->actual,
		];
	}
}