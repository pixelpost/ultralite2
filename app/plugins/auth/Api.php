<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Event,
	pixelpost\core\Filter,
	pixelpost\plugins\api\Exception\FieldRequired,
	pixelpost\plugins\api\Exception\FieldEmpty;

/**
 * Provide API methods for managing auth content
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Api
{
	/**
	 * Verify that a grant name exists.
	 * Becareful, next arguments are references.
	 *
	 * @param  string $name The grant name
	 * @param  int    $id   Will be set to the grant id
	 * @return bool
	 **/
	protected static function check_grant_name($name, &$id = null)
	{
		try
		{
			$id = Model::grant_get($name);
			return true;
		}
		catch(ModelExceptionNoResult $e)
		{
			return false;
		}
	}

	/**
	 * Verify that a user name exists.
	 * Becareful, next arguments are references.
	 *
	 * @param  string $name  The user name
	 * @param  int    $id    Will be set to the user id
	 * @param  string $pass  Will be set to the user password
	 * @param  string $email Will be set to the user email
	 * @return bool
	 **/
	protected static function check_user_name($name, &$id = null, &$pass = null, &$email = null)
	{
		try
		{
			// create $id, $pass and $email
			extract(Model::user_get_by_name($name));
			return true;
		}
		catch(ModelExceptionNoResult $e)
		{
			return false;
		}
	}

	/**
	 * Verify that a entity key exists.
	 * Becareful, next arguments are references.
	 *
	 * @param  string $key     The entity key
	 * @param  int    $id      Will be set to the entity id
	 * @param  int    $user_id Will be set to the user id
	 * @return bool
	 **/
	protected static function check_entity_key($key, &$id = null, &$user_id = null)
	{
		try
		{
			// create id, name, user_id, private_key
			extract(Model::entity_get_by_public_key($key));
			return true;
		}
		catch(ModelExceptionNoResult $e)
		{
			return false;
		}
	}

	/**
	 * Retrieve a field from a request.
	 *
	 * @param  string   $field   The field name to retrieve
	 * @param  stdClass $request The event request
	 * @param  string   $method  The method name
	 * @return mixed
	 **/
	protected static function get_required($field, $request, $method)
	{
		$data = isset($request->$field) ? trim($request->$field) : false;

		if ($data === false) throw new FieldRequired($method, $field);
		if ($data === '')    throw new FieldEmpty($field);

		Filter::check_encoding($data);

		return $data;
	}

	/**
	 * Retrieve a field from a request, if exists else return False.
	 *
	 * @param  string   $field   The field name to retrieve
	 * @param  stdClass $request The event request
	 * @param  string   $method  The method name
	 * @return mixed
	 **/
	protected static function get_optional($field, $request, $method)
	{
		$data = isset($request->$field) ? trim($request->$field) : false;

		if ($data === '') throw new FieldEmpty($field);

		$data and Filter::check_encoding($data);

		return $data;
	}

	public static function __callStatic($__name, $__args)
	{
		$event = current($__args);

		require __DIR__ . '/api/' . $__name . '.php';
	}

	public static function auth_version(Event $event)
	{
		$event->response = array('version' => Plugin::version());
	}
}