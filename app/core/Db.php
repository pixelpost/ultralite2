<?php

namespace pixelpost;

/**
 * Db support, actually using builtin php Sqlite3
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
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
}

/**
 * Utility For SQLite database, permit to map personnal field to sql field
 * and cast their datatype correctly.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class SqlMapper
{
	const SQL_NULL = 0;
	const SQL_TEXT = 1;
	const SQL_INT  = 2;
	const SQL_REAL = 3;
	const SQL_DATE = 4;
	const SQL_BLOB = 5;

	const DATA_STRING = 0;
	const DATA_INT    = 1;
	const DATA_FLOAT  = 2;
	const DATA_BOOL   = 3;
	const DATA_DATE   = 4;
	const DATA_NULL   = 5;

	protected $_dataTypes = array();
	protected $_dataMap   = array();
	protected $_sqlMap    = array();
	protected $_sqlType   = array();

	/**
	 * Register a new field in the map, if sqlType is not provided he is
	 * automatically created from $datatype with this casting rule :
	 *
	 * DATA_STRING => SQL_TEXT
	 * DATA_INT    => SQL_INT
	 * DATA_FLOAT  => SQL_REAL
	 * DATA_BOOL   => SQL_INT
	 * DATA_NULL   => SQL_NULL
	 *
	 * @param  string    $dataName
	 * @param  string    $sqlField
	 * @param  int       $dataType
	 * @param  int       $sqlType
	 * @return SqlMapper
	 */
	public function map($dataName, $sqlField, $dataType, $sqlType = null)
	{
		if (is_null($sqlType))
		{
			switch($dataType)
			{
				case self::DATA_STRING : $sqlType = self::SQL_TEXT; break;
				case self::DATA_INT    : $sqlType = self::SQL_INT;  break;
				case self::DATA_FLOAT  : $sqlType = self::SQL_REAL; break;
				case self::DATA_BOOL   : $sqlType = self::SQL_INT;  break;
				case self::DATA_DATE   : $sqlType = self::SQL_DATE; break;
				case self::DATA_NULL   : $sqlType = self::SQL_NULL; break;
				default                : $sqlType = self::SQL_NULL; break;
			}
		}

		$this->_dataTypes[$dataName] = $dataType;
		$this->_dataMap[$dataName]   = $sqlField;
		$this->_sqlMap[$sqlField]    = $dataName;
		$this->_sqlType[$sqlField]   = $sqlType;

		return $this;
	}

	/**
	 * Create a comma separated SQL field list corresponding to the data field
	 * provide in the $dataFields array
	 *
	 * @param  array  $dataFields
	 * @return string
	 */
	public function genSqlSelectList(array $dataFields)
	{
		$list = array();

		foreach($dataFields as $dataField)
		{
			if (!isset($this->_dataMap[$dataField])) continue;

			$list[] = $this->_dataMap[$dataField];
		}

		return implode(', ', $list);
	}

	/**
	 * Create a comma separated SQL field list corresponding to the data field
	 * provide in the $dataFields array
	 *
	 * @param  array  $dataFields
	 * @return string
	 */
	public function genSqlUpdateList(array $dataFields)
	{
		$list = array();

		foreach($dataFields as $dataField => $value)
		{
			if (!isset($this->_dataMap[$dataField])) continue;

			$sqlField = $this->_dataMap[$dataField];
			$sqlType  = $this->_sqlType[$sqlField];
			$sqlData  = $this->_castSql($value, $sqlType);
			$list[] = sprintf('%s = %s', $sqlField, $sqlData);
		}

		return implode(', ', $list);
	}

	/**
	 * Create a comma separated SQL field list corresponding to the data field
	 * provide in the $dataFields array
	 *
	 * @param  array  $dataFields
	 * @return string
	 */
	public function genSqlInsertList(array $dataFields)
	{
		$list = array();

		foreach($dataFields as $dataField => $value)
		{
			if (!isset($this->_dataMap[$dataField])) continue;

			$sqlField = $this->_dataMap[$dataField];
			$sqlType  = $this->_sqlType[$sqlField];
			$sqlData  = $this->_castSql($value, $sqlType);
			$list[$sqlField] = $sqlData;
		}

		return '(' . implode(', ', array_keys($list)) . ') VALUES (' . implode(', ', $list) . ')';
	}

	/**
	 * Transform a SQL array result in his corresponding data array result form.
	 *
	 * The $todo closure permit to manipulate the resultset. This closure is
	 * called with one argument the resultset, consider to pass it by reference
	 * to manipulate it.
	 *
	 * @param  array   $result
	 * @param  Closure $todo
	 * @return array
	 */
	public function genArrayResult(array $result, \Closure $todo = null)
	{
		$list = array();

		foreach($result as $sqlField => $value)
		{
			if (!isset($this->_sqlMap[$sqlField])) continue;

			$dataField = $this->_sqlMap[$sqlField];
			$dataType  = $this->_dataTypes[$dataField];

			$list[$dataField] = $this->_castData($value, $dataType);
		}

		if (!is_null($todo)) $todo($list);

		return $list;
	}

	/**
	 * Cast SQL datatype into the $datatype provided
	 *
	 * @param  mixed $value
	 * @param  int   $dataType
	 * @return mixed
	 */
	protected function _castData($value, $dataType)
	{
		switch($dataType)
		{
			case self::DATA_STRING :
				Filter::assume_string($value);
				return $value;

			case self::DATA_INT :
				Filter::assume_int($value);
				return $value;

			case self::DATA_FLOAT :
				Filter::assume_float($value);
				return $value;

			case self::DATA_BOOL :
				Filter::assume_int($value);
				Filter::assume_bool($value);
				return $value;

			case self::DATA_DATE :
				return $this->date_unserialize($value);

			default :
				return null;
		}
	}

	/**
	 * Cast Data datatype into the $sqltype provided
	 *
	 * @param  mixed $value
	 * @param  int   $sqlType
	 * @return mixed
	 */
	protected function _castSql($value, $sqlType)
	{
		$db = Db::create();

		switch($sqlType)
		{
			case self::SQL_TEXT :
				Filter::assume_string($value);
				return sprintf('\'%s\'', Db::escapeString($value));

			case self::SQL_INT :
				Filter::assume_int($value);
				return $value;

			case self::SQL_REAL :
				Filter::assume_float($value);
				return $value;

			case self::SQL_BLOB :
				return sprintf('\'%s\'', Db::escapeString($value));

			case self::SQL_DATE :
				return sprintf('\'%s\'', $this->date_serialize($value));

			default :
				return null;
		}
	}

	/**
	 * Serialize a DateTime object in a string to be stored in database as a
	 * INTEGER value. The result is a string and not en int because PHP cannot
	 * handle big int as SQlite3 can (SQlite3 automatically convert string to
	 * INTEGER on insertion).
	 *
	 * @param \DateTime $date
	 * @return string
	 */
	public function date_serialize(\DateTime $date)
	{
		$date->setTimezone(new \DateTimeZone('UTC'));

		return $date->format('YmdHis');
	}

	/**
	 * Unserialize a $date string comming from SQlite3 database to a datetime
	 * Object.
	 *
	 * @param  string    $date
	 * @return \DateTime
	 */
	public function date_unserialize($date)
	{
		Filter::assume_string($date);

		return \DateTime::createFromFormat('YmdHis', $date, new \DateTimeZone('UTC'));
	}
}

