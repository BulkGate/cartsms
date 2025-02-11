<?php

declare(strict_types=1);

namespace BulkGate\CartSms\Database;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Database;
use BulkGate\Plugin\Database\ResultCollection;
use BulkGate\Plugin\Strict;
use Opencart\System\Library\DB;

class Connection implements Database\Connection
{
	use Strict;

	private $db;

	/**
	 * @var list<string>
	 */
	private array $sql = [];

	public function __construct(DB $db)
	{
		$this->db = $db;
	}

	public function execute(string $sql): ?ResultCollection
	{
		$output = new ResultCollection();

		$this->sql[] = $sql;

		$result = (array) $this->db->query($sql);

		foreach ($result['rows'] ?? [] as $key => $item) {
			$output[$key] = (array) $item;
		}

		return $output;
	}

	public function lastId()
	{
		return $this->db->getLastId();
	}

	public function prefix(): string
	{
		return DB_PREFIX;
	}

	public function getSqlList(): array
	{
		return $this->sql;
	}

	public function table(string $table): string
	{
		return $this->prefix() . $table;
	}

	public function prepare(string $sql, ...$parameters): string
	{
		return sprintf($sql, ...array_map(fn ($value) => "'$value'", array_map([$this->db, 'escape'], $parameters)));
	}

	public function escape(string $string): string
	{
		return $this->db->escape($string);
	}
}
