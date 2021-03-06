<?php

namespace pixelpost\core;

use SQLite3, DateTime, DateTimeZone;

/**
 * Db support, actually using builtin php Sqlite3
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Db extends SQLite3
{
	/**
	 * @var pixelpost\core\Db The singleton of that class is stored here
	 */
	protected static $_instance;

	/**
	 * @var String The database file
	 */
	protected static $_database = '';

	/**
	 * You shoudn't use this contructor directly, You must use create() method.
	 *
	 * @throws pixelpost\core\Error
	 */
	final public function __construct()
	{
		if (!is_null(static::$_instance)) throw Error::create(10);

		static::$_database = static::$_database ?: PRIV_PATH . '/sqlite3.db';

		$this->open(static::$_database);

		static::$_instance = $this;
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
	 * @return pixelpost\core\Db
	 */
	public static function create()
	{
		return static::$_instance ?: new static;
	}

	/**
	 * Do the opposite of create()
	 *
	 * @return pixelpost\core\Db
	 */
	public static function delete()
	{
		static::$_instance->close();
		static::$_instance = null;
		unlink(static::$_database);
	}

	/**
	 * Change the database to connect on when use create()
	 *
	 * @param string $file the sqlite3 database file.
	 */
	public static function set_database_file($file)
	{
		static::$_database = $file;
	}

	/**
	 * Escape a string for SQL request. Add quotes aside the string.
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function escape($string)
	{
		return '\'' . static::escapeString(strval($string)) . '\'';
	}

	/**
	 * Serialize a DateTime object in a string to be stored in database as a
	 * INTEGER value. The result is a string and not en int because PHP cannot
	 * handle big int as SQlite3 can (SQlite3 automatically convert string to
	 * INTEGER on insertion).
	 *
	 * @param  DateTime $date
	 * @return string
	 */
	public static function date_serialize(DateTime $date)
	{
		$date->setTimezone(new DateTimeZone('UTC'));

		return $date->format('YmdHis');
	}

	/**
	 * Unserialize a $date string comming from SQlite3 database to a datetime
	 * Object.
	 *
	 * @param  string    $date
	 * @return DateTime
	 */
	public static function date_unserialize($date)
	{
		return DateTime::createFromFormat('YmdHis', strval($date), new DateTimeZone('UTC'));
	}
}
