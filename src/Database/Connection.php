<?php declare(strict_types=1);

namespace BulkGate\CartSms\Database;

/*
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin;
use Opencart\System\Library\DB as OpencartDB;

class Connection implements Plugin\Database\Connection
{
	use Plugin\Strict;

	/** @var OpencartDB */
	private $db;

	/**
	 * @var list<string>
	 */
	private array $sql = [];

	public function __construct(OpencartDB $db)
	{
		$this->db = $db;
	}

	public function execute(string $sql): Plugin\Database\ResultCollection
	{
		$output = new Plugin\Database\ResultCollection();

		$this->sql[] = $sql;

		$result = (array) $this->db->query($sql);

		foreach ($result['rows'] ?? [] as $key => $item) {
			$output[$key] = (array) $item;
		}

		return $output;
	}

	public function lastId(): int
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
