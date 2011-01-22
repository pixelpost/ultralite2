<?php

namespace 'pixelpost';

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
    protected static $_listen = array();

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

        if ( ! isset(self::$_listen[$eventName]) )
        {
            self::$_listen[$eventName] = array();
        }

        self::$_listen[$eventName] = $callback;
    }

    /**
     * Throw an event. Return TRUE if the event have at least one listener else 
     * return FALSE.
     *
     * @param array $datas The datas loaded in the Event class.
     * @return bool
     */
    public static function signal($eventName, array $datas = array())
    {
        Filter::assume_string($eventName);

        if ( ! isset(self::$_listen[$eventName]) ) return false;

        if ( count(self::$_listen[$eventName]) <= 0 ) return false;

        $event = self::create($datas);

        foreach(self::$_listen[$eventName] as $callback)
        {
            if ( ! call_user_func($callback, $event) ) break;
        }

        return true;
    }
}

