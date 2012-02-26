<?php

namespace pixelpost\plugins\auth;

use pixelpost\Conf,
	pixelpost\Db,
	pixelpost\Filter,
	DateTime;

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
			token TEXT, challenge TEXT, nonce TEXT, user_id INTEGER, created INTEGER);');

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
	 * @param  int $user_id The user id
	 * @return int          The number of user deleted
	 */
	public static function user_del($user_id)
	{
		$db = Db::create();

		Filter::is_int($user_id);

		$query = 'DELETE FROM auth_user WHERE id = %d;';
		$query = sprintf($query, $user_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		try
		{
			foreach(self::user_grant_list_by_user($user_id) as $grant)
			{
				self::user_grant_unlink($user_id, $grant['id']);
			}
		}
		catch(ModelExceptionNoResult $e) {}

		return true;
	}

	/**
	 * Update a user in database.
	 *
	 * @param  int    $user_id  The user id
	 * @param  string $username The user name.
	 * @param  string $password The hash of the password.
	 * @return int              The number of user updated
	 */
	public static function user_update($user_id, $username, $password)
	{
		$db = Db::create();

		Filter::is_int($user_id);

		$query = 'UPDATE auth_user SET name = %s, pass = %s WHERE id = %d;';
		$query = sprintf($query, Db::escape($username), Db::escape($password), $user_id);

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
	 * @param  string $user_id The user id
	 * @return array           Data in a array with key: 'name', 'pass'
	 */
	public static function user_get_by_id($user_id)
	{
		Filter::is_int($user_id);

		$db = Db::create();

		$query = 'SELECT name, pass FROM auth_user WHERE id = %d LIMIT 1;';
		$query = sprintf($query, $user_id);

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

		$query = 'SELECT id, name FROM auth_user ORDER BY name ASC;';

		$result = $db->query($query);

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC)) $l[] = $row;

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
	 * @param  int $grant_id The grant id
	 * @return int          The number of grants deleted
	 */
	public static function grant_del($grant_id)
	{
		$db = Db::create();

		Filter::is_int($user_id);

		$query = 'DELETE FROM auth_grant WHERE id = %d;';
		$query = sprintf($query, $grant_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		try
		{
			foreach(self::user_grant_list_by_grant($grant_id) as $user)
			{
				self::user_grant_unlink($user['id'], $grant_id);
			}
		}
		catch(ModelExceptionNoResult $e) {}

		return true;
	}

	/**
	 * Update a grant in database.
	 *
	 * @param  int    $grant_id The grant id
	 * @param  string $name     The new grant name
	 * @return int              The number of grant updated
	 */
	public static function grant_update($grant_id, $name)
	{
		$db = Db::create();

		Filter::is_int($grant_id);

		$query = 'UPDATE auth_grant SET name = %s WHERE id = %d;';
		$query = sprintf($query, Db::escape($name), $grant_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * Retrieve a grant from database.
	 *
	 * @param  string $name The grant name
	 * @return int          The grant id
	 */
	public static function grant_get($name)
	{
		$db = Db::create();

		$query = 'SELECT id FROM auth_grant WHERE name = %s LIMIT 1;';
		$query = sprintf($query, Db::escape($name));

		$result = $db->querySingle($query, true);

		if ($result === false) throw new ModelExceptionSqlError();
		if (empty($result))    throw new ModelExceptionNoResult();

		return intval($result);
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

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC)) $l[] = $row;

		return $l;
	}

	/**
	 * Add a grant to a user.
	 *
	 * @param  int $user_id  The user id
	 * @param  int $grant_id The grant id
	 * @return int           The link id or 0 if the link exists
	 */
	public static function user_grant_link($user_id, $grant_id)
	{
		$db = Db::create();

		Filter::is_int($user_id);
		Filter::is_int($grant_id);

		$query = 'SELECT COUNT(id) FROM auth_user_grant WHERE user_id = %d AND grant_id = %d;';
		$query = sprintf($query, $user_id, $grant_id);

		if (intval($db->querySingle($query)) > 0) return 0;

		$query = 'INSERT INTO auth_user_grant (user_id, grant_id) VALUES (%d, %d);';
		$query = sprintf($query, $user_id, $grant_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $db->lastInsertRowID();
	}

	/**
	 * Remove a grant to a user
	 *
	 * @param  int $user_id  The user id
	 * @param  int $grant_id The grant id
	 * @return int           The number of link deleted
	 */
	public static function user_grant_unlink($user_id, $grant_id)
	{
		$db = Db::create();

		Filter::is_int($user_id);
		Filter::is_int($grant_id);

		$query = 'DELETE FROM auth_user_grant WHERE user_id = %d AND grant_id = %d;';
		$query = sprintf($query, $user_id, $grant_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * List user's grants
	 *
	 * @param  int   $user_id The user id
	 * @return array          Each item is an array with key 'id', 'name'
	 */
	public static function user_grant_list_by_user($user_id)
	{
		$db = Db::create();

		Filter::is_int($user_id);

		$query = 'SELECT g.id, g.name FROM auth_user_grant ug '
		       . 'LEFT JOIN auth_grant g ON g.id = ug.grant_id '
			   . 'WHERE ug.user_id = %d;';

		$result = $db->query(sprintf($query, $user_id));

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC)) $l[] = $row;

		return $l;
	}

	/**
	 * List user granted to $grant_id
	 *
	 * @param  int   $grant_id The grant id
	 * @return array           Each item is an array with key 'id', 'name'
	 */
	public static function user_grant_list_by_grant($grant_id)
	{
		$db = Db::create();

		Filter::is_int($grant_id);

		$query = 'SELECT u.id, u.name FROM auth_user_grant ug '
		       . 'LEFT JOIN auth_user u ON u.id = ug.user_id '
			   . 'WHERE ug.grant_id = %d;';

		$result = $db->query(sprintf($query, $grant_id));

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC)) $l[] = $row;

		return $l;
	}

	/**
	 * Add a challenge in database.
	 * This delete all expired challenge.
	 *
	 * @param  string $challenge The challenge
	 * @param  int    $user_id    The user id
	 * @param  int    $lifetime  The challenge lifetime
	 * @return int               The challenge id
	 */
	public static function challenge_add($challenge, $user_id, $lifetime)
	{
		Filter::assume_string($challenge);
		Filter::is_int($user_id);
		Filter::is_int($lifetime);

		$db   = Db::create();
		$date = new DateTime();

		// delete old challenge
		$sql = 'DELETE FROM auth_challenge WHERE expire < %s;';
		$sql = sprintf($sql, Db::escape(Db::date_serialize($date)));

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		// add a new one
		$challenge = Db::escape($challenge);
		$date      = Db::escape(Db::date_serialize($date->modify('+' . $lifetime . 'seconds')));

		$sql = 'INSERT INTO auth_challenge (challenge, user_id, expire) VALUES (%s, %d, %s);';
		$sql = sprintf($sql, $challenge, $user_id, $date);

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
		Filter::assume_string($challenge);

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
	 * @param  int $challenge_id The challenge id
	 * @return int              The number of challenge deleted
	 */
	public static function challenge_del($challenge_id)
	{
		Filter::is_int($challenge_id);

		$db = Db::create();

		$sql = 'DELETE FROM auth_challenge WHERE id = %d;';
		$sql = sprintf($sql, $challenge_id);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * Add a token in database.
	 * This is delete all other user's token.
	 *
	 * @param  string $token     The token
	 * @param  string $challenge The challenge
	 * @param  string $nonce     The nonce
	 * @param  int    $user_id   The user id
	 * @return int               The token id
	 */
	public static function token_add($token, $challenge, $nonce, $user_id)
	{
		Filter::assume_string($token);
		Filter::assume_string($challenge);
		Filter::assume_string($nonce);
		Filter::is_int($user_id);

		$db        = Db::create();

		// delete other user token
		$sql = 'DELETE FROM auth_token WHERE user_id = %d;';
		$sql = sprintf($sql, $user_id);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		$token     = Db::escape($token);
		$challenge = Db::escape($challenge);
		$nonce     = Db::escape($nonce);
		$date      = Db::escape(Db::date_serialize(new DateTime()));

		// add a new one
		$sql = 'INSERT INTO auth_token (token, challenge, nonce, user_id, created) '
			 . 'VALUES (%s, %s, %s, %d, %s);';
		$sql = sprintf($sql, $token, $challenge, $nonce, $user_id, $date);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		return $db->lastInsertRowID();
	}

	/**
	 * Change the token value of a token
	 *
	 * @param  int    $token_id The token Id to change
	 * @param  string $token    The new token
	 * @return int
	 */
	public static function token_update_token($token_id, $token)
	{
		Filter::assume_string($token);
		Filter::is_int($token_id);

		$db  = Db::create();

		$sql = 'UPDATE auth_token SET token = %s WHERE id = %d;';
		$sql = sprintf($sql, Db::escape($token), $token_id);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * Change the nonce value of a token
	 *
	 * @param  int    $token_id The token Id to change
	 * @param  string $nonce    The new nonce
	 * @return int
	 */
	public static function token_update_nonce($token_id, $nonce)
	{
		Filter::assume_string($nonce);
		Filter::is_int($token_id);

		$db  = Db::create();

		$sql = 'UPDATE auth_token SET nonce = %s WHERE id = %d;';
		$sql = sprintf($sql, Db::escape($nonce), $token_id);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * Retreive user's token from database
	 *
	 * @param  int   $user_id The user id
	 * @return array          Each items have key: 'id'
	 */
	public static function token_list_by_user($user_id)
	{
		Filter::is_int($user_id);

		$db = Db::create();

		$sql = 'SELECT id FROM auth_token WHERE user_id = %d;';
		$sql = sprintf($sql, $user_id);

		$result = $db->query($sql);

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC)) $l[] = $row;

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
		Filter::assume_string($token);

		$db  = Db::create();

		$sql = 'SELECT id, challenge, nonce, user_id, created FROM auth_token
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
	 * @param  int $token_id The token id
	 * @return int           Then number of tokens deleted
	 */
	public static function token_del($token_id)
	{
		Filter::is_int($token_id);

		$db = Db::create();

		$sql = 'DELETE FROM auth_token WHERE id = %d;';
		$sql = sprintf($sql, $token_id);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		return $db->changes();
	}
}
