<?php

namespace pixelpost\plugins\api\Exception;

/**
 * API Exception for field with a limited list of value
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class FieldNotInList extends \pixelpost\plugins\api\Exception
{
	/**
	 * Create the Exception
	 * 
	 * @param string $field
	 * @param array  $list
	 */
	public function __construct($field, array $list)
	{
		$code    = 'bad_data';
		$message = 'Field `%s` need to be one of this value: %s.';
		
		parent::__construct($code, sprintf($message, implode(' | ', $message)));
	}		
}