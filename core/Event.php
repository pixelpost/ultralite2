<?php

namespace pixelpost;

/**
 * Event support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
 */
class Event extends \ArrayObject
{

	/**
	 * @var array containts the list of callback indexed by event name.
	 */
	protected static $_listen = array();
	/**
	 * @var bool the event is processed or not
	 */
	protected $_processed = false;

	/**
	 * Create a new Event instance
	 *
	 * @param array $datas
	 */
	public function __construct(array $datas = array())
	{
		parent::__construct($datas, \ArrayObject::ARRAY_AS_PROPS);
	}

	/**
	 * Returns an instance of Event class
	 *
	 * @param array $datas
	 * @return Event
	 */
	public static function create(array $datas = array())
	{
		return new static($datas);
	}

	/**
	 * Register a callback method to an event name. This implies when a event is
	 * thrown (see: signal()), the callback method is called with the event
	 * class argument.
	 *
	 * @param string   $event    The name of the event want to listen.
	 * @param callback $callback The callback method to call when the event is throw
	 */
	public static function register($eventName, $callback)
	{
		Filter::assume_string($eventName);

		if (!isset(self::$_listen[$eventName]))
		{
			self::$_listen[$eventName] = array();
		}

		self::$_listen[$eventName][] = $callback;
	}

	/**
	 * Throw an event. Return the event is thrown which can be contain response
	 * content or modified content.
	 * By default if the event have at least one listener, it's state is set to
	 * processed. A listener can still change this state.
	 *
	 * @param array $datas The datas loaded in the Event class.
	 * @return Event
	 */
	public static function signal($eventName, array $datas = array())
	{
		Filter::assume_string($eventName);

		$event = self::create($datas);

		if (!isset(self::$_listen[$eventName]))
			return $event;

		if (count(self::$_listen[$eventName]) <= 0)
			return $event;

		$event->set_processed();

		foreach (self::$_listen[$eventName] as $callback)
		{
			if (!call_user_func($callback, $event)) break;
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

