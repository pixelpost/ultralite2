<?php

namespace pixelpost\plugins\api;

/**
 * Provide a specific exception.
 *
 * This exception can be used by other plugins to send an standart error to a
 * API client.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Exception extends \Exception
{
	/**
	 * This is containing a short message, more explicit than a exception code
	 * see: getCode()
	 * 
	 * @var string
	 */
	protected $_shortMsg;
	
	/**
	 * Create a new API exception.
	 * 
	 * @param string     $shortMsg
	 * @param string     $longMsg
	 * @param \Exception $previous
	 */
	public function __construct($shortMessage, $longMsg, \Exception $previous = null)
	{
		$this->_shortMsg = $shortMessage;
		parent::__construct($longMsg, 0, $previous);		
	}
	
	/**
	 * Return the short message error 
	 * 
	 * @return string 
	 */
	public function getShortMessage()
	{
		return $this->_shortMsg;
	}
}
