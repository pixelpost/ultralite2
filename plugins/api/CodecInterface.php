<?php

namespace pixelpost\plugins\api;

use pixelpost;

/**
 * Interface of a codec.
 *
 * A codec can decode request and encode request in a specific format like
 * xml, xml-rpc, json, soap etc...
 */
interface CodecInterface
{
	/**
	 * Decode the request and return an PHP stdClass containing the requested
	 * data.
	 *
	 * @param string
	 * @return stdClass
	 */
	public function decode($request);

	/**
	 * Encode a reponse, an array containing all the client datas, in the client
	 * format.
	 *
	 * @param array
	 * @return string
	 */
	public function encode(array $response);
}