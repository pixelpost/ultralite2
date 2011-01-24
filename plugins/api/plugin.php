<?php

namespace pixelpost\plugins\api;

use pixelpost;

/**
 * Base router for pixelpost api urls.
 *
 * Tracks Event :
 *
 * request.api
 *
 * Sends Event :
 *
 * request.api.json
 * request.api.xml
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
 */
class Plugin implements pixelpost\PluginInterface
{

	public static function on_api_request(pixelpost\Event $event)
	{
		// get the url paramters, the Request class already split the url (using
		// slashes) and get_params() is the array result of the split.
		$urlParams = $event->request->get_params();

		// we skip the first url param wich is the /api/
		// using array_shift() instead of direct array access [0] prevent
		// the error 'unknown array index' in case the array is empty or
		// whatever.
		array_shift($urlParams);

		// we get the format
		// remember, example of an correct url call : /api/json/
		$format = array_shift($urlParams);

		// prepare the event data (we just continue to pass the request class
		// send by the 'request.new' event).
		$eventData = array('request' => $event->request);

		switch ($format)
		{
			case 'json' : pixelpost\Event::signal('request.api.json', $eventData);
				break;
			case 'xml'  : pixelpost\Event::signal('request.api.xml', $eventData);
				break;
			default :
				// how to deal with bad request ?
				// here by just sent a TXT error message in response of the api call
				echo 'ERROR: Bad url format, please use:', "\n",
					 '- ', WEB_URL . API_URL . '/json/', "\n",
					 '- ', WEB_URL . API_URL . '/xml/', "\n";
				break;
		}

		// we order to stop processing of the event request.api by returning false
		return false;
	}

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
		pixelpost\Event::register('request.api',  '\\' . __CLASS__ . '::on_api_request');
	}

}

