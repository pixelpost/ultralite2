<?php

namespace pixelpost\plugins\api\Exception;

/**
 * API Exception for generic invalid field (format, value etc..)
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class FieldNotValid extends pixelpost\plugins\api\Exception
{
	/**
	 * Create the Exception
	 * 
	 * @param string $field
	 * @param string $moreInfo
	 */
	public function __construct($field, $moreInfo = '')
	{
		$code    = 'bad_data';
		
		if ($moreInfo == '')
		{
			$message = 'The specified `%s` is not valid.';		
		}
		else
		{
			$message = 'The specified `%s` is not valid : ' . $moreInfo . '.';					
		}
		
		parent::__construct($code, sprintf($message, $field));
	}		
}