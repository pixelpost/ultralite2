<?php

namespace pixelpost\plugins\router;

use pixelpost;

/**
 * Base router for pixelpost.
 *
 * Tracks Event :
 *
 * router.version
 * request.new
 *
 * Sends Event :
 *
 * request.api
 * request.admin
 * request.web
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
		return array();
	}	
	
	public static function install()
	{
		$configuration = '{
			"api"   : "api", 
			"admin" : "admin"
		}';
		
		$conf = pixelpost\Config::create();
		$conf->plugin_router = json_decode($configuration);
		$conf->save();
		
		return true;
	}

	public static function uninstall()
	{
		$conf = pixelpost\Config::create();
		
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
		$conf = pixelpost\Config::create();
		
		define('API_URL',   WEB_URL . $conf->plugin_router->api   . '/', true);
		define('ADMIN_URL', WEB_URL . $conf->plugin_router->admin . '/', true);
		
		pixelpost\Event::register('router.version', '\\' . __CLASS__ . '::router_version');
		pixelpost\Event::register('request.new',    '\\' . __CLASS__ . '::on_request');
	}

	public static function router_version(pixelpost\Event $event)
	{
		$event->response = array('version' => self::version());
	}

	public static function on_request(pixelpost\Event $event)
	{
		// retrieve the configuration
		$conf = pixelpost\Config::create();

		// get the url paramters, the Request class already split the url (using
		// slashes) and get_params() is the array result of the split.
		$urlParams = $event->request->get_params();

		// no parameter in the url, we add a virtual one
		if (count($urlParams) == 0) $urlParams[] = 'index';

		// prepare the event data (we just continu to pass the request class
		// send by the 'request.new' event).
		$eventData = array('request' => $event->request);

		// make a choice between ADMIN, API, WEB.
		// ADMIN and API base url are sent in the configuration file
		// other words is the WEB interface.
		switch (array_shift($urlParams))
		{
			case $conf->plugin_router->admin :
				pixelpost\Event::signal('request.admin', $eventData);
				break;
			case $conf->plugin_router->api :
				pixelpost\Event::signal('request.api', $eventData);
				break;
			default :
				pixelpost\Event::signal('request.web', $eventData);
				break;
		}

		// we order to stop processing of the event request.new by returning
		// false
		return false;
	}

}
