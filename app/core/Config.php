<?php

namespace pixelpost\core;

use ArrayObject;

/**
 * Configuration support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Config extends ArrayObject
{
	/**
	 * @var Config The singleton of that class is stored here
	 */
	protected static $_instance;

	/**
	 * @var string The filename where the configuration is read to.
	 */
	protected static $_file;

	/**
	 * You shoudn't use this contructor directly, You must use create() method.
	 *
	 * @throws pixelpost\core\Error
	 */
	final public function __construct()
	{
		if (!is_null(static::$_instance)) throw Error::create(2);

		parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);

		static::$_instance = $this;
	}

	/**
	 * Returns an unique instance of Config class containing the configuration data
	 *
	 * @return pixelpost\core\Config
	 */
	public static function create()
	{
		return static::$_instance ?: new static;
	}

	/**
	 * Load the $filename as a json encoded configuration file. All data in the
	 * configuration file can be accessed by getting the instance of Config
	 * class.
	 * Keep in memory the filename in $_file protected static var.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $filename The configuration file you want load.
	 */
	public static function load($filename)
	{
		Filter::assume_string($filename);

		if (!file_exists($filename))
		{
			throw Error::create(3, array($filename));
		}

		if (false == $content = file_get_contents($filename))
		{
			throw Error::create(4, array($filename));
		}

		$conf = json_decode($content);

		if (json_last_error() != JSON_ERROR_NONE)
		{
			$errormsg = '';
			switch (json_last_error())
			{
				default                   : $errormsg = 'Unknown error';
				case JSON_ERROR_DEPTH     : $errormsg = 'Max depth.';
				case JSON_ERROR_CTRL_CHAR : $errormsg = 'Bad characters.';
				case JSON_ERROR_SYNTAX    : $errormsg = 'Bad syntax.';
			}
			throw Error::create(5, array($filename, $errormsg));
		}

		static::create()->exchangeArray($conf);

		static::$_file = $filename;

		return static::$_instance;
	}

	/**
	 * Backup the actual configuration in the file referenced by $_file.
	 *
	 * @return bool
	 */
	public function save()
	{
		$data = json_encode($this, JSON_HEX_QUOT);

		return (bool) file_put_contents(static::$_file, $data, LOCK_EX);
	}
}
