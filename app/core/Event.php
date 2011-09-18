<?php

namespace pixelpost;

/**
 * Event support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Event extends \ArrayObject
{

	/**
	 * @var array containts the list of callback indexed by event name
	 * and priority.
	 */
	protected static $_listen  = array();
	protected static $_ordered = array();

	/**
	 * @var bool the event is processed or not
	 */
	protected $_processed = false;

	/**
	 * Create a new Event instance
	 *
	 * @param array $data
	 */
	public function __construct(array $data = array())
	{
		parent::__construct($data, \ArrayObject::ARRAY_AS_PROPS);
	}

	/**
	 * Returns an instance of Event class
	 *
	 * @param array $data
	 * @return Event
	 */
	public static function create(array $data = array())
	{
		return new static($data);
	}

	/**
	 * Register a callback method to an event name. This implies when a event is
	 * thrown (see: signal()), the callback method is called with the event
	 * class argument.
	 *
	 * @param string   $event    The name of the event want to listen.
	 * @param callback $callback The callback method to call when the event is throw
	 * @param int      $priotiy  The default priority is set to 100, less is highter
	 */
	public static function register($eventName, $callback, $priority = 100)
	{
		Filter::assume_string($eventName);

		if (!isset(self::$_listen[$eventName]))
		{
			self::$_listen[$eventName] = array();
		}

		// If an event with the same priority exists, increase the priority by one.
		if (isset(self::$_listen[$eventName][$priority]))
		{
			return self::register($eventName, $callback, $priority++);
		}

		self::$_listen[$eventName][$priority] = $callback;
		self::$_ordered[$eventName] = false;
	}

	/**
	 * Throw an event. Return the event is thrown which can be contain response
	 * content or modified content.
	 * By default if the event have at least one listener, it's state is set to
	 * processed. A listener can still change this state.
	 *
	 * A listener may explicitly return false to stop the propagation of the
	 * event to other listeners.
	 *
	 * @param  array $data The data loaded in the Event class.
	 * @return Event
	 */
	public static function signal($eventName, array $data = array())
	{
		Filter::assume_string($eventName);

		$event = self::create($data);

		if (!isset(self::$_listen[$eventName])) return $event;

		if (count(self::$_listen[$eventName]) <= 0) return $event;

		if (!self::$_ordered[$eventName])
		{
			ksort(self::$_listen[$eventName]);
			self::$_ordered[$eventName];
		}

		$event->set_processed();

		foreach (self::$_listen[$eventName] as $callback)
		{
			if (call_user_func($callback, $event) === false) break;
		}

		return $event;
	}

	/**
	 * Change the 'processed' state of an event.
	 *
	 * @param bool $processed By default the value is TRUE
	 */
	public function set_processed($processed = true)
	{
		Filter::assume_bool($processed);

		$this->_processed = $processed;
	}

	/**
	 * Return if the event is processed by a listener.
	 *
	 * @return bool
	 */
	public function is_processed()
	{
		return $this->_processed;
	}

}

