<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\SqlMapper as Map;

/**
 * All Exception thown by the Model class are ModelException class
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class ModelException extends \Exception {}

/**
 * Exception thrown when a SQL resultset is empty
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class ModelExceptionNoResult extends ModelException {} 

/**
 * Exception thrown when a SQL error is raise, this exception retrieve 
 * automatically the last SQL error message and error code.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class ModelExceptionSqlError extends ModelException 
{
	public function __construct()
	{
		$db = pixelpost\Db::create();
		
		parent::__construct($db->lastErrorMsg(), $db->lastErrorCode());
	}
}

/**
 * This class Store and retrieve the photo data
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Model
{
	protected static $_mapper;
	
	/**
	 * Return the SqlMapper corresponding to the photos Table.
	 * 
	 * NOTA: The mapper is registred in a static field because it not necessary
	 * to re-create it each time he is used.
	 * 
	 * @return pixelpost\SqlMapper
	 */
	protected static function _getMapper()
	{
		if (is_null(self::$_mapper))
		{
			// use pixelpost\SqlMapper as Map
			self::$_mapper = new Map();
			self::$_mapper->map('id',           'id',      Map::DATA_INT)
						  ->map('filename',     'file',    Map::DATA_STRING)
						  ->map('title',        'title',   Map::DATA_STRING)
						  ->map('description',  'desc',    Map::DATA_STRING)
						  ->map('publish-date', 'publish', Map::DATA_DATE)
						  ->map('visible',      'show',    Map::DATA_BOOL);
		}
		
		return self::$_mapper;
	}
	
	/**
	 * Create the table in database
	 */
	public static function table_create()
	{
		pixelpost\Db::create()->exec('CREATE TABLE photos (id INTEGER PRIMARY KEY,
			file TEXT, title TEXT, desc TEXT, publish INTEGER, show INTEGER);');		
	}

	/**
	 * Update the table in table to the last version
	 */
	public static function table_update()
	{
		// actually there nothing to update
	}

	/**
	 * Drop the table in database
	 */
	public static function table_delete()
	{
		pixelpost\Db::create()->exec('DROP TABLE photos;');
	}

	/**
	 * Add a photo. 
	 * Return the new photo id.
	 * 
	 * @param  string $filename
	 * @return int
	 */
	public static function photo_add($filename)
	{
		pixelpost\Filter::assume_string($filename);
		
		$fields = self::_getMapper()->genSqlInsertList(array(
			'filename'     => $filename,
			'publish-date' => new \DateTime(),
			'show'         => 0
		));
		
		$db  = pixelpost\Db::create();
		$sql = sprintf('INSERT INTO photos %s;', $fields);
				
		if (!$db->exec($sql)) throw new ModelExceptionSqlError();		
		
		return $db->lastInsertRowID();
	}

	/**
	 * Delete the photo $photoId.
	 * Return the number of deleted row
	 * 
	 * @param  int $photoId 
	 * @return int
	 */
	public static function photo_del($photoId)
	{
		pixelpost\Filter::assume_int($photoId);
		
		$db  = pixelpost\Db::create();
		$sql = sprintf('DELETE FROM photos WHERE id = %d;', $photoId);
		
		if (!$db->exec($sql)) throw new ModelExceptionSqlError();		
		
		return $db->changes();
	}
	
	/**
	 * Retrieve $fields to the photos tables in database. 
	 * $fields is a list of data needed to be retrieved.
	 * This return an array of associated array field => value
	 * The $todo closure permit to manipulate EACH resultset like:
	 * 
	 * $todo = function(&$result) 
	 * { 
	 *     $result['url'] = 'http://something.com/photos/' . $result['file']; 
	 * }
	 * 
	 * In case of error this method raise an ModelExceptionSqlError exception
	 * In case of no result this method raise an ModelExceptionNoResult exception
	 * 
	 * @param  int     $photoId
	 * @param  array   $fields
	 * @param  Closure $todo
	 * @return array
	 */
	public static function photo_list(array $fields, $options, \Closure $todo = null)
	{
		$db     = pixelpost\Db::create();
		$map    = self::_getMapper();
		$fields = $map->genSqlSelectList($fields);
		$where = '';
		$order = '';
		$limit = '';
		
		// work on SQL where clause
		if (isset($options['filter']))
		{
			$w = array();
			
			// use the couple foreach/switch here is more readable and keep
			// the order in the array
			foreach($options['filter'] as $filter => $value)
			{
				switch($filter)
				{
					default: break;
					
					case 'publish-date-interval' :
						$start = $db->escapeString($map->date_serialize($value['start']));
						$end   = $db->escapeString($map->date_serialize($value['end']));
						
						$w[] = sprintf(' publish BETWEEN %s AND %s', $start, $end);
						break;

					case 'visible' :
						$w[] = sprintf(' show = %s', intval($value));
						break;					
				}
			}
			
			if (count($w) > 0) $where = ' WHERE' . implode(' AND', $w);
		}
		
		// work on SQL ORDER BY clause
		if (isset($options['sort']))
		{
			$s = array();
			
			// use the couple foreach/switch here is more readable and keep
			// the order in the array
			foreach($options['sort'] as $sort => $value)
			{
				switch($sort)
				{
					default: break;
						
					case 'publish-date' :
						$value = (($value == 'asc') ? 'ASC' : 'DESC');
						$s[] = sprintf(' publish %s', $value);
						break;
					
					case 'title' :
						$value = (($value == 'asc') ? 'ASC' : 'DESC');
						$s[] = sprintf(' title %s', $value);
						break;
				}
			}
			
			if (count($s) > 0) $order = ' ORDER BY' . implode(',', $s);				
		}
		
		// work on SQL LIMIT clause
		if (isset($options['pager']))
		{
			$page = intval($options['pager']['page']);
			$max  = intval($options['pager']['max-per-page']);
			
			$limit = sprintf(' LIMIT %s, %s', ($page - 1) * $max, $max);
		}
		
		$query = 'SELECT %s FROM photos%s%s%s;';
		$query = sprintf($query, $fields, $where, $order, $limit);

		$result = pixelpost\Db::create()->query($query);		

		if ($result === true)  throw new ModelExceptionNoResult();		
		if ($result === false) throw new ModelExceptionSqlError();		
		
		$list = array();

		while(false !== $fetched = $result->fetchArray(\SQLITE3_ASSOC))
		{
			$list[] = self::_getMapper()->genArrayResult($fetched, $todo);
		}

		return $list; 
	}
	
	/**
	 * Retrieve $fields of $photoId to the photos tables in database. 
	 * $fields is a list of data needed to be retrieved.
	 * This return an associated array field => value
	 * The $todo closure permit to manipulate the resultset like:
	 * 
	 * $todo = function(&$result) 
	 * { 
	 *     $result['url'] = 'http://something.com/photos/' . $result['file']; 
	 * }
	 * 
	 * In case of error this method raise an ModelExceptionSqlError exception
	 * In case of no result this method raise an ModelExceptionNoResult exception
	 * 
	 * @param  int     $photoId
	 * @param  array   $fields
	 * @param  Closure $todo
	 * @return array
	 */
	public static function photo_get($photoId, array $fields, \Closure $todo = null)
	{
		pixelpost\Filter::assume_int($photoId);

		$fields = self::_getMapper()->genSqlSelectList($fields);
		
		$query = 'SELECT %s FROM photos WHERE id = %d LIMIT 1;';
		$query = sprintf($query, $fields, $photoId);
		
		$result = pixelpost\Db::create()->querySingle($query, true);		
		
		if ($result === false) throw new ModelExceptionSqlError();		
		if (empty($result))    throw new ModelExceptionNoResult();
		
		return self::_getMapper()->genArrayResult($result, $todo);
	}

	/**
	 * Update some fields of $photoId where $fields is an associated array
	 * containing dataName => value. Return the number of updated row.
	 * 
	 * In case of error this method raise an ModelExceptionSqlError exception
	 * 
	 * @param  int   $photoId
	 * @param  array $fields 
	 * @return int
	 */
	public static function photo_set($photoId, array $fields)
	{
		pixelpost\Filter::assume_int($photoId);
		
		$db = pixelpost\Db::create();
		
		$values = self::_getMapper()->genSqlUpdateList($fields);
		
		$sql = 'UPDATE photos SET %s WHERE id = %d;';
		$sql = sprintf($sql, $values, $photoId);
		
		if (!$db->exec($sql)) throw new ModelExceptionSqlError();
		
		return $db->changes();
	}
	
}
