<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Db,
	pixelpost\core\Filter,
	DateTime,
	Closure;

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
	 * Create tables in database.
	 */
	public static function table_create()
	{
		$db = Db::create();

		$db->exec('CREATE TABLE auth_user (id INTEGER PRIMARY KEY,
			name TEXT, pass TEXT, email TEXT);');

		$db->exec('CREATE TABLE auth_entity (id INTEGER PRIMARY KEY,
			user_id INTEGER, is_me INT, name TEXT, public_key TEXT, private_key TEXT);');

		$db->exec('CREATE TABLE auth_grant (id INTEGER PRIMARY KEY,
			name TEXT);');

		$db->exec('CREATE TABLE auth_entity_grant (id INTEGER PRIMARY KEY,
			entity_id INTEGER, grant_id INTEGER);');

		$db->exec('CREATE TABLE auth_challenge (id INTEGER PRIMARY KEY,
			challenge TEXT, entity_id INTEGER, session TEXT, expire INTEGER);');

		$db->exec('CREATE TABLE auth_token (id INTEGER PRIMARY KEY,
			token TEXT, challenge TEXT, nonce TEXT, entity_id INTEGER,
			session TEXT, created INTEGER, expire INTEGER);');

		$db->exec('INSERT INTO auth_grant (name) VALUES ("read");');   // read content
		$db->exec('INSERT INTO auth_grant (name) VALUES ("write");');  // set / add content
		$db->exec('INSERT INTO auth_grant (name) VALUES ("delete");'); // del content
		$db->exec('INSERT INTO auth_grant (name) VALUES ("config");'); // change configuration
		$db->exec('INSERT INTO auth_grant (name) VALUES ("admin");');  // change user, grants
	}

	/**
	 * Update tables in table to the last version.
	 */
	public static function table_update()
	{
		// actually there nothing to update
	}

	/**
	 * Drop tables in database.
	 */
	public static function table_delete()
	{
		$db = Db::create();
		$db->exec('DROP TABLE auth_user;');
		$db->exec('DROP TABLE auth_grant;');
		$db->exec('DROP TABLE auth_entity_grant;');
		$db->exec('DROP TABLE auth_challenge;');
		$db->exec('DROP TABLE auth_token;');
	}

	/**
	 * Add a user in database.
	 *
	 * @param  string $username The user name.
	 * @param  string $password The hash of the password.
	 * @param  string $email    The user email.
	 * @return int              The user id
	 */
	public static function user_add($username, $password, $email)
	{
		$db = Db::create();

		$username = Db::escape($username);
		$password = Db::escape($password);
		$email    = Db::escape($email);

		$query = 'INSERT INTO auth_user (name, pass, email) VALUES (%s, %s, %s);';
		$query = sprintf($query, $username, $password, $email);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		$user_id = $db->lastInsertRowID();

		// this is never attempted to be used
		$pub_key  = md5($username);
		$priv_key = md5($password);

		$entity_id = self::entity_add($user_id, $username, $pub_key, $priv_key);

		// this entity is just here to link user and grants.
		$query = 'UPDATE auth_entity SET is_me = 1 WHERE id = %d;';
		$query = sprintf($query, $entity_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $user_id;
	}

	/**
	 * Delete a user from database.
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
			foreach(self::entity_list_by_user($user_id) as $entity)
			{
				self::entity_del($entity['id']);
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
	 * @param  string $email    The user email.
	 * @return int              The number of user updated
	 */
	public static function user_update($user_id, $username, $password, $email)
	{
		$db = Db::create();

		Filter::is_int($user_id);

		$username = Db::escape($username);
		$password = Db::escape($password);
		$email    = Db::escape($email);

		$query = 'UPDATE auth_user SET name = %s, pass = %s, email = %s WHERE id = %d;';
		$query = sprintf($query, $username, $password, $email, $user_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		$query = 'UPDATE auth_entity SET name = %s WHERE user_id = %d AND is_me = 1;';
		$query = sprintf($query, Db::escape($username), Db::escape($password), $user_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * Retrieve a user data by its name.
	 *
	 * @param  string $username The user name
	 * @return array            key: name, pass, email
	 */
	public static function user_get_by_name($username)
	{
		$db = Db::create();

		$query = 'SELECT id, pass, email FROM auth_user WHERE name = %s LIMIT 1;';
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
	 * @return array           key: name, pass, email
	 */
	public static function user_get_by_id($user_id)
	{
		Filter::is_int($user_id);

		$db = Db::create();

		$query = 'SELECT name, pass, email FROM auth_user WHERE id = %d LIMIT 1;';
		$query = sprintf($query, $user_id);

		$result = $db->querySingle($query, true);

		if ($result === false) throw new ModelExceptionSqlError();
		if (empty($result))    throw new ModelExceptionNoResult();

		return $result;
	}

	/**
	 * List all user in database.
	 *
	 * @param  Closure $todo Add processing on an item
	 * @return array         item: id, name, pass, email
	 */
	public static function user_list(Closure $todo = null)
	{
		$db = Db::create();

		$query = 'SELECT id, name, pass, email FROM auth_user ORDER BY name ASC;';

		$result = $db->query($query);

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC))
		{
			if (is_null($todo) || $todo($row) !== false) $l[] = $row;
		}

		return $l;
	}

	/**
	 * Retrieve the personnal entity id of the user $user_id.
	 *
	 * @param  int $user_id The user id
	 * @return int          The entity id
	 */
	public static function user_get_entity_id($user_id)
	{
		Filter::is_int($user_id);

		$db = Db::create();

		$query = 'SELECT id FROM auth_entity WHERE user_id = %d AND is_me = 1 LIMIT 1;';
		$query = sprintf($query, $user_id);

		$entity_id = $db->querySingle($query);

		if ($entity_id === false) throw new ModelExceptionSqlError();
		if ($entity_id === null)  throw new ModelExceptionNoResult();

		return $entity_id;
	}

	/**
	 * Add an entity in database.
	 *
	 * @param  int    $user_id  The user id
	 * @param  string $name     The entity name
	 * @param  string $pub_key  The entity public key
	 * @param  string $priv_key The entity private key
	 * @return int              The entity id
	 */
	public static function entity_add($user_id, $name, $pub_key, $priv_key)
	{
		Filter::is_int($user_id);

		$name     = Db::escape($name);
		$pub_key  = Db::escape($pub_key);
		$priv_key = Db::escape($priv_key);

		$query = 'INSERT INTO auth_entity (is_me, user_id, name, public_key, private_key) '
		       . 'VALUES (0, %d, %s, %s, %s);';
		$query = sprintf($query, $user_id, $name, $pub_key, $priv_key);

		$db = Db::create();

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $db->lastInsertRowID();
	}

	/**
	 * Remove an entity from database.
	 *
	 * @param  int  $entity_id The user id
	 * @return bool
	 */
	public static function entity_del($entity_id)
	{
		Filter::is_int($entity_id);

		$query = 'DELETE FROM auth_entity WHERE id = %d;';
		$query = sprintf($query, $entity_id);

		$db = Db::create();

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		try
		{
			foreach(self::entity_grant_list_by_entity($entity_id) as $grant)
			{
				self::entity_grant_unlink($user_id, $grant['id']);
			}
		}
		catch(ModelExceptionNoResult $e) {}

		try
		{
			foreach (self::token_list_by_entity($entity_id) as $token)
			{
				self::token_del($token['id']);
			}
		}
		catch(ModelExceptionNoResult $e) {}

		return true;
	}

	/**
	 * Change an entity in database.
	 *
	 * @param  int    $entity_id The entity id
	 * @param  string $name      The entity name
	 * @return int               The number of entity updated
	 */
	public static function entity_update($entity_id, $name)
	{
		Filter::is_int($entity_id);

		$query = 'UPDATE auth_entity SET name = %s WHERE id = %d;';
		$query = sprintf($query, Db::escape($name));

		$db = Db::create();

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * Retrieve an entity by its id.
	 *
	 * @param  int   $entity_id The entity id
	 * @return array            key: name, is_me, user_id, public_key, private_key
	 */
	public static function entity_get_by_id($entity_id)
	{
		Filter::is_int($entity_id);

		$query = 'SELECT name, is_me, user_id, public_key, private_key FROM auth_entity '
		       . 'WHERE id = %d LIMIT 1;';
		$query = sprintf($query, $entity_id);

		$db = Db::create();

		$result = $db->querySingle($query, true);

		if ($result === false) throw new ModelExceptionSqlError();
		if (empty($result))    throw new ModelExceptionNoResult();

		return $result;
	}

	/**
	 * Retrieve an entity by its public key.
	 *
	 * @param  int   $public_key The entity public key
	 * @return array             key: id, name, is_me, user_id, private_key
	 */
	public static function entity_get_by_public_key($public_key)
	{
		$query = 'SELECT id, name, is_me, user_id, private_key FROM auth_entity '
		       . 'WHERE public_key = %s LIMIT 1;';
		$query = sprintf($query, Db::escape($public_key));

		$db = Db::create();

		$result = $db->querySingle($query, true);

		if ($result === false) throw new ModelExceptionSqlError();
		if (empty($result))    throw new ModelExceptionNoResult();

		return $result;
	}

	/**
	 * Return all entities belongs to a user.
	 *
	 * @param  int     $user_id  The user id
	 * @param  Closure $todo     Add processing on an item
	 * @return array             item: id, name, is_me, public_key, private_key
	 */
	public static function entity_list_by_user($user_id, Closure $todo = null)
	{
		Filter::is_int($user_id);

		$db = Db::create();

		$query = 'SELECT id, is_me, name, public_key, private_key FROM auth_entity '
		       . 'WHERE user_id = %d ORDER BY name ASC;';

		$result = $db->query(sprintf($query, $user_id));

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC))
		{
			$row['is_me'] = (bool) $row['is_me'];

			if ($todo && $todo($row) === false) continue;

			$l[] = $row;
		}

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

		$grant_id = $db->querySingle($query);

		if ($grant_id === false) throw new ModelExceptionSqlError();
		if ($grant_id === null)  throw new ModelExceptionNoResult();

		return $grant_id;
	}

	/**
	 * List all grants in database.
	 *
	 * @param  Closure $todo Add processing on an item
	 * @return array         item: id, name
	 */
	public static function grant_list(Closure $todo = null)
	{
		$db = Db::create();

		$query = 'SELECT id, name FROM auth_grant ORDER BY name ASC;';

		$result = $db->query($query);

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC))
		{
			if (is_null($todo) || $todo($row) !== false) $l[] = $row;
		}

		return $l;
	}

	/**
	 * Add a grant to an entity.
	 *
	 * @param  int $entity_id The entity id
	 * @param  int $grant_id  The grant id
	 * @return int            The link id or 0 if the link exists
	 */
	public static function entity_grant_link($entity_id, $grant_id)
	{
		$db = Db::create();

		Filter::is_int($entity_id);
		Filter::is_int($grant_id);

		$query = 'SELECT COUNT(id) FROM auth_entity_grant WHERE entity_id = %d AND grant_id = %d;';
		$query = sprintf($query, $entity_id, $grant_id);

		if (intval($db->querySingle($query)) > 0) return 0;

		$query = 'INSERT INTO auth_entity_grant (entity_id, grant_id) VALUES (%d, %d);';
		$query = sprintf($query, $entity_id, $grant_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $db->lastInsertRowID();
	}

	/**
	 * Remove a grant to an entity.
	 *
	 * @param  int $entity_id The entity id
	 * @param  int $grant_id  The grant id
	 * @return int            The number of link deleted
	 */
	public static function entity_grant_unlink($entity_id, $grant_id)
	{
		$db = Db::create();

		Filter::is_int($entity_id);
		Filter::is_int($grant_id);

		$query = 'DELETE FROM auth_entity_grant WHERE entity_id = %d AND grant_id = %d;';
		$query = sprintf($query, $entity_id, $grant_id);

		if (!$db->exec($query)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * List entity's grants.
	 *
	 * @param  int     $entity_id The entity id
	 * @param  Closure $todo      Add processing on an item
	 * @return array              item: id, name
	 */
	public static function entity_grant_list_by_entity($entity_id, Closure $todo = null)
	{
		$db = Db::create();

		Filter::is_int($entity_id);

		$query = 'SELECT g.id, g.name FROM auth_entity_grant eg '
		       . 'LEFT JOIN auth_grant g ON g.id = eg.grant_id '
			   . 'WHERE eg.entity_id = %d;';

		$result = $db->query(sprintf($query, $entity_id));

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC))
		{
			if (is_null($todo) || $todo($row) !== false) $l[] = $row;
		}

		return $l;
	}

	/**
	 * List entity granted to $grant_id.
	 *
	 * @param  int     $grant_id The grant id
	 * @param  Closure $todo     Add processing on an item
	 * @return array             item: id, name, is_me, user_id, public_key, private_key
	 */
	public static function entity_grant_list_by_grant($grant_id, Closure $todo = null)
	{
		$db = Db::create();

		Filter::is_int($grant_id);

		$query = 'SELECT e.id, e.name, e.is_me, e.user_id, e.public_key, e.private_key '
		       . 'FROM auth_entity_grant eg '
		       . 'LEFT JOIN auth_entity e ON e.id = eg.entity_id '
			   . 'WHERE eg.grant_id = %d;';

		$result = $db->query(sprintf($query, $grant_id));

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC))
		{
			$row['is_me'] = (bool) $row['is_me'];

			if ($todo && $todo($row) === false) continue;

			$l[] = $row;
		}

		return $l;
	}

	/**
	 * Add a challenge in database.
	 * This delete all expired challenge.
	 *
	 * @param  string $challenge The challenge
	 * @param  int    $entity_id The entity id
	 * @param  string $session   The auth session name
	 * @param  int    $lifetime  The challenge lifetime
	 * @return int               The challenge id
	 */
	public static function challenge_add($challenge, $entity_id, $session, $lifetime)
	{
		Filter::is_int($entity_id);
		Filter::is_int($lifetime);

		$db = Db::create();

		// delete old challenge
		$date = new DateTime();

		$expire = Db::escape(Db::date_serialize($date));

		$sql = 'DELETE FROM auth_challenge WHERE expire < %s;';
		$sql = sprintf($sql, $expire);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		$date->modify('+' . $lifetime . 'seconds');

		$challenge = Db::escape($challenge);
		$session   = Db::escape($session);
		$expire    = Db::escape(Db::date_serialize($date));

		// delete old challenge
		$sql = 'DELETE FROM auth_challenge WHERE entity_id = %d AND session = %s;';
		$sql = sprintf($sql, $entity_id, $session);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		// add a new one
		$sql = 'INSERT INTO auth_challenge (challenge, entity_id, session, expire) '
		     . 'VALUES (%s, %d, %s, %s);';
		$sql = sprintf($sql, $challenge, $entity_id, $session, $expire);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		return $db->lastInsertRowID();
	}

	/**
	 * Retrieve a challenge from database.
	 *
	 * @param  string $challenge The challenge
	 * @return array             key: id, entity_id, session, exprire
	 */
	public static function challenge_get($challenge)
	{
		Filter::assume_string($challenge);

		$sql = 'SELECT id, entity_id, session, expire FROM auth_challenge '
		     . 'WHERE challenge = %s LIMIT 1;';
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
	 * @return int               The number of challenge deleted
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
	 * This is delete all other entity's token.
	 *
	 * @param  string $token     The token
	 * @param  string $challenge The challenge
	 * @param  string $nonce     The nonce
	 * @param  int    $entity_id The entity id
	 * @param  string $session   The auth session name
	 * @param  string $lifetime  The token lifetime in seconds
	 * @return int               The token id
	 */
	public static function token_add($token, $challenge, $nonce, $entity_id, $session, $lifetime)
	{
		Filter::is_int($entity_id);
		Filter::is_int($lifetime);

		$created = new DateTime();
		$expire = clone $created;
		$expire->modify('+' . $lifetime . 'seconds');

		$db = Db::create();

		$token     = Db::escape($token);
		$challenge = Db::escape($challenge);
		$nonce     = Db::escape($nonce);
		$session   = Db::escape($session);
		$created   = Db::escape(Db::date_serialize($created));
		$expire    = Db::escape(Db::date_serialize($expire));

		// delete other entity auth session
		$sql = 'DELETE FROM auth_token WHERE entity_id = %d AND session = %s;';
		$sql = sprintf($sql, $entity_id, $session);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		// add a new one
		$sql = 'INSERT INTO auth_token (token, challenge, nonce, entity_id, session, created, expire) '
			 . 'VALUES (%s, %s, %s, %d, %s, %s, %s);';
		$sql = sprintf($sql, $token, $challenge, $nonce, $entity_id, $session, $created, $expire);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		return $db->lastInsertRowID();
	}

	/**
	 * Change the token value of a token.
	 *
	 * @param  int    $token_id The token Id to change
	 * @param  string $token    The new token
	 * @param  int    $lifetime The token lifetime in seconds
	 * @return int
	 */
	public static function token_update_token($token_id, $token, $lifetime)
	{
		Filter::is_int($token_id);
		Filter::is_int($lifetime);

		$expire = new DateTime();
		$expire->modify('+' . $lifetime . 'seconds');

		$db  = Db::create();

		$token  = Db::escape($token);
		$expire = Db::escape(Db::date_serialize($expire));

		$sql = 'UPDATE auth_token SET token = %s, expire = %s WHERE id = %d;';
		$sql = sprintf($sql, $token, $expire, $token_id);

		if (!$db->exec($sql)) throw new ModelExceptionSqlError();

		return $db->changes();
	}

	/**
	 * Change the nonce value of a token.
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
	 * Retreive entity's token from database.
	 *
	 * @param  int     $entity_id The entity id
	 * @param  Closure $todo      Add processing on an item
	 * @return array              item: id, token, challenge, nonce, entity_id, session, created, expire
	 */
	public static function token_list_by_entity($entity_id, Closure $todo = null)
	{
		Filter::is_int($entity_id);

		$db = Db::create();

		$sql = 'SELECT id, token, challenge, nonce, entity_id, session, created, expire '
			 . 'FROM auth_token WHERE entity_id = %d;';
		$sql = sprintf($sql, $entity_id);

		$result = $db->query($sql);

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC))
		{
			$row['created'] = Db::date_unserialize($row['created']);
			$row['expire']  = Db::date_unserialize($row['expire']);

			if (is_null($todo) || $todo($row) !== false) $l[] = $row;
		}

		return $l;
	}

	/**
	 * Retreive entity's token from database.
	 *
	 * @param  int     $user_id The entity id
	 * @param  Closure $todo    Add processing on an item
	 * @return array            item: id, token, challenge, nonce, entity_id, session, created, expire
	 */
	public static function token_list_by_user($user_id, Closure $todo = null)
	{
		Filter::is_int($user_id);

		$db = Db::create();

		$sql = 'SELECT t.id, t.token, t.challenge, t.nonce, t.entity_id, t.session, t.created, t.expire '
			 . 'FROM auth_token t JOIN auth_entity e ON e.id = t.entity_id WHERE e.user_id = %d;';
		$sql = sprintf($sql, $user_id);

		$result = $db->query($sql);

		if ($result === true)  throw new ModelExceptionNoResult();
		if ($result === false) throw new ModelExceptionSqlError();

		$l = array();

		while(false !== $row = $result->fetchArray(SQLITE3_ASSOC))
		{
			$row['created'] = Db::date_unserialize($row['created']);
			$row['expire']  = Db::date_unserialize($row['expire']);

			if (is_null($todo) || $todo($row) !== false) $l[] = $row;
		}

		return $l;
	}

	/**
	 * Retrieve a token from database.
	 *
	 * @param  string $token The token
	 * @return array         key: id, challenge, entity_id, session, created, expire
	 */
	public static function token_get($token)
	{
		$db = Db::create();

		$sql = 'SELECT id, challenge, nonce, entity_id, session, created, expire '
		     . 'FROM auth_token WHERE token = %s LIMIT 1;';
		$sql = sprintf($sql, Db::escape($token));

		$result = $db->querySingle($sql, true);

		if ($result === false)  throw new ModelExceptionSqlError();
		if (empty($result))     throw new ModelExceptionNoResult();

		$result['created'] = Db::date_unserialize($result['created']);
		$result['expire']  = Db::date_unserialize($result['expire']);

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
