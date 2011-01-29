<?php

namespace pixelpost\plugins\api;

use pixelpost;

/**
 * Provide a XML codec for the plugin 'api'
 *
 * For more information about codec see: CodecInterface
 */
class CodecXml implements CodecInterface
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

		// shut up ! all xml errors and prefer libxml_get_errors()
		libxml_use_internal_errors(true);

		// check $request is UTF-8 and decode it
		$request = simplexml_load_string(pixelpost\Filter::check_encoding($request));

		// check if the request is well decoded
		if ($request === false)
		{
			if (pixelpost\DEBUG)
			{
				$errorMsg = '';
				
				foreach ($errors as $error)
				{
					$errorMsg .= $this->format_xml_error($error);
				}
				
				libxml_clear_errors();

				throw new Exception('bad_encoding',
					'The XML request seems invalid format. Debug: ' . $errorMsg);
			}
			else
			{
				throw new Exception('bad_encoding',
					'The XML request seems invalid format.');
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
		return '<?xml version="1.0" encoding="UTF-8"?>' .
		       '<xml>' .
			   $this->array_to_xml($response) .
			   '</xml>';
	}

	/**
	 * transform an array to an XML string
	 * 
	 * @param array $data
	 * @return string
	 */
	public function array_to_xml(array $data)
	{
		$xml = '';

		foreach($data as $key => $value)
		{
			if (\is_array($value)) $value = $this->array_to_xml($value);

			pixelpost\Filter::assume_string($value);

			$key = pixelpost\Filter::format_for_xml($key);

			if (pixelpost\Filter::format_for_url($value) != $value)
			{
				$xml .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>';
			}
			else
			{
				$xml .= '<' . $key . '>' . $value . '</' . $key . '>';
			}
		}

		return $xml;
	}

	/**
	 * Return a readable text message of an libXMLError
	 *
	 * @param \libXMLError $error
	 * @return string
	 */
	public function format_xml_error(\libXMLError $error)
	{
		$err = '';
		
		switch ($error->level)
		{
			case LIBXML_ERR_WARNING : $err .= '- Warning ' . $error->code; break;
			case LIBXML_ERR_ERROR   : $err .= '- Error '   . $error->code; break;
			case LIBXML_ERR_FATAL   : $err .= '- Fatal '   . $error->code; break;
			default                 : $err .= '- Unknown ' . $error->code; break;
		}
		
		$err .= ' line: '    . $error->line .
				':'          . $error->column .
				' message: ' . $error->message;

		return $err . "\n";
	}
}