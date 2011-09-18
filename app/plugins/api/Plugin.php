<?php

namespace pixelpost\plugins\api;

use pixelpost;

/**
 * API router for pixelpost api urls.
 *
 * Tracks Event :
 * - 'api.version'
 * - 'request.api'
 *
 * Sends Event :
 * - 'api.*'
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
		pixelpost\Event::register('request.api', '\\' . __CLASS__ . '::on_api_request');		
		pixelpost\Event::register('api.version', '\\' . __CLASS__ . '::api_version');		
	}
	
	public static function api_version(pixelpost\Event $event)
	{
		$event->response = array('version' => self::version());
	}

	/**
	 * Treat a new request comming from event 'request.api' and check the second
	 * part of the requested URL to find what the format of response is asked
	 * for (JSON, XML).
	 * When format is found, this call for a request treatment (see: process())
	 *
	 * In case of bad url parameter, the method return an error text message
	 * starting by the fifth character ERROR. this allow to be can be tracked
	 * by the client.
	 *
	 * @param  pixelpost\Event $event
	 * @return bool
	 */
	public static function on_api_request(pixelpost\Event $event)
	{
		// Retrieve the url paramters
		$urlParams = $event->request->get_params();

		// we skip the first url param which is 'api'
		array_shift($urlParams);

		// we work on the second url param
		$format = array_shift($urlParams);

		// validate the format or raise an error
		if (self::is_valid_format($format) == false)
		{
			echo 'ERROR: Bad url format, please use:', "\n",
			'- ', API_URL . 'json/', "\n",
			'- ', API_URL . 'xml/', "\n";
			return false;
		}

		// load the concrete format codec
		$codec = self::get_codec($format);

		// start processing the request
		try
		{
			// decode the requested data
			$request = $codec->decode($event->request->get_data());

			// process the request
			$response = self::process($request, $event->request);

			// get the response and format it
			$response = self::format_valid($response);
		}
		// tracks API Exception
		catch (Exception $error)
		{
			$response = self::format_error($error);
		}
		// tracks global Exception
		catch (\Exception $error)
		{
			// a anonymous exception
			$basic = new Exception('unknown', 'Unknown error.');

			// according the DEBUG mode, we send the real error or not
			$response = self::format_error(DEBUG ? $error : $basic);
		}

		// send the response to the client
		echo $codec->encode($response);

		// stop processing of the event request.api by returning false
		return false;
	}

	/**
	 * Check if the requested encoding format is valid
	 *
	 * @param string $format
	 * @return bool
	 */
	public static function is_valid_format($format)
	{
		switch ($format)
		{
			case 'json' : return true;
			case 'xml'  : return true;
			default     : return false;
		}
	}

	/**
	 * Load the codec of an api request. The codec is a class can decode
	 * request and encode response in the same format.
	 *
	 * @param string $format
	 * @return CodecInterface
	 */
	public static function get_codec($format)
	{
		// change word like 'xml-rpc' in camelcase: 'CodecXmlRpc'
		$className = 'Codec' . str_replace(' ', '', ucwords(str_replace('-', ' ', $format)));

		// sometimes php sucks and is unable to find a class in the same
		// namespace when the class name is dynamic
		// so we need to provide the full class name.
		$className = __NAMESPACE__ . '\\' . $className;
		
		return new $className();
	}

	/**
	 * Change an exception to an array which need to be encoded by a codec to be
	 * send to the final user
	 *
	 * @param \Exception $error
	 * @return array
	 */
	public static function format_error(\Exception $error)
	{
		return array(
			'status'  => 'error',
			'code'    => ($error instanceof Exception) ? $error->getShortMessage() : $error->getCode(),
			'message' => $error->getMessage(),
		);			
	}

	/**
	 * Format an response for the API user.
	 *
	 * @param string|array|StdClass $response
	 * @return array
	 */
	public static function format_valid($response)
	{
		return array(
			'status'   => 'valid',
			'response' => $response,
		);
	}

	/**
	 * Check an api request is well formated and process the request:
	 * 
	 * 1. send the api event corresponding to the method request
	 * 2. return the event response if exists
	 *
	 * @param \stdClass          $request The API data in the request
	 * @param \pixelpost\Request $http    The HTTP request provided by request.new
	 */
	public static function process(\stdClass $request, \pixelpost\Request $http)
	{		
		// create the data who are propagated int the event
		$datas = array('request' => $request->request, 'http_request' => $http);

		// we send an the significate the api data is decoded
		$event = pixelpost\Event::signal('request.api.decoded', $datas);
		
		// whatever if event is processed or not, we just retrieve the request
		$request = $event->request;
		
		// check the request is well formated
		if (!property_exists($request, 'method'))
		{
			throw new Exception('bad_format', 'The request need to provide a \'method\' property');
		}

		if (!property_exists($request, 'request'))
		{
			throw new Exception('bad_format', 'The request need to provide a \'request\' property');
		}

		// get the requested api method
		if ('' == $method = trim($request->method))
		{
			throw new Exception('empty_method', 'The method field is empty.');
		}
		
		// create the data who are propagated int the event
		$datas = array('request' => $request->request, 'http_request' => $http);

		// send the signal that an API method is requested
		$event = pixelpost\Event::signal('api.' . $method, $datas);

		// check if the event is processed (no '404' request)
		if (!$event->is_processed())
		{
			throw new Exception('bad_method', "The '$method' requested method is unsupported.");
		}

		// check if there is a response data in the event
		if (!property_exists($event, 'response'))
		{
			throw new Exception('internal_error', "Oops ! there is actually a problem.");
		}

		return $event->response;
	}

}

