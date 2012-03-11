<?php

namespace pixelpost\core;

/**
 * Test class for TemplateLoop.
 */
class TemplateLoopTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers pixelpost\core\TemplateLoop::__construct
	 */
	public function test__construct()
	{
		$array = array('foo', 2, 'bar', 4);

		$loop = new TemplateLoop($array);

		$this->assertSame(4, $loop->length);
		$this->assertSame(false, $loop->first);
		$this->assertSame(false, $loop->last);
	}

	/**
	 * @covers pixelpost\core\TemplateLoop::__construct
	 */
	public function test__construct_array_is_an_object()
	{
		$object = new \ArrayObject(array('foo', 'bar', 'baz'));

		$loop = new TemplateLoop($object);

		$this->assertSame(3, $loop->length);
		$this->assertSame(false, $loop->first);
		$this->assertSame(false, $loop->last);
	}

	/**
	 * @covers pixelpost\core\TemplateLoop::iterate
	 */
	public function test_iterate()
	{
		$array = array('foo', 'bar', 'baz');

		$loop = new TemplateLoop($array);

		$this->assertSame(3, $loop->length);
		$this->assertSame(false, $loop->first);
		$this->assertSame(false, $loop->last);

		$loop->iterate();

		$this->assertSame(1, $loop->index);
		$this->assertSame(0, $loop->index0);
		$this->assertSame(3, $loop->revindex);
		$this->assertSame(2, $loop->revindex0);
		$this->assertSame(3, $loop->length);
		$this->assertSame(true, $loop->first);
		$this->assertSame(false, $loop->last);

		$loop->iterate();

		$this->assertSame(2, $loop->index);
		$this->assertSame(1, $loop->index0);
		$this->assertSame(2, $loop->revindex);
		$this->assertSame(1, $loop->revindex0);
		$this->assertSame(3, $loop->length);
		$this->assertSame(false, $loop->first);
		$this->assertSame(false, $loop->last);

		$loop->iterate();

		$this->assertSame(3, $loop->index);
		$this->assertSame(2, $loop->index0);
		$this->assertSame(1, $loop->revindex);
		$this->assertSame(0, $loop->revindex0);
		$this->assertSame(3, $loop->length);
		$this->assertSame(false, $loop->first);
		$this->assertSame(true, $loop->last);
	}

}
