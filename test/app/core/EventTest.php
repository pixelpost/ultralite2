<?php

namespace pixelpost\core;

/**
 * Test class for Event.
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
	protected static $event1Called = false;
	protected static $event2Called = false;
	protected static $event3Called = false;

	public static function on_event1(Event $event)
	{
		$event->unit_test = true;
		self::$event1Called = true;
	}

	public static function on_event2(Event $event)
	{
		$event->unit_test = 'bar';
		self::$event2Called = true;
		return true;
	}

	public static function on_event3(Event $event)
	{
		$event->unit_test = 'foo';
		self::$event3Called = true;
		return false;
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		self::$event1Called = false;
		self::$event2Called = false;
		self::$event3Called = false;
	}

	/**
	 * @covers pixelpost\core\Event::create
	 */
	public function test_create_return_instance()
	{
		$event = Event::create();

		$this->assertTrue($event instanceof Event);
	}

	/**
	 * @covers pixelpost\core\Event::register
	 * @covers pixelpost\core\Event::signal
	 */
	public function test_register_and_signal()
	{
		$this->assertFalse(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);

		$eventName = 'my-unit-test-event-1';

		$event = Event::signal($eventName);

		$this->assertTrue($event instanceof Event);
		$this->assertFalse($event->is_processed());
		$this->assertFalse(property_exists($event, 'unit_test'));
		$this->assertFalse(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);

		Event::register($eventName, __CLASS__ . '::on_event1');

		$event = Event::signal($eventName);

		$this->assertTrue($event instanceof Event);
		$this->assertTrue($event->is_processed());
		$this->assertTrue(property_exists($event, 'unit_test'));
		$this->assertTrue($event->unit_test);
		$this->assertTrue(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);
	}

	/**
	 * @covers pixelpost\core\Event::register
	 * @covers pixelpost\core\Event::signal
	 */
	public function test_register_and_signal_normal_order_call()
	{
		$this->assertFalse(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);

		$eventName = 'my-unit-test-event-2';

		$event = Event::signal($eventName);

		$this->assertTrue($event instanceof Event);
		$this->assertFalse($event->is_processed());
		$this->assertFalse(property_exists($event, 'unit_test'));

		$this->assertFalse(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);

		Event::register($eventName, __CLASS__ . '::on_event1');
		Event::register($eventName, __CLASS__ . '::on_event2');

		$event = Event::signal($eventName);

		$this->assertTrue($event instanceof Event);
		$this->assertTrue($event->is_processed());
		$this->assertTrue(property_exists($event, 'unit_test'));
		$this->assertSame('bar', $event->unit_test);

		$this->assertTrue(self::$event1Called);
		$this->assertTrue(self::$event2Called);
		$this->assertFalse(self::$event3Called);
	}

	/**
	 * @covers pixelpost\core\Event::register
	 * @covers pixelpost\core\Event::signal
	 */
	public function test_register_and_signal_event_break_call_chain()
	{
		$this->assertFalse(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);

		$eventName = 'my-unit-test-event-3';

		$event = Event::signal($eventName);

		$this->assertTrue($event instanceof Event);
		$this->assertFalse($event->is_processed());
		$this->assertFalse(property_exists($event, 'unit_test'));

		$this->assertFalse(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);

		Event::register($eventName, __CLASS__ . '::on_event1');
		Event::register($eventName, __CLASS__ . '::on_event3');
		Event::register($eventName, __CLASS__ . '::on_event2');

		$event = Event::signal($eventName);

		$this->assertTrue($event instanceof Event);
		$this->assertTrue($event->is_processed());
		$this->assertTrue(property_exists($event, 'unit_test'));
		$this->assertSame('foo', $event->unit_test);

		$this->assertTrue(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertTrue(self::$event3Called);
	}

	/**
	 * @covers pixelpost\core\Event::register
	 * @covers pixelpost\core\Event::signal
	 */
	public function test_register_and_signal_priority()
	{
		$this->assertFalse(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);

		$eventName = 'my-unit-test-event-4';

		$event = Event::signal($eventName);

		$this->assertTrue($event instanceof Event);
		$this->assertFalse($event->is_processed());
		$this->assertFalse(property_exists($event, 'unit_test'));

		$this->assertFalse(self::$event1Called);
		$this->assertFalse(self::$event2Called);
		$this->assertFalse(self::$event3Called);

		Event::register($eventName, __CLASS__ . '::on_event1', 120);
		Event::register($eventName, __CLASS__ . '::on_event2', 5);
		Event::register($eventName, __CLASS__ . '::on_event3', 120);

		$event = Event::signal($eventName);

		$this->assertTrue($event instanceof Event);
		$this->assertTrue($event->is_processed());
		$this->assertTrue(property_exists($event, 'unit_test'));
		$this->assertSame('foo', $event->unit_test);

		$this->assertTrue(self::$event1Called);
		$this->assertTrue(self::$event2Called);
		$this->assertTrue(self::$event3Called);
	}

	/**
	 * @covers pixelpost\core\Event::register
	 * @covers pixelpost\core\Event::signal
	 */
	public function test_signal_data()
	{
		$eventName = 'my-unit-test-event-5';

		$event = Event::signal($eventName, array('foo' => 123, 'bar' => 256));

		$this->assertTrue(property_exists($event, 'foo'));
		$this->assertTrue(property_exists($event, 'bar'));
		$this->assertSame(123, $event->foo);
		$this->assertSame(256, $event->bar);
	}

	/**
	 * @covers pixelpost\core\Event::set_processed
	 * @covers pixelpost\core\Event::is_processed
	 */
	public function test_set_and_is_processed()
	{
		$event = Event::create();
		$this->assertFalse($event->is_processed());

		$event->set_processed();
		$this->assertTrue($event->is_processed());

		$event->set_processed(false);
		$this->assertFalse($event->is_processed());

		$event->set_processed(true);
		$this->assertTrue($event->is_processed());
	}

}
