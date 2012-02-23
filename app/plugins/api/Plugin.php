<?php

namespace pixelpost\plugins\api;

use
pixelpost\Event,
pixelpost\Filter,
pixelpost\PluginInterface,
pixelpost\plugin\api\Exception as ApiException,
Exception,
stdClass;

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
		Event::register('request.api', '\\' . __CLASS__ . '::api_request');
		Event::register('api.version', '\\' . __CLASS__ . '::api_version');
	}

	public static function api_version(Event $event)
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
	public static function api_request(Event $event)
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
			'- ', API_URL . 'xml/', "\n",
			'- ', API_URL . 'get/', "\n";
			return false;
		}

		// load the concrete format codec
		$codec = self::get_codec($format);

		// start processing the request
		try
		{
			// decode the requested data
			$request = $codec->decode($event->request);

			// we tell that api request is decoded
			$request = Event::signal('api.request.raw', compact('request'))->request;

			// process the request
			$response = self::process($request);

			// get the response and format it
			$response = self::format_valid($response);

			// we tell that api data is decoded
			$response = Event::signal('api.response.raw', compact('response'))->response;
		}
		// tracks API Exception
		catch (ApiException $error)
		{
			$response = self::format_error($error);

			$response = Event::signal('api.error.raw', compact('response'))->response;
		}
		// tracks global Exception
		catch (\Exception $error)
		{
			// a anonymous exception
			$basic = new ApiException('unknown', 'Unknown error.');

			// according the DEBUG mode, we send the real error or not
			$response = self::format_error(DEBUG ? $error : $basic);

			$response = Event::signal('api.error.raw', compact('response'))->response;
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
			case 'get'  : return true;
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
		$message = (DEBUG)
		         ? $error->getMessage() . ': [' . $error->getLine() . ']:' . $error->getFile()
				 : $error->getMessage();

		$code    = ($error instanceof Exception)
		         ? $error->getShortMessage()
			     : $error->getCode();

		return array(
			'status'  => 'error',
			'code'    => $code,
			'message' => $message,
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
	 * @param \stdClass $request The API data in the request
	 */
	public static function process(stdClass $request)
	{
		// check the request is well formated
		if (!property_exists($request, 'method'))
		{
			throw new ApiException('no_method', 'The request need to provide a \'method\' property');
		}

		if (!property_exists($request, 'request'))
		{
			$event->request = new stdClass();
		}

		// get the requested api method
		if ('' == $method = trim($request->method))
		{
			throw new ApiException('empty_method', 'The method field is empty.');
		}

		$request = $request->request;

		// send the signal that an API method is requested
		$event = Event::signal('api.' . $method, compact('request'));

		// check if the event is processed (no '404' request)
		if (!$event->is_processed())
		{
			throw new ApiException('bad_method', "The '$method' requested method is unsupported.");
		}

		// check if there is a response data in the event
		if (!property_exists($event, 'response'))
		{
			throw new ApiException('internal_error', "Oops ! there is actually a problem.");
		}

		return $event->response;
	}

	/**
	 * Make a call to a Api methode
	 *
	 * @param  string $method
	 * @param  array  $request
	 * @return array
	 */
	public static function call_api_method($method, $request)
	{
		$method = 'api.' . $method;

		if (is_array($request)) $request = Filter::array_to_object($request);

		try
		{
			// make the call
			$call = Event::signal($method, compact('request'));

			// check if the call is processed
			if (!$call->is_processed())
			{
				throw new Exception('event `'. $method .'` is not processed');
			}

			// check if the response exists
			if (!isset($call->response))
			{
				throw new Exception('event `'. $method .'` not provide a response');
			}

			// return the response
			return $call->response;
		}
		// handle all pixelpost\plugins\api\Exception can be thrown
		// if you don't the user receive the error message of your internal call
		catch(ApiException $e)
		{
			throw new Exception('event `'. $method .'` thrown an exception', 0, $e);
		}
	}
}

