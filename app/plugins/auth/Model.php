<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\Db as Db;

/**
 * All Exception thown by the Model class are ModelException class
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class ModelException extends \Exception {}

/**
 * Exception thrown when a SQL resultset is empty
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class ModelExceptionNoResult extends ModelException {}

/**
 * Exception thrown when a SQL error is raise, this exception retrieve
 * automatically the last SQL error message and error code.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class ModelExceptionSqlError extends ModelException
{
	public function __construct()
	{
		$db = Db::create();

		parent::__construct($db->lastErrorMsg(), $db->lastErrorCode());
	}
}

/**
 * This class Store and retrieve all auth data
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Model
{
	/**
	 * Create tables in database
	 */
	public static function table_create()
	{
		$db = Db::create();
		
		$db->exec('CREATE TABLE auth_user (id INTEGER PRIMARY KEY,
			name TEXT, pass TEXT);');
		
		$db->exec('CREATE TABLE auth_grant (id INTEGER PRIMARY KEY,
			name TEXT);');
		
		$db->exec('CREATE TABLE auth_user_grant (id INTEGER PRIMARY KEY,
			user_id INTEGER, grant_id INTEGER);');
		
		$db->exec('CREATE TABLE auth_challenge (id INTEGER PRIMARY KEY,
			challenge TEXT, user_id INTEGER, expire INTEGER);');
				
		$db->exec('CREATE TABLE auth_token (id INTEGER PRIMARY KEY,
			token TEXT, challenge TEXT, user_id INTEGER, created INTEGER);');
		
		$db->exec('INSERT INTO auth_grant (name) VALUES ("read");');   // read content
		$db->exec('INSERT INTO auth_grant (name) VALUES ("write");');  // set / add content
		$db->exec('INSERT INTO auth_grant (name) VALUES ("delete");'); // del content
		$db->exec('INSERT INTO auth_grant (name) VALUES ("config");'); // change configuration
		$db->exec('INSERT INTO auth_grant (name) VALUES ("admin");');  // change user, grants
	}

	/**
	 * Update tables in table to the last version
	 */
	public static function table_update()
	{
		// actually there nothing to update
	}

	/**
	 * Drop tables in database
	 */
	public static function table_delete()
	{
		$db = Db::create();
		$db->exec('DROP TABLE auth_user;');
		$db->exec('DROP TABLE auth_grant;');
		$db->exec('DROP TABLE auth_user_grant;');
		$db->exec('DROP TABLE auth_challenge;');
		$db->exec('DROP TABLE auth_token;');
	}
	
	/**
	 * Add a user in database
	 * 
	 * @param  string $username The user name.
	 * @param  string $password The hash of the password.
	 * @return int              The user id
	 */
	public static function user_add($username, $password)
	{
		$db = Db::create();
		
		$query = 'INSERT INTO auth_user (name, pass) VALUES (%s, %s);';
		$query = sprintf($query, Db::escape($username), Db::escape($password));

		if (!$db->exec($query)) throw new ModelExceptionSqlError();
		
		return $db->lastInsertRowID();		
	}

	/**
	 * Delete a user from database
	 * 
	 * @param  int $userId The user id
	 * @return int         The number of user deleted
	 */
	public static function user_del($userId)
	{
		$db = Db::create();
		
		pixelpost\Filter::is_int($userId);
		
		$query = 'DELETE FROM auth_user WHERE id = %d;';
		$query = sprintf($query, $userId);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		try
		{
			foreach(self::user_grant_list_by_user($userId) as $grant)
			{
				self::user_grant_unlink($userId, $grant['id']);
			}
		}
		catch(ModelExceptionNoResult $e) {}
		
		return true;
	}
	
	/**
	 * Update a user in database.
	 * 
	 * @param  int    $userId   The user id
	 * @param  string $username The user name.
	 * @param  string $password The hash of the password.
	 * @return int              The number of user updated      
	 */
	public static function user_update($userId, $username, $password)
	{
		$db = Db::create();
		
		pixelpost\Filter::is_int($userId);
		
		$query = 'UPDATE auth_user SET name = %s, pass = %s WHERE id = %d;';
		$query = sprintf($query, Db::escape($username), Db::escape($password), $userId);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();
		
		return $db->changes();
	}
	
	/**
	 * Retrieve a user data by its name.
	 * 
	 * @param  string $username The user name
	 * @return array            Data in a array with key: 'id', 'pass'
	 */
	public static function user_get_by_name($username)
	{
		$db = Db::create();
		
		$query = 'SELECT id, pass FROM auth_user WHERE name = %s LIMIT 1;';
		$query = sprintf($query, Db::escape($username));
		
		$result = $db->querySingle($query, true);
		
		if ($result === false) throw new ModelExceptionSqlError();
		if (empty($result))    throw new ModelExceptionNoResult();		
		
		return $result;		
	}
	
	/**
	 * Retrieve a user data by its id.
	 * 
	 * @param  string $userId The user id
	 * @return array          Data in a array with key: 'id', 'pass'
	 */
	public static function user_get_by_id($userId)
	{
		pixelpost\Filter::is_int($userId);
		
		$db = Db::create();
		
		$query = 'SELECT name, pass FROM auth_user WHERE id = %d LIMIT 1;';
		$query = sprintf($query, $userId);
		
		$result = $db->querySingle($query, true);
		
		if ($result === false) throw new ModelExceptionSqlError();
		if (empty($result))    throw new ModelExceptionNoResult();		
		
		return $result;		
	}
	
	/**
	 * List all user in database.
	 * 
	 * @return array The list of user constains an array with 'id', 'name' 
	 */
	public static function user_list()
	{
		$db = Db::create();
		
		$query = 'SELECT id, name FROM auth_user ORDER BY username ASC;';
		
		$result = $db->query($query);
				
		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(\SQLITE3_ASSOC)) $l[] = $row;

		return $l;		
	}
	
	/**
	 * Add a grant in database.
	 * 
	 * @param  string $name The grant name
	 * @return int          The grant id 
	 */
	public static function grant_add($name)
	{
		$db = Db::create();
		
		$query = 'INSERT INTO auth_grant (name) VALUES (%s);';
		$query = sprintf($query, Db::escape($name));

		if (!$db->exec($query)) throw new ModelExceptionSqlError();
		
		return $db->lastInsertRowID();		
	}

	/**
	 * Delete a grant from database.
	 * 
	 * @param  int $grantId The grant id
	 * @return int          The number of grants deleted
	 */
	public static function grant_del($grantId)
	{
		$db = Db::create();
		
		pixelpost\Filter::is_int($userId);
		
		$query = 'DELETE FROM auth_grant WHERE id = %d;';
		$query = sprintf($query, $grantId);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();
				
		try
		{
			foreach(self::user_grant_list_by_grant($grantId) as $user)
			{
				self::user_grant_unlink($user['id'], $grantId);
			}			
		}
		catch(ModelExceptionNoResult $e) {}
		
		return true;
	}

	/**
	 * Update a grant in database.
	 * 
	 * @param  int    $grantId  The grant id
	 * @param  string $name     The new grant name
	 * @return int              The number of grant updated 
	 */
	public static function grant_update($grantId, $name)
	{
		$db = Db::create();
		
		pixelpost\Filter::is_int($grantId);
		
		$query = 'UPDATE auth_grant SET name = %s WHERE id = %d;';
		$query = sprintf($query, Db::escape($name), $grantId);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();
		
		return $db->changes();
	}

	/**
	 * Retrieve a grant from database.
	 * 
	 * @param  string $name The grant name
	 * @return array        The array contains 'id'
	 */
	public static function grant_get($name)
	{
		$db = Db::create();
		
		$query = 'SELECT id FROM auth_grant WHERE name = %s LIMIT 1;';
		$query = sprintf($query, Db::escape($name));
		
		$result = $db->querySingle($query, true);
		
		if ($result === false) throw new ModelExceptionSqlError();
		if (empty($result))    throw new ModelExceptionNoResult();		
		
		return $result;		
	}

	/**
	 * List all grants in database.
	 * 
	 * @return array The list, each items is an array with key 'id', 'name' 
	 */
	public static function grant_list()
	{
		$db = Db::create();
		
		$query = 'SELECT id, name FROM auth_grant ORDER BY name ASC;';
		
		$result = $db->query($query);
				
		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(\SQLITE3_ASSOC)) $l[] = $row;

		return $l;		
	}
	
	/**
	 * Add a grant to a user.
	 * 
	 * @param  int $userId  The user id
	 * @param  int $grantId The grant id
	 * @return int          The link id
	 */
	public static function user_grant_link($userId, $grantId)
	{
		$db = Db::create();

		pixelpost\Filter::is_int($userId);
		pixelpost\Filter::is_int($grantId);
		
		$query = 'INSERT INTO auth_user_grant (user_id, grant_id) VALUES (%d, %d);';
		$query = sprintf($query, $userId, $grantId);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();
		
		return $db->lastInsertRowID();	
	}

	/**
	 * Remove a grant to a user 
	 * 
	 * @param  int $userId  The user id
	 * @param  int $grantId The grant id
	 * @return int          The number of link deleted
	 */
	public static function user_grant_unlink($userId, $grantId)
	{
		$db = Db::create();

		pixelpost\Filter::is_int($userId);
		pixelpost\Filter::is_int($grantId);
		
		$query = 'DELETE FROM auth_user_grant WHERE user_id = %d AND grant_id = %d;';
		$query = sprintf($query, $userId, $grantId);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();
		
		return $db->changes();	
	}

	/**
	 * List user's grants 
	 * 
	 * @param  int   $userId The user id
	 * @return array         Each item is an array with key 'id', 'name'
	 */
	public static function user_grant_list_by_user($userId)
	{
		$db = Db::create();
		
		pixelpost\Filter::is_int($userId);
		
		$query = 'SELECT g.id, g.name FROM auth_user_grant ug '
		       . 'LEFT JOIN auth_grant g ON g.id = ug.grant_id '
			   . 'WHERE ug.user_id = %d;';
		
		$result = $db->query(sprintf($query, $userId));
				
		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(\SQLITE3_ASSOC)) $l[] = $row;

		return $l;		
	}
	
	/**
	 * List user granted to $grantId 
	 * 
	 * @param  int   $grantId The grant id
	 * @return array          Each item is an array with key 'id', 'name'
	 */
	public static function user_grant_list_by_grant($grantId)
	{
		$db = Db::create();
		
		pixelpost\Filter::is_int($grantId);
		
		$query = 'SELECT u.id, u.name FROM auth_user_grant ug '
		       . 'LEFT JOIN auth_user u ON u.id = ug.user_id '
			   . 'WHERE ug.grant_id = %d;';
		
		$result = $db->query(sprintf($query, $grantId));
				
		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(\SQLITE3_ASSOC)) $l[] = $row;

		return $l;		
	}
	
	/**
	 * Add a challenge in database.
	 *  
	 * @param  string $challenge The challenge
	 * @param  int    $userId    The user id
	 * @param  int    $lifetime  The challenge lifetime
	 * @return int               The challenge id
	 */
	public static function challenge_add($challenge, $userId, $lifetime)
	{
		pixelpost\Filter::assume_string($challenge);
		pixelpost\Filter::is_int($userId);
		pixelpost\Filter::is_int($lifetime);
		
		$date = new \DateTime();
		$date->modify('+' . $lifetime . 'seconds');
		
		$challenge = Db::escape($challenge);
		$date      = Db::escape(Db::date_serialize($date));
		$db        = Db::create();
		
		$sql = 'INSERT INTO auth_challenge (challenge, user_id, expire) VALUES (%s, %d, %s);';
		$sql = sprintf($sql, $challenge, $userId, $date);
		
		if (!$db->exec($sql)) throw new ModelExceptionSqlError();
		
		return $db->lastInsertRowID();		
	}

	/**
	 * Retrieve a challenge from database.
	 * 
	 * @param  string $challenge The challenge
	 * @return array             Challenge data, with key: 'id', 'user_id', 'exprire' 
	 */
	public static function challenge_get($challenge)
	{
		pixelpost\Filter::assume_string($challenge);

		$sql = 'SELECT id, user_id, expire FROM auth_challenge WHERE challenge = %s LIMIT 1;';
		$sql = sprintf($sql, Db::escape($challenge));
		
		$db  = Db::create();
		
		$result = $db->querySingle($sql, true);
		
		if ($result === false)  throw new ModelExceptionSqlError();
		if (empty($result))     throw new ModelExceptionNoResult();
		
		$result['expire'] = Db::date_unserialize($result['expire']);
		
		return $result;
	}

	/**
	 * Remove a challenge from database.
	 * 
	 * @param  int $challengeId The challenge id
	 * @return int              The number of challenge deleted
	 */
	public static function challenge_del($challengeId)
	{
		pixelpost\Filter::is_int($challengeId);
		
		$db = Db::create();
		
		$sql = 'DELETE FROM auth_challenge WHERE id = %d;';
		$sql = sprintf($sql, $challengeId);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();
		
		return $db->changes();		
	}

	/**
	 * Add a token in database.
	 * 
	 * @param  string $token     The token
	 * @param  string $challenge The challenge
	 * @param  int    $userId    The user id
	 * @return int               The token id
	 */
	public static function token_add($token, $challenge, $userId)
	{
		pixelpost\Filter::assume_string($token);
		pixelpost\Filter::assume_string($challenge);
		pixelpost\Filter::is_int($userId);
		
		$db        = Db::create();
		$token     = Db::escape($token);
		$challenge = Db::escape($challenge);
		$date      = Db::escape(Db::date_serialize(new \DateTime()));
		
		try
		{
			foreach(self::token_list_by_user($userId) as $result)
			{
				self::token_del($result['id']);
			}
		}
		catch(ModelExceptionNoResult $e) {}
				
		$sql = 'INSERT INTO auth_token (token, challenge, user_id, created) 
			VALUES (%s, %s, %d, %s);';
		$sql = sprintf($sql, $token, $challenge, $userId, $date);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();
		
		return $db->lastInsertRowID();		
	}

	/**
	 * Retreive user's token from database
	 * 
	 * @param  int   $userId The user id
	 * @return array         Each items have key: 'id'
	 */
	public static function token_list_by_user($userId)
	{
		pixelpost\Filter::is_int($userId);
		
		$db = Db::create();
		
		$sql = 'SELECT id FROM auth_token WHERE user_id = %d;';
		$sql = sprintf($sql, $userId);
		
		$result = $db->query($sql);
		
		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();
		
		$l = array();

		while(false !== $row = $result->fetchArray(\SQLITE3_ASSOC)) $l[] = $row;

		return $l;		
	}
	
	/**
	 * Retrieve a token from database.
	 * 
	 * @param  string $token The token
	 * @return array         Token data with key: 'id', 'challenge', 'user_id', 'created' 
	 */
	public static function token_get($token)
	{
		pixelpost\Filter::assume_string($token);
		
		$db  = Db::create();

		$sql = 'SELECT id, challenge, user_id, created FROM auth_token 
			WHERE token = %s LIMIT 1;';
		$sql = sprintf($sql, Db::escape($token));
		
		$result = $db->querySingle($sql, true);
		
		if ($result === false)  throw new ModelExceptionSqlError();
		if (empty($result))     throw new ModelExceptionNoResult();
		
		$result['created'] = Db::date_unserialize($result['created']);
		
		return $result;
	}

	/**
	 * Delete a token from database.
	 * 
	 * @param  int $tokenId The token id
	 * @return int          Then number of tokens deleted 
	 */
	public static function token_del($tokenId)
	{
		pixelpost\Filter::is_int($tokenId);
		
		$db = Db::create();
		
		$sql = 'DELETE FROM auth_token WHERE id = %d;';
		$sql = sprintf($sql, $tokenId);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();
		
		return $db->changes();		
	}
}
