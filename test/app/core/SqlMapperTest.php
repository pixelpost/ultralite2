<?php

namespace pixelpost;

/**
 * Test class for SqlMapper.
 */
class SqlMapperTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var SqlMapper
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new SqlMapper;

		$this->object->map('name',      'username',  SqlMapper::DATA_STRING);
		$this->object->map('total',     'count',     SqlMapper::DATA_INT);
		$this->object->map('birth',     'birthday',  SqlMapper::DATA_DATE);
		$this->object->map('last-seen', 'connected', SqlMapper::DATA_DATE);
		$this->object->map('active',    'active',    SqlMapper::DATA_BOOL);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**
	 * @covers pixelpost\SqlMapper::create
	 */
	public function test_create()
	{
		$this->assertTrue(SqlMapper::create() instanceOf SqlMapper);
	}

	/**
	 * @covers pixelpost\SqlMapper::map
	 */
	public function test_map()
	{
		$result = $this->object->map('data_name', 'sql_name', SqlMapper::DATA_STRING, SqlMapper::SQL_TEXT);

		$this->assertTrue($result instanceOf SqlMapper);
	}

	/**
	 * @covers pixelpost\SqlMapper::genSqlSelectList
	 */
	public function test_genSqlSelectList()
	{
		$fields = array('name', 'active', 'birth');

		$result = 'username, active, birthday';

		$this->assertSame($result, $this->object->genSqlSelectList($fields));
	}

	/**
	 * @covers pixelpost\SqlMapper::genSqlSelectList
	 */
	public function test_genSqlSelectList_unknown_field()
	{
		$fields = array('name', 'active', 'foo', 'birth');

		$result = 'username, active, birthday';

		$this->assertSame($result, $this->object->genSqlSelectList($fields));
	}


	/**
	 * @covers pixelpost\SqlMapper::genSqlUpdateList
	 * @covers pixelpost\SqlMapper::_castSql
	 */
	public function test_genSqlUpdateList()
	{
		$fields = array(
			'active' => true,
			'birth'  => new \DateTime('2012-02-29 14:15:16', new \DateTimeZone('UTC')),
			'name'   => 'Alban',
			'total'  => 3,
		);

		$result = 'active = 1, birthday = 20120229141516, username = \'Alban\', count = 3';

		$this->assertSame($result, $this->object->genSqlUpdateList($fields));
	}

	/**
	 * @covers pixelpost\SqlMapper::genSqlUpdateList
	 * @covers pixelpost\SqlMapper::_castSql
	 */
	public function test_genSqlUpdateList_unknown_field()
	{
		$fields = array(
			'active' => true,
			'birth'  => new \DateTime('2012-02-29 14:15:16', new \DateTimeZone('UTC')),
			'foo'    => 42,
			'name'   => 'Alban',
			'total'  => 3,
		);

		$result = 'active = 1, birthday = 20120229141516, username = \'Alban\', count = 3';

		$this->assertSame($result, $this->object->genSqlUpdateList($fields));
	}

	/**
	 * @covers pixelpost\SqlMapper::genSqlInsertList
	 * @covers pixelpost\SqlMapper::_castSql
	 */
	public function test_genSqlInsertList()
	{
		$fields = array(
			'active' => true,
			'birth'  => new \DateTime('2012-02-29 14:15:16', new \DateTimeZone('UTC')),
			'name'   => 'Alban',
			'total'  => 3,
		);

		$result = '(active, birthday, username, count) VALUES (1, 20120229141516, \'Alban\', 3)';

		$this->assertSame($result, $this->object->genSqlInsertList($fields));
	}

	/**
	 * @covers pixelpost\SqlMapper::genSqlInsertList
	 * @covers pixelpost\SqlMapper::_castSql
	 */
	public function test_genSqlInsertList_unknown_field()
	{
		$fields = array(
			'active' => true,
			'birth'  => new \DateTime('2012-02-29 14:15:16', new \DateTimeZone('UTC')),
			'foo'    => 42,
			'name'   => 'Alban',
			'total'  => 3,
		);

		$result = '(active, birthday, username, count) VALUES (1, 20120229141516, \'Alban\', 3)';

		$this->assertSame($result, $this->object->genSqlInsertList($fields));
	}

	/**
	 * @covers pixelpost\SqlMapper::genArrayResult
	 * @covers pixelpost\SqlMapper::_castData
	 */
	public function test_genArrayResult()
	{
		$result = array(
			'birthday' => '20120229141516',
			'active'   => '0',
			'username' => 'Bertrand',
			'count'    => '18',
		);

		$test = array(
			'birth'  => '2012-02-29 14:15:16',
			'active' => false,
			'name'   => 'Bertrand',
			'total'  => 18,
		);

		$me = $this;

		$todo = function(&$result) use ($me)
		{
			$me->assertTrue($result['birth'] instanceOf \DateTime);

			$result['birth'] = $result['birth']->format('Y-m-d H:i:s');
		};

		$this->assertSame($test, $this->object->genArrayResult($result, $todo));
	}

	/**
	 * @covers pixelpost\SqlMapper::genArrayResult
	 * @covers pixelpost\SqlMapper::_castData
	 */
	public function test_genArrayResult_unknown_field()
	{
		$result = array(
			'birthday' => '20120229141516',
			'active'   => '0',
			'foo'      => 'bar',
			'username' => 'Bertrand',
			'count'    => '18',
		);

		$test = array(
			'birth'  => '2012-02-29 14:15:16',
			'active' => false,
			'name'   => 'Bertrand',
			'total'  => 18,
		);

		$me = $this;

		$todo = function(&$result) use ($me)
		{
			$me->assertTrue($result['birth'] instanceOf \DateTime);

			$result['birth'] = $result['birth']->format('Y-m-d H:i:s');
		};

		$this->assertSame($test, $this->object->genArrayResult($result, $todo));
	}
}
