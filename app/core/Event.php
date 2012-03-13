<?php

namespace pixelpost\core;

use ArrayObject;

/**
 * Event support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Event extends ArrayObject
{

	/**
	 * @var array containts the list of callback indexed by event name and priority.
	 */
	protected static $_listen  = array();
	protected static $_ordered = array();

	/**
	 * @var bool the event is processed or not
	 */
	protected $_processed = false;
	/**
	 * @var string the event name
	 */
	protected $_name      = '';

	/**
	 * Create a new Event instance
	 *
	 * @param array $data
	 */
	public function __construct(array $data = array())
	{
		parent::__construct($data, ArrayObject::ARRAY_AS_PROPS);
	}

	/**
	 * Returns an instance of Event class
	 *
	 * @param  array $data
	 * @return pixelpost\core\Event
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
	 * Note: If you want register two or more listener, see `register_list` method.
	 *
	 * @param string   $name     The name of the event want to listen.
	 * @param callback $callback The callback method to call when the event is thrown
	 * @param int      $priotiy  The default priority is set to 100, less is more prior
	 */
	public static function register($name, $callback, $priority = 100)
	{
		$name = strval($name);

		isset(static::$_listen[$name]) or static::$_listen[$name] = array();

		while (isset(static::$_listen[$name][$priority])) ++$priority;

		static::$_listen[$name][$priority] = $callback;
		static::$_ordered[$name] = false;
	}

	/**
	 * Register a list of callback method to events name. See `register()` method
	 * The list is an array where values are `event`, `callback` and optionnaly
	 * `priority`.
	 *
	 * Note: If you want register two or more listener, this method is the fastest one.
	 *
	 * @param array $event The name of the event want to listen.
	 */
	public static function register_list(array $listeners)
	{
		foreach ($listeners as $l)
		{
			list($name, $callback, $priority) = ($l + array(2 => 100));

			isset(static::$_listen[$name]) or static::$_listen[$name] = array();

			while (isset(static::$_listen[$name][$priority])) ++$priority;

			static::$_listen[$name][$priority] = $callback;
			static::$_ordered[$name] = false;
		}
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
	 * The event is auto forged with submitted array $data.
	 *
	 * @param  array $data The data loaded in the Event class.
	 * @return pixelpost\core\Event
	 */
	public static function signal($eventName, array $data = array())
	{
		return static::raise($eventName, static::create($data));
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
	 * @param  pixelpost\core\Event $event The event will be thrown.
	 * @return pixelpost\core\Event
	 */
	public static function raise($eventName, Event $event)
	{
		$eventName = strval($eventName);

		$event->set_name($eventName);

		if (!isset(static::$_listen[$eventName])) return $event;

		if (count(static::$_listen[$eventName]) <= 0) return $event;

		if (!static::$_ordered[$eventName])
		{
			ksort(static::$_listen[$eventName]);
			static::$_ordered[$eventName] = true;
		}

		$event->set_processed();

		foreach (static::$_listen[$eventName] as $callback)
		{
			if (call_user_func($callback, $event) === false) break;
		}

		return $event;
	}

	/**
	 * Change the 'processed' state of an event.
	 *
	 * @param  bool $processed By default the value is TRUE
	 * @return pixelpost\core\Event
	 */
	public function set_processed($processed = true)
	{
		Filter::assume_bool($processed);

		$this->_processed = $processed;

		return $this;
	}

	/**
	 * Change the event name.
	 *
	 * @param  string $name The new event name
	 * @return pixelpost\core\Event
	 */
	public function set_name($name)
	{
		$this->_name = $name;
	}

	/**
	 * Return the event name.
	 *
	 * @return string
	 */
	public function get_name()
	{
		return $this->_name;
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

	/**
	 * Throw this event to another event name, return if the redirection is_processed
	 *
	 * @return bool
	 */
	public function redirect($to)
	{
		Filter::assume_string($to);

		// backup internal state
		$name = $this->_name;
		$proc = $this->_processed;

		// by default the redirection is not processed
		$this->_processed = false;

		static::raise($to, $this);

		// redirect worked ?
		$result = $this->_processed;

		// restore internal state
		$this->_processed = $proc;
		$this->_name      = $name;

		// return if redirection worked
		return $result;
	}
}
