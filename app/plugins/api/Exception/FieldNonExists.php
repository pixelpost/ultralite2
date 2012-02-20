<?php

namespace pixelpost\plugins\api\Exception;

/**
 * API Exception for field providing a non existant value (id, file...)
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class FieldNonExists extends \pixelpost\plugins\api\Exception
{
	/**
	 * Create the Exception
	 *
	 * @param string $field
	 * @param string $value
	 */
	public function __construct($field, $value = null)
	{
		$code    = 'bad_data';

		if (is_null($value))
		{
			$message = 'The specified `%s` not exists.';
		}
		else
		{
			$message = 'The specified `%s` with value `' . $value . '` not exists.';
		}

		parent::__construct($code, sprintf($message, $field));
	}
}