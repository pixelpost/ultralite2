<?php

namespace pixelpost\core;

/**
 * Test class for Config.
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var string a temp file name
	 */
	protected $_conffile;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->_conffile = tempnam(realpath(sys_get_temp_dir()), 'pp_ut_config_');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		file_exists($this->_conffile) && unlink($this->_conffile);
	}

	/**
	 * @covers pixelpost\core\Config::create
	 */
	public function test_create_return_instance()
	{
		$conf = Config::create();

		$this->assertTrue($conf instanceof Config);
	}

	/**
	 * @covers pixelpost\core\Config::create
	 */
	public function test_create_return_singleton()
	{
		$conf1 = Config::create();
		$conf2 = Config::create();

		$this->assertTrue($conf1 === $conf2);
	}

	/**
	 * @covers pixelpost\core\Config::load
	 */
	public function test_load()
	{
		$data = '{"foo":"bar","bar":3}';

		file_put_contents($this->_conffile, $data);

		$conf = Config::load($this->_conffile);

		$this->assertTrue($conf instanceof Config);
		$this->assertTrue(property_exists($conf, 'foo'));
		$this->assertTrue(property_exists($conf, 'bar'));
		$this->assertSame('bar', $conf->foo);
		$this->assertSame(3, $conf->bar);

		$this->assertTrue($conf === Config::create());
	}

	/**
	 * @covers pixelpost\core\Config::load
	 */
	public function testSave()
	{
		$data = '{"foo":"bar","bar":3}';

		file_put_contents($this->_conffile, $data);

		$conf = Config::load($this->_conffile);

		$conf->baz = 'test';
		$conf->save();

		$this->assertSame('{"foo":"bar","bar":3,"baz":"test"}', file_get_contents($this->_conffile));
	}
}
