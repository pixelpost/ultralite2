<?php

namespace pixelpost\plugins\api\Exception;

/**
 * API Exception for required field
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class FieldRequired extends \pixelpost\plugins\api\Exception
{
	/**
	 * Create the Exception
	 *
	 * @param string $apiMethod
	 * @param string $field
	 */
	public function __construct($apiMethod, $field)
	{
		$code    = 'bad_request';
		$message = '`%s` method requires a `%s` field.';

		parent::__construct($code, sprintf($message, $apiMethod, $field));
	}
}