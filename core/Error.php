<?php

namespace pixelpost;

/**
 * Error support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
 */
class Error extends \Exception
{

	/**
	 * Create a new instance of the Error class
	 *
	 * @param int   $code The error code (see: get_message_by_code())
	 * @param array $args The optionnals args (array of string)
	 */
	public function __construct($code = 0, array $args = array())
	{
		// check code is a int
		\is_int($code) or $code = $this->get_default_code();

		// get the error message
		$message = $this->get_message_by_code($code);

		\is_string($message) or $message = $this->get_default_message();

		// if there are some arguments, format them
		if (empty($args) == false && $message !== false)
		{
			$message = $this->_sprintf_message($message, $args);
		}

		// construct the exception
		parent::__construct($message, $code);
	}

	/**
	 * Create a new Error instance and return it
	 *
	 * @param int   $code The error code (see: get_message_by_code())
	 * @param array $args The optionnals args (array of string)
	 * @return Error
	 */
	public static function create($code = 0, array $args = array())
	{
		return new static($code, $args);
	}

	/**
	 * Return an explicit message.
	 *
	 * @final
	 * @return string
	 */
	public final function __toString()
	{
		return sprintf('[%s][%d] : %s', get_class($this), $this->code, $this->message);
	}

	/**
	 * Complete the error message with the args.
	 *
	 * This methods process like the sprintf() function in PHP core, just more
	 * handy (error should not be generate error of formating).
	 *
	 * @param  string $message Message without formating
	 * @param  array  $args    Arguments, data to add in the message
	 * @return string
	 */
	protected function _sprintf_message($message, array $args)
	{
		// create a range(1..X) where X is exactly the number of arguments we
		// have.
		$search = range(1, sizeof($args));

		// change $search range(1..NumARgs) to range(%s1..%sNumARgs)
		array_walk($search, function (&$value, $key)
		{
			$value = '%s' . $value;
		});

		// replace each %sX by its arguments
		return str_replace($search, $args, $message);
	}

	/**
	 * Return the default error code
	 *
	 * @return int
	 */
	public function get_default_code()
	{
		return 0;
	}

	/**
	 * Return the default error message
	 *
	 * @return string
	 */
	public function get_default_message()
	{
		return 'Unknown Exception.';
	}

	/**
	 * Return the message corresponding to a code number
	 *
	 * @param int $code code number
	 * @return string
	 */
	public function get_message_by_code($code)
	{
		switch ($code)
		{
			case 1 : return 'Filter: Parameter is not a "%s1".';
			case 2 : return 'Config: This is a singleton, use create() method instead of __construct().';
			case 3 : return 'Config: Config file "%s1" not exists.';
			case 4 : return 'Config: Couldn\'t retrieve "%s1" config file content.';
			case 5 : return 'Config: Couldn\'t decode "%s1" config file (JSON error: %s2).';
			case 6 : return 'Plugin: Bad state code "%s1" for set_state() method.';
			case 7 : return 'Plugin: plugin "%s1" should provide a "%s2" class (fullname: "%s3").';
			case 8 : return 'Plugin: plugin "%s1" should provide a "%s2" class that implements "%s3".';
			case 9 : return 'Plugin: Could not open the directory "%s1".';
		}
	}

}

