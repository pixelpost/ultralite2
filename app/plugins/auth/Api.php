<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

/**
 * Provide API methods for managing photo content
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Api
{	
	
	public static function auth_version(pixelpost\Event $event)
	{
		$event->response = array('version' => Plugin::version());
	}
	
	public static function auth_request(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_request.php';
	}

	public static function auth_token(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_token.php';
	}
	
	public static function auth_refresh(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_refresh.php';
	}
	
	public static function auth_config_get(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_config_get.php';
	}
	
	public static function auth_config_set(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_config_set.php';
	}
	
	public static function auth_user_add(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_add.php';
	}
	
	public static function auth_user_set(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_set.php';
	}
	
	public static function auth_user_get(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_get.php';
	}
	
	public static function auth_user_del(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_del.php';
	}
	
	public static function auth_user_list(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'auth_user_list.php';
	}
}