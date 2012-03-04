<?php

namespace pixelpost\plugins\api\Exception;

/**
 * API Exception for non authorized users
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Ungranted extends \pixelpost\plugins\api\Exception
{
	/**
	 * Create the Exception
	 *
	 * @param string $apiMethod
	 */
	public function __construct($apiMethod)
	{
		$code    = 'unauthorized';
		$message = 'You do not have the necessary rights to use the `%s` method.';

		parent::__construct($code, sprintf($message, $apiMethod));
	}
}