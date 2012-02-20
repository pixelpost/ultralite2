<?php

namespace pixelpost\plugins\admin;

use pixelpost;

/**
 * ADMIN routers for pixelpost admin urls.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin implements pixelpost\PluginInterface
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

		pixelpost\Event::register('request.admin',     $self . '::admin_router');

		pixelpost\Event::register('api.admin.version', $api  . '::api_version');

		pixelpost\Event::register('admin.index',       $page . '::page_index');
		pixelpost\Event::register('admin.404',         $page . '::page_404');
		pixelpost\Event::register('admin.api-test',    $page . '::page_api_test');
	}

	/**
	 * Treat a new request comming from event 'request.admin' and check the second
	 * part of the requested URL to find what admin page is asked for.
	 *
	 * This produce an event admin.* where * is replaced by the requested page.
	 * (ex: admin.index)
	 *
	 * In case of non response to an event admin.* the event admin.404 is thrown.
	 *
	 * @param  pixelpost\Event $event
	 * @return bool
	 */
	public static function admin_router(pixelpost\Event $event)
	{
		// retrieve the urls params and assume the two first exists
		$urlParams = $event->request->get_params() + array('admin', 'index');

		// remove 'admin' from the array
		array_shift($urlParams);

		// retrieve the requested admin page
		$page = array_shift($urlParams);

		// event data
		$data = array('request' => $event->request, 'params' => $urlParams);

		// send the signal that an ADMIN method is requested
		$reponseEvent = pixelpost\Event::signal('admin.' . $page, $data);

		// check if there is a response or send a 404 webpage
		if (!$reponseEvent->is_processed())
		{
			pixelpost\Event::signal('admin.404', $data);
			return false;
		}

		// continue processing of request.admin for third party plugins
		return true;
	}
}
