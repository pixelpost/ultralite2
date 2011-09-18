<?php

namespace pixelpost;

/**
 * Db support, actually using builtin php Sqlite3
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Db extends \SQLite3
{

	/**
	 * @var Db The singleton of that class is stored here
	 */
	protected static $_instance;
	
	/**
	 * You shoudn't use this contructor directly, You must use create() method.
	 *
	 * @throws Error
	 */
	final public function __construct()
	{
		if (!is_null(self::$_instance))
		{
			throw Error::create(10);
		}

		$this->open(PRIV_PATH . SEP . 'sqlite3.db');
		self::$_instance = $this;
	}
	
	/**
	 * You shoudn't use this destructor directly.
	 */
	final public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * Returns an unique instance of Db class allready connected to the database
	 * (if the db file doeasn't exist, the first connection create it)
	 *
	 * @return Db
	 */
	public static function create()
	{
		return self::$_instance ?: new static;
	}
	
	/**
	 * Escape a string for SQL request. Add quotes aside the string.
	 * 
	 * @param  string $string 
	 * @return string
	 */
	public function escape($string)
	{
		Filter::assume_string($string);
		
		return sprintf('\'%s\'', self::escapeString($string));
	}
}
