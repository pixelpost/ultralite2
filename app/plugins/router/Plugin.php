<?php

namespace pixelpost\plugins\router;

use pixelpost\core\Config,
	pixelpost\core\PluginInterface,
	pixelpost\core\Event;

/**
 * Base router for pixelpost.
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
		return array();
	}

	public static function install()
	{
		$configuration = '{
			"api"   : "api",
			"admin" : "admin"
		}';

		$conf = Config::create();
		$conf->plugin_router = json_decode($configuration);
		$conf->save();

		return true;
	}

	public static function uninstall()
	{
		$conf = Config::create();

		unset($conf->plugin_router);

		$conf->save();

		return true;
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		$conf = Config::create();

		define('API_URL',   WEB_URL . $conf->plugin_router->api   . '/', true);
		define('ADMIN_URL', WEB_URL . $conf->plugin_router->admin . '/', true);

		Event::register('request.new', __CLASS__ . '::request_new');
	}

	public static function request_new(Event $event)
	{
		// retrieve the configuration plugin
		$conf    = Config::create()->plugin_router;

		// get the request and its url paramters
		$request = $event->request;
		$params  = $request->get_params() + array('index');

		// Make a choice between ADMIN, API, WEB.
		// ADMIN and API base url are in the configuration file,
		// other words is the WEB interface.
		switch (current($params))
		{
			case $conf->admin : $event_name = 'request.admin'; array_shift($params); break;
			case $conf->api   : $event_name = 'request.api';   array_shift($params); break;
			default           : $event_name = 'request.web';   break;
		}

		// send the event
		Event::signal($event_name, compact('request', 'params'));

		// we order to stop processing of the event request.new
		return false;
	}

	/**
	 * Route automatically an event by following the params.
	 *
	 * The event need data:
	 * - mixed request
	 * - array params
	 */
	public static function route(Event $event)
	{
		$route = $event->get_name() . '.' . (array_shift($event->params) ?: 'index');

		$event->set_processed($event->redirect($route));
	}
}