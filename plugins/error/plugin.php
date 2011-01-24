<?php

namespace pixelpost\plugins\error;

use pixelpost;

/**
 * Error management for pixelpost.
 *
 * Tracks Event :
 *
 * error.new
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
 */
class Plugin implements pixelpost\PluginInterface
{

	public static function version()
	{
		return '0.0.1';
	}

	public static function install()
	{
		return true;
	}

	public static function uninstall()
	{
		return true;
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		pixelpost\Event::register('error.new', '\\' . __CLASS__ . '::on_error');
	}

	public static function on_error(pixelpost\Event $event)
	{
		$error = $event->exception;

		// if we use pixelpost\DEBUG or pixelpost\SEP
		// php actually says that's constant are unknow. I think this is a
		// behaviour cause by the fact the set_exeption_handler() is in the
		// pixelpost namespace.
		if (DEBUG)
		{
			include __DIR__ . SEP . 'template' . SEP . 'error_debug.php';
		}
		else
		{
			include __DIR__ . SEP . 'template' . SEP . 'error.php';
		}

		// we need to stop the script, if not PHP understand that the exception
		// was not caugth. And raise an error:
		// PHP Fatal error: Exception thrown without a stack frame in Unknown on
		// line 0
		exit();
	}
}