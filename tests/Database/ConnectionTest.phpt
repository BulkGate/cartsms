<?php declare(strict_types=1);

namespace BulkGate\WooSms\Database\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use BulkGate\CartSms\Database\Connection;
use Opencart\System\Library\DB;
use Tester\{Assert, TestCase};


require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class ConnectionWordpressTest extends TestCase
{
	public function __construct()
	{
		define('DB_PREFIX', 'oc_');
	}

	public function testExecute(): void
	{
		$connection = new Connection($db = Mockery::mock(DB::class));
		$db->shouldReceive('query')->with('SQL')->once()->andReturn([
			'rows' => [['id' => 4], ['id' => 5]]
		]);

		[$e1, $e2] = $connection->execute('SQL')->toArray();

		Assert::same([['id' => 4], ['id' => 5]], [$e1->toArray(), $e2->toArray()]);
		Assert::same(['SQL'], $connection->getSqlList());
	}


	public function testPrepare(): void
	{
		$connection = new Connection($db = Mockery::mock(DB::class));

		$db->shouldReceive('escape')->with('hello')->once()->andReturn('hello');

		Assert::same("SQL ('hello')", $connection->prepare('SQL (%s)', 'hello'));
	}


	public function testLastId(): void
	{
		$connection = new Connection($db = Mockery::mock(DB::class));
		$db->shouldReceive('getLastId')->withNoArgs()->once()->andReturn(451);

		Assert::same(451, $connection->lastId());
	}


	public function testEscape(): void
	{
		$connection = new Connection($db = Mockery::mock(DB::class));
		$db->shouldReceive('escape')->with('dangerous string')->once()->andReturn('safe string');

		Assert::same('safe string', $connection->escape('dangerous string'));
	}


	public function testPrefix(): void
	{
		$connection = new Connection($db = Mockery::mock(DB::class));

		Assert::same('oc_', $connection->prefix());
	}


	public function testTable(): void
	{
		$connection = new Connection($db = Mockery::mock(DB::class));

		Assert::same('oc_users', $connection->table('users'));
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ConnectionWordpressTest())->run();
