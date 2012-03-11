<?php

namespace pixelpost\core;

/**
 * Test class for Db.
 */
class DbTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var string A tep filename
	 */
	protected $_dababase = '';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->_dababase = realpath(sys_get_temp_dir()) . SEP . 'pp_ut_db_sqlite3_' . uniqid();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		file_exists($this->_dababase) and unlink($this->_dababase);
	}


	/**
	 * @covers pixelpost\core\Db::create
	 */
	public function test_create_return_value()
	{
		Db::set_database_file($this->_dababase);

		$db = Db::create();

		$this->assertTrue($db instanceof Db);
	}

	/**
	 * @covers pixelpost\core\Db::create
	 */
	public function test_create_return_singleton()
	{
		Db::set_database_file($this->_dababase);

		$db1 = Db::create();
		$db2 = Db::create();

		$this->assertTrue($db1 === $db2);
	}

	/**
	 * @covers pixelpost\core\Db::escape
	 */
	public function test_escape()
	{
		$this->assertSame("'test '' test'", Db::escape("test ' test"));
	}

	/**
	 * @covers pixelpost\core\Db::date_serialize
	 */
	public function test_date_serialize()
	{
		$date = new \DateTime('2002-06-10 08:34:23', new \DateTimeZone('UTC'));

		$this->assertSame('20020610083423', Db::date_serialize($date));
	}

	/**
	 * @todo Implement testDate_unserialize().
	 */
	public function test_date_unserialize()
	{
		$date = Db::date_unserialize('20020610083423');

		$this->assertTrue($date instanceof \DateTime);

		$this->assertSame('2002-06-10T08:34:23+00:00', $date->format(DATE_RFC3339));
	}
}

