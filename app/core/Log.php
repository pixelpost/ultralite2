<?php

namespace pixelpost\core;

/**
 * Log support
 *
 * @copyright  2012 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Log
{
	/**
	 * Log a debug message
	 * This method follow the same scheme as sprintf function.
	 *
	 * @return true
	 */
	public static function debug()
	{
		return static::log('debug', func_get_args());
	}

	/**
	 * Log a info message
	 * This method follow the same scheme as sprintf function.
	 *
	 * @return true
	 */
	public static function info()
	{
		return static::log('info', func_get_args());
	}

	/**
	 * Log a warning message
	 * This method follow the same scheme as sprintf function.
	 *
	 * @return true
	 */
	public static function warning()
	{
		return static::log('warning', func_get_args());
	}

	/**
	 * Log a error message
	 * This method follow the same scheme as sprintf function.
	 *
	 * @return true
	 */
	public static function error()
	{
		return static::log('error', func_get_args());
	}

	/**
	 * Log a message in the file LOG_FILE
	 *
	 * @param string $level
	 * @param string $args
	 * @return true
	 */
	protected static function log($level, $args)
	{
		if (count($args) == 0) Error::create(10, array($level));

		$message = array_shift($args);

		if (count($args) > 0) $message = vsprintf($message, $args);

		$date    = date(DATE_RFC2822);
		$process = PROCESS_ID;
		$fmt     = "[%s] [%s] [%s] %s\n";

		error_log(sprintf($fmt, $date, $process, $level, $message), 3, LOG_FILE);

		return true;
	}
}