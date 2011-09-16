<?php

namespace pixelpost\plugins\api;

use pixelpost;

/**
 * Interface of a codec.
 *
 * A codec can decode request and encode request in a specific format like
 * xml, xml-rpc, json, soap etc...
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
interface CodecInterface
{

	/**
	 * Decode the request and return an PHP stdClass containing the requested
	 * data.
	 *
	 * @param  string
	 * @return stdClass
	 */
	public function decode($request);

	/**
	 * Encode a reponse, an array containing all the client data, in the client
	 * format.
	 *
	 * @param  array
	 * @return string
	 */
	public function encode(array $response);
}
