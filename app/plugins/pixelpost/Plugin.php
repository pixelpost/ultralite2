<?php

namespace pixelpost\plugins\pixelpost;

use pixelpost\core\Config,
	pixelpost\core\PluginInterface,
	pixelpost\core\Template,
	pixelpost\core\Request,
	pixelpost\core\Event;

/**
 * Base for pixelpost.
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
		return VERSION;
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
		$conf->pixelpost = json_decode($configuration);
		$conf->save();

		return true;
	}

	public static function uninstall()
	{
		$conf = Config::create();

		unset($conf->pixelpost);

		$conf->save();

		return true;
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		Event::register_list(array(
			array('app.start',                       __CLASS__ . '::startup'),
			array('http.new',                        __CLASS__ . '::http'),
			array('error.new',                       __CLASS__ . '::error'),
			array('admin.settings.plugin.pixelpost', __CLASS__ . '::about'),
		));
	}

	public static function about(Event $event)
	{
		Template::create()->publish('pixelpost/tpl/about.tpl');
	}

	public static function startup(Event $event)
	{
		// retrieve the configuration plugin
		$conf = Config::create();

		// create usefull constant
		define('API_URL',   WEB_URL . $conf->pixelpost->api   . '/', true);
		define('ADMIN_URL', WEB_URL . $conf->pixelpost->admin . '/', true);

		if (CLI)
		{
			$argc = $_SERVER['argc'];
			$argv = $_SERVER['argv'];

			// send the event as we have a new cli request
			assert('pixelpost\core\Log::info("(pixelpost) Handle: php %s", implode(" ", $argv))');

			$event = Event::signal('cli.new', compact('argc', 'argv'));
		}
		else
		{
			// parse the http request
			assert('pixelpost\core\Log::info("(pixelpost) Web request creation")');

			$request = Request::create()->set_userdir(Config::create()->userdir)->auto();

			// send the event as we have a new http request
			assert('pixelpost\core\Log::info("(pixelpost) Handle: %s", $request->get_request_url())');

			$event = Event::signal('http.new', compact('request'));
		}
	}

	public static function http(Event $event)
	{
		// retrieve the configuration plugin
		$conf    = Config::create()->pixelpost;

		// get the request and its url paramters
		$request = $event->request;
		$params  = $request->get_params() + array('index');

		// Make a choice between ADMIN, API, WEB.
		// ADMIN and API base url are in the configuration file,
		// other words is the WEB interface.
		switch (current($params))
		{
			case $conf->admin : $event_name = 'http.admin'; array_shift($params); break;
			case $conf->api   : $event_name = 'http.api';   array_shift($params); break;
			default           : $event_name = 'http.web';   break;
		}

		// send the event
		Event::signal($event_name, compact('request', 'params'));

		// we order to stop processing of the event http.new
		return false;
	}

	public static function error(Event $event)
	{
		$error = $event->exception;

		if (DEBUG) include __DIR__ . '/tpl/error_debug.tpl';
		else       include __DIR__ . '/tpl/error.tpl';

		// we need to stop the script, if not, PHP understand that the exception
		// was not caugth. And raise an error:
		// PHP Fatal error: Exception thrown without a stack frame in Unknown on
		// line 0
		exit();
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