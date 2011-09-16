<?php

namespace pixelpost\plugins\admin;

use pixelpost;

/**
 * ADMIN routers for pixelpost admin urls.
 *
 * Tracks Event :
 * - 'request.admin'
 *
 * Sends Event :
 * - 'admin.*'
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
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
		pixelpost\Event::register('request.admin', '\\' . __CLASS__ . '::on_request');
		pixelpost\Event::register('admin.index',   '\\' . __CLASS__ . '::on_page_index');
		pixelpost\Event::register('admin.404',     '\\' . __CLASS__ . '::on_page_404');
		pixelpost\Event::register('admin.api-test','\\' . __CLASS__ . '::on_api_test');
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
	public static function on_request(pixelpost\Event $event)
	{
		// retrieve the urls params and assume the two first exists
		$urlParams = $event->request->get_params() + array('admin', 'index');

		// retrieve the requested admin page
		$page = $urlParams[1];

		// the data send with the event
		$eventData = array('request' => $event->request);

		// send the signal that an ADMIN method is requested
		$reponseEvent = pixelpost\Event::signal('admin.' . $page, $eventData);

		// check if there is a response or send a 404 webpage
		if (!$reponseEvent->is_processed())
		{
			pixelpost\Event::signal('admin.404');
			return false;
		}

		// continue processing of request.admin for third party plugins
		return true;
	}

	public static function on_page_index(pixelpost\Event $event)
	{
		echo "<h1>Welcome On admin page</h1>";
	}

	public static function on_page_404(pixelpost\Event $event)
	{
		echo "<h1>Oops 404 error</h1>";
	}

	public static function on_api_test(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'page' . SEP . 'api_test.php';
	}

}
