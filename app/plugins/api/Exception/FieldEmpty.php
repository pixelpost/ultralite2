<?php

namespace pixelpost\plugins\api\Exception;

/**
 * API Exception for empty fields
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class FieldEmpty extends pixelpost\plugins\api\Exception
{
	/**
	 * Create the Exception
	 * 
	 * @param string $field
	 */
	public function __construct($field)
	{
		$code    = 'bad_request';
		$message = 'The field `%s` is empty.';
		
		parent::__construct($code, sprintf($message, $field));
	}		
}