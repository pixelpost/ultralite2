<?php

namespace pixelpost\plugins\api;

use pixelpost\Filter,
	pixelpost\Request;

/**
 * Provide a GET/POST codec for the plugin 'api'
 *
 * For more information about codec see: CodecInterface
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class CodecGet
{

	protected $returnType = 'json';

	/**
	 * Decode the request and return an PHP stdClass containing the requested
	 * data.
	 *
	 * @param  pixelpost\Request
	 * @return stdClass
	 */
	public function decode(Request $request)
	{
		// Retrieve the url data
		$params = $request->get_params();

		// retrieve posted or query data
		$data = $request->is_post() ? $request->get_post() : $request->get_query();

		// we skip the first and second url param which is 'api' and 'get'
		array_shift($params);
		array_shift($params);

		// the extra params joined to method
		$response = array();

		// set the request data
		$response['request'] = $data;

		// the third url param is the method requested
		$response['method'] = array_shift($params);

		// the third url param is the return type
		$this->returnType = array_shift($params);

		// extract extra parameters
		foreach($params as $param)
		{
			if (false !== $pos = strpos($param, ':'))
			{
				list($key, $val) = explode(':', $param, 2);

				$key = Filter::check_encoding(urldecode($key));
				$val = Filter::check_encoding(urldecode($val));

				$response[$key] = $val;
			}
		}

		return Filter::array_to_object($response);
	}

	/**
	 * Encode a reponse, an array containing all the client data, in the client
	 * format.
	 *
	 * @param  array
	 * @return string
	 */
	public function encode(array $response)
	{
		switch ($this->returnType)
	    {
			case 'xml' : $codec = new CodecXml();  break;
			default    : $codec = new CodecJson(); break;
		}

		return $codec->encode($response);
	}
}
