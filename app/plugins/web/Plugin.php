<?php

namespace pixelpost\plugins\web;

use pixelpost\plugins\router\Plugin as Router,
	pixelpost\PluginInterface,
	pixelpost\Event;

/**
 * WEB routers for pixelpost web urls.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin implements PluginInterface
{
	public static function version()
	{
		return '0.0.1';
	}

	public static function depends()
	{
		return array('router' => '0.0.1');
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
		$self = '\\' . __CLASS__;
		$api  = '\\' . __NAMESPACE__ . '\\Api';
		$page = '\\' . __NAMESPACE__ . '\\Page';

		Event::register('request.web',         $self . '::web_router');

		Event::register('web.index',           $page . '::page_index');
		Event::register('web.404',             $page . '::page_404');
	}

	/**
	 * Treat a new request coming from event 'request.web' and check the second
	 * part of the requested URL to find what web page is asked for.
	 *
	 * This produce an event web.* where * is replaced by the requested page.
	 * (ex: web.index)
	 *
	 * In case of non response to an event web.* the event web.404 is thrown.
	 *
	 * @param  pixelpost\Event $event
	 * @return bool
	 */
	public static function web_router(Event $event)
	{
		$event->set_name('web');

		Router::route($event);

		if (!$event->is_processed())
		{
			$event->redirect('web.404');
			return false;
		}

		$event->set_name('request.web');
	}
}
