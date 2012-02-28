<?php

namespace pixelpost\plugins\auth;

use pixelpost\Event;

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

	public static function auth_version(Event $event)
	{
		$event->response = array('version' => Plugin::version());
	}

	public static function auth_request(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_request.php';
	}

	public static function auth_token(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_token.php';
	}

	public static function auth_refresh(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_refresh.php';
	}

	public static function auth_config_get(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_config_get.php';
	}

	public static function auth_config_set(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_config_set.php';
	}

	public static function auth_user_add(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_add.php';
	}

	public static function auth_user_set(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_set.php';
	}

	public static function auth_user_get(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_get.php';
	}

	public static function auth_user_del(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_del.php';
	}

	public static function auth_user_list(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_list.php';
	}

	public static function auth_user_grant_add(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_grant_add.php';
	}

	public static function auth_user_grant_del(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_grant_del.php';
	}

	public static function auth_grant_add(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_grant_add.php';
	}

	public static function auth_grant_set(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_grant_set.php';
	}

	public static function auth_grant_get(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_grant_get.php';
	}

	public static function auth_grant_del(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_grant_del.php';
	}

	public static function auth_grant_list(Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_grant_list.php';
	}
}