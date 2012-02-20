<?php

namespace pixelpost\plugins\api\Exception;

/**
 * API Exception for number field which are not in bounds.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class FieldOutBounds extends \pixelpost\plugins\api\Exception
{
	/**
	 * Create the Exception
	 *
	 * @param string $field
	 * @param int    $min
	 * @param int    $max
	 */
	public function __construct($field, $min, $max)
	{
		$code    = 'bad_data';
		$message = 'Field `%s` need to be a number between %d and %d.';

		parent::__construct($code, sprintf($message, $min, $max));
	}
}