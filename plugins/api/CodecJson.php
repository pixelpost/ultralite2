<?php

namespace pixelpost\plugins\api;

use pixelpost;

/**
 * Provide a JSON codec for the plugin 'api'
 *
 * For more information about codec see: CodecInterface
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
		pixelpost\Filter::param_string($request);

		// check $request is UTF-8 and decode it
		$request = json_decode(pixelpost\Filter::check_encoding($request));

		// check if the request is well decoded
		if ('' == $errorMsg = $this->get_json_decode_error())
		{
			if (pixelpost\DEBUG)
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
	 * Encode a reponse, an array containing all the client datas, in the client
	 * format.
	 *
	 * @param array
	 * @return string
	 */
	public function encode(array $response)
	{
		return json_encode($response);
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
