<?php

namespace pixelpost\plugins\api\Exception;

/**
 * API Exception for internal error
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Internal extends \pixelpost\plugins\api\Exception
{
	/**
	 * Create the Exception
	 *
	 * @param string $message
	 */
	public function __construct($message, \Exception $previous = null)
	{
		$code    = 'internal_error';

		parent::__construct($code, $message, $previous);
	}
}