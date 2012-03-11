<?php

namespace pixelpost\core;

/**
 * Test class for Error.
 */
class ErrorTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers pixelpost\core\Error::create
	 */
	public function test_create_return_instance()
	{
		$error = Error::create();

		$this->assertTrue($error instanceof Error);
		$this->assertTrue($error instanceof \Exception);
	}

	/**
	 * @covers pixelpost\core\Error::create
	 */
	public function test_create_default_code_and_message()
	{
		$error = Error::create();

		$this->assertSame($error->get_default_code(), $error->getCode());
		$this->assertSame($error->get_default_message(), $error->getMessage());
	}

	/**
	 * @covers pixelpost\core\Error::create
	 */
	public function test_create_code_and_message()
	{
		$code    = 36;
		$message = 'This is a test message';

		$error = $this->getMockBuilder('pixelpost\core\Error')
			 ->setMethods(array('get_message_by_code'))
			 ->disableOriginalConstructor()
			 ->getMock();

		$error->expects($this->once())
			  ->method('get_message_by_code')
			  ->with($code)
			  ->will($this->returnValue($message));

		$error->__construct($code);

		$this->assertSame($code,    $error->getCode());
		$this->assertSame($message, $error->getMessage());
	}

	/**
	 * @covers pixelpost\core\Error::create
	 */
	public function test_create_code_and_message_with_parameters()
	{
		$code    = 24;
		$args    = array('foo', 'bar', 'baz');
		$message = 'This is a test message %s1 %s2 %s3';
		$result  = 'This is a test message foo bar baz';

		$error = $this->getMockBuilder('pixelpost\core\Error')
			 ->setMethods(array('get_message_by_code'))
			 ->disableOriginalConstructor()
			 ->getMock();

		$error->expects($this->once())
			  ->method('get_message_by_code')
			  ->with($code)
			  ->will($this->returnValue($message));

		$error->__construct($code, $args);

		$this->assertSame($code, $error->getCode());
		$this->assertSame($result, $error->getMessage());
	}

	/**
	 * @covers pixelpost\core\Error::__toString
	 */
	public function test__toString()
	{
		$error = Error::create();
		$class = get_class($error);
		$result = sprintf('[%s][%d] : %s', $class, $error->get_default_code(), $error->get_default_message());

		$this->assertSame($result, sprintf('%s', $error));
	}

	/**
	 * @covers pixelpost\core\Error::get_default_code
	 */
	public function test_get_default_code()
	{
		$this->assertSame(0, Error::create()->get_default_code());
	}

	/**
	 * @covers pixelpost\core\Error::get_default_message
	 */
	public function test_get_default_message()
	{
		$this->assertSame('Unknown Exception.', Error::create()->get_default_message());
	}

	/**
	 * @covers pixelpost\core\Error::get_message_by_code
	 */
	public function testet_message_by_code()
	{
		$error = Error::create();

		$test1 = 'Filter: Parameter is not a "%s1".';
		$test3 = 'Config: Config file "%s1" not exists.';

		$this->assertSame($test1, $error->get_message_by_code(1));
		$this->assertSame($test3, $error->get_message_by_code(3));
	}
}
