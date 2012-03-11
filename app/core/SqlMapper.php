<?php

namespace pixelpost\core;

use Closure;

/**
 * Utility For SQLite database, permit to map personnal field to sql field
 * and cast their datatype correctly.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
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
	 * Return an SqlMapper object
	 *
	 * @return pixelpost\core\SqlMapper
	 */
	public static function create()
	{
		return new static;
	}

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
	 * @return pixelpost\core\SqlMapper
	 */
	public function map($dataName, $sqlField, $dataType, $sqlType = null)
	{
		if (is_null($sqlType))
		{
			switch($dataType)
			{
				case static::DATA_STRING : $sqlType = static::SQL_TEXT; break;
				case static::DATA_INT    : $sqlType = static::SQL_INT;  break;
				case static::DATA_FLOAT  : $sqlType = static::SQL_REAL; break;
				case static::DATA_BOOL   : $sqlType = static::SQL_INT;  break;
				case static::DATA_DATE   : $sqlType = static::SQL_DATE; break;
				case static::DATA_NULL   : $sqlType = static::SQL_NULL; break;
				default                  : $sqlType = static::SQL_NULL; break;
			}
		}

		$this->_dataTypes[$dataName] = $dataType;
		$this->_dataMap[$dataName]   = $sqlField;
		$this->_sqlMap[$sqlField]    = $dataName;
		$this->_sqlType[$sqlField]   = $sqlType;

		return $this;
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
			case static::DATA_STRING : return strval($value);
			case static::DATA_INT    : return intval($value);
			case static::DATA_FLOAT  : return floatval($value);
			case static::DATA_BOOL   : return (bool) intval($value);
			case static::DATA_DATE   : return Db::date_unserialize($value);
			default                  : return null;
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
		switch($sqlType)
		{
			case static::SQL_TEXT : return Db::escape(strval($value));
			case static::SQL_INT  : return intval($value);
			case static::SQL_REAL : return floatval($value);
			case static::SQL_BLOB : return Db::escape($value);
			case static::SQL_DATE : return Db::date_serialize($value);
			default               : return null;
		}
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
			$list[] = $sqlField . ' = ' . $sqlData;
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
	public function genArrayResult(array $result, Closure $todo = null)
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
}

