<?php

namespace pixelpost\plugins\api;

use pixelpost;

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
	public function decode(pixelpost\Request $request)
	{
		// Retrieve the url paramters
		$urlParams = $request->get_params();

		// we skip the first and second url param which is 'api' and 'get'
		array_shift($urlParams);
		array_shift($urlParams);

		// the third url param is the method requested
		$method = array_shift($urlParams);

		// the third url param is the return type
		$this->returnType = array_shift($urlParams);
		
		// retrieve posted or query data
		$data = ($request->is_post()) ? $request->get_post() : $request->get_query();
		
		$req = array('method' => $method, 'request' => $data);
		
		return pixelpost\Filter::array_to_object($req);
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
