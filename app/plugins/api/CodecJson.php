<?php

namespace pixelpost\plugins\api;

use pixelpost;

/**
 * Provide a JSON codec for the plugin 'api'
 *
 * For more information about codec see: CodecInterface
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class CodecJson implements CodecInterface
{

	/**
	 * Decode the request and return an PHP stdClass containing the requested
	 * data.
	 *
	 * @param string
	 * @return stdClass
	 */
	public function decode($request)
	{
		// check $request is a string
		pixelpost\Filter::is_string($request);
		pixelpost\Filter::check_encoding($request);
		
		if (trim($request) == '')
		{
			throw new Exception('no_request', 'Your request is empty.');			
		}

		// check $request is UTF-8 and decode it
		$request = json_decode($request);

		// check if the request is well decoded
		if ('' != $errorMsg = $this->get_json_decode_error())
		{
			if (DEBUG)
			{
				throw new Exception('bad_encoding',
						'The JSON request seems invalid format. Debug: ' . $errorMsg);
			}
			else
			{
				throw new Exception('bad_encoding',
						'The JSON request seems invalid format.');
			}
		}

		return $request;
	}

	/**
	 * Encode a reponse, an array containing all the client data, in the client
	 * format.
	 *
	 * @param array
	 * @return string
	 */
	public function encode(array $response)
	{
		return json_encode($response, JSON_HEX_QUOT);
	}

	/**
	 * Check if there is an JSON decode error and return the corresponding 
	 * error message. Return an empty string if there is no error.
	 *
	 * @return string
	 */
	public function get_json_decode_error()
	{
		switch (json_last_error())
		{
			default                   : return 'Unknown error';
			case JSON_ERROR_DEPTH     : return 'Max depth.';
			case JSON_ERROR_CTRL_CHAR : return 'Bad characters.';
			case JSON_ERROR_SYNTAX    : return 'Bad syntax.';
			case JSON_ERROR_NONE      : return '';
		}
	}

}