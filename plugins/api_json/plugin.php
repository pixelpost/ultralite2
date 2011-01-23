<?php

namespace pixelpost\plugins\api_json;

use pixelpost;

/**
 * Override the PHP core json_encode by the same method with extra option set.
 *
 * @param mixed $data
 * @return string
 */
function json_encode($data)
{
	return json_encode($data, \JSON_FORCE_OBJECT | \JSON_HEX_TAG  |
			                  \JSON_HEX_APOS     | \JSON_HEX_QUOT |
			                  \JSON_HEX_AMP);
}

/**
 * Decode JSON api request and call the requested controller.
 *
 * Tracks Event :
 *
 * request.api.json
 *
 * Sends Event :
 *
 * api.*
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
 */
class Plugin implements pixelpost\PluginInterface
{

	/**
	 * Sends an json error message and return FALSE.
	 *
	 * @param string $code
	 * @param string $message
	 * @return bool
	 */
	public static function json_error($code, $message)
	{
		pixelpost\is_string($code);
		pixelpost\is_string($message);

		echo json_encode(array(
			'status' => 'error',
			'code' => $code,
			'message' => $message,
		));

		return false;
	}

	/**
	 * Return the last json decode error or an empty string if there is no
	 * error.
	 *
	 * @return string
	 */
	public static function get_json_decode_error()
	{
		switch (json_last_error ())
		{
			default                   : return 'Unkown error';
			case JSON_ERROR_DEPTH     : return 'Max depth.';
			case JSON_ERROR_CTRL_CHAR : return 'Bad characters.';
			case JSON_ERROR_SYNTAX    : return 'Bad syntax.';
			case JSON_ERROR_NONE      : return '';
		}
	}

	/**
	 * Process the 'request.api.json' event, test the JSON request, process it
	 * by sending a new api.* event, get the response and encode it to JSON
	 * before send it to the client.
	 *
	 * If there is an error, the appropriate message is sent. (see:
	 * json_error()).
	 *
	 * All API event need to reply with an 'response' array or object in the
	 * event. Event if it empty array or object.
	 *
	 * @param pixelpost\Event $event
	 * @return false
	 */
	public static function on_api_json_request(pixelpost\Event $event)
	{
		// no json request, we sent an json error message
		if (!$event->request->is_data())
		{
			return self::json_error('empty_data',
					'No JSON data is sent.');
		}

		// get the json requested data, assure that's utf-8 data.
		$request = $event->request->get_data();
		$request = pixelpost\Filter::check_encoding($request);

		// decode the request
		$request = json_decode($request);

		// check if the request is well decoded
		if ('' == $errorMsg = self::get_json_decode_error())
		{
			if (DEBUG)
			{
				return self::json_error('bad_encoding',
						'The JSON request seems invalid format. Debug: ' . $errorMsg);
			}
			else
			{
				return self::json_error('bad_encoding',
						'The JSON request seems invalid format.');
			}
		}

		// check if the request is an object and provide a 'method' property
		if (!is_object($request) || !property_exists($request, 'method'))
		{
			return self::json_error('bad_format',
					'The request need to provide a \'method\’ property');
		}

		// get the requested api method
		$method = trim($request->method);

		// check if the method called is not empty
		if ($method == '')
		{
			return self::json_error('empty_method',
					'The method field is empty.');
		}

		// ok, now process the request by sending the corresponding event.
		$apiMethod = 'api.' . $method;

		$event = pixelpost\Event::signal($apiMethod, array('request' => $request));

		// check if the event is processed (no '404' request)
		if (!$event->is_processed())
		{
			return self::json_error('bad_method',
					"The '$method' requested method is unsupported.");
		}

		// check if there is a response data in the event
		if (!property_exists($event, 'response'))
		{
			return self::json_error('internal_error',
					"Oups ! there is actually a problem.");
		}

		// encode the response data and sent it !
		echo json_encode(array(
			'Status' => 'valid',
			'Data' => $event->response,
		));

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
		pixelpost\Event::register('request.api.json', __NAMESPACE__ . '::on_api_json_request');
	}

}

