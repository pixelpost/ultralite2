<?php

namespace pixelpost\plugins\auth;

use pixelpost;

/**
 * Auth management for pixelpost.
 *
 * Tracks Event :
 *
 * auth.version
 * auth.request
 * auth.token
 * auth.refresh
 * auth.config.get
 * auth.config.set
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin implements pixelpost\PluginInterface
{
	/**
	 * The token provided by the api call
	 * 
	 * @var string
	 */
	protected static $_token     = '';
	
	/**
	 * The signature provided by the api call
	 * 
	 * @var string
	 */
	protected static $_signature = '';
	
	/**
	 * The authentified username if auth success
	 * 
	 * @var string
	 */
	protected static $_username  = '';
	
	/**
	 * The authentified userId if auth success
	 * 
	 * @var int
	 */
	protected static $_userId    = 0;
	
	public static function version()
	{
		return '0.0.1';
	}
	
	public static function depends()
	{
		return array('api' => '0.0.1');
	}	

	public static function install()
	{
		$configuration = '{ "lifetime" : 300 }';
		
		$conf = pixelpost\Config::create();
		
		$conf->plugin_auth = json_decode($configuration);
		
		$conf->save();
		
		Model::table_create();
		
		return true;
	}

	public static function uninstall()
	{
		$conf = pixelpost\Config::create();
		
		unset($conf->plugin_auth);
		
		$conf->save();
		
		Model::table_delete();
		
		return true;
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		pixelpost\Event::register('request.api.decoded', '\\' . __CLASS__ . '::request_api_decoded');
		pixelpost\Event::register('auth.version',    '\\' . __CLASS__ . '::auth_version');
		pixelpost\Event::register('auth.request',    '\\' . __CLASS__ . '::auth_request');
		pixelpost\Event::register('auth.token',      '\\' . __CLASS__ . '::auth_token');
		pixelpost\Event::register('auth.refresh',    '\\' . __CLASS__ . '::auth_refresh');
		pixelpost\Event::register('auth.config.get', '\\' . __CLASS__ . '::auth_config_get');
		pixelpost\Event::register('auth.config.set', '\\' . __CLASS__ . '::auth_config_set');
		// TODO add thoses events
		//pixelpost\Event::register('auth.user.add', '...');
		//pixelpost\Event::register('auth.user.set', '...');
		//pixelpost\Event::register('auth.user.get', '...');
		//pixelpost\Event::register('auth.user.del', '...');
		//pixelpost\Event::register('auth.user.list', '...');
		//pixelpost\Event::register('auth.grant.add', '...');
		//pixelpost\Event::register('auth.grant.set', '...');
		//pixelpost\Event::register('auth.grant.get', '...');
		//pixelpost\Event::register('auth.grant.del', '...');
		//pixelpost\Event::register('auth.grant.list', '...');
	}
	
	public static function auth_version(pixelpost\Event $event)
	{
		$event->response = array('version' => self::version());
	}
	
	public static function auth_request(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'auth_request.php';
	}

	public static function auth_token(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'auth_token.php';
	}
	
	public static function auth_refresh(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'auth_refresh.php';
	}
	
	public static function auth_config_get(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'auth_config_get.php';
	}
	
	public static function auth_config_set(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'auth_config_set.php';
	}
	
	/**
	 * Store token and signature data if they are present in a api request
	 * 
	 * @param pixelpost\Event $event 
	 */
	public static function request_api_decoded(pixelpost\Event $event)
	{
		// if we have auth data, we store them.
		if (isset($event->request->token) && isset($event->request->signature))
		{
			self::$_token     = $event->request->token;
			self::$_signature = $event->request->signature;
		}
	}

	/**
	 * Return the authentified username or an empty string
	 * 
	 * @return string
	 */
	public static function get_username()
	{
		return self::$_username;
	}

	/**
	 * Return the authentified userId or 0
	 * 
	 * @return string
	 */
	public static function get_user_id()
	{
		return self::$_userId;
	}
	
	/**
	 * Return if a user is authentified
	 * 
	 * @return bool 
	 */
	public static function is_auth()
	{
		// check if user is allready authentified
		if (self::$_userId != '')    return true;
		
		// check if we have authentification data
		if (self::$_token == '')     return false;
		if (self::$_signature == '') return false;

		// retrieve the token infos
		try
		{
			$token = Model::token_get(self::$_token);
		}
		catch(ModelExceptionNoResult $e)
		{
			throw new ApiException('bad_token', 'This token is invalid.');
		}
		
		// retrieve user data
		$user = Model::user_get_by_id($token['user_id']);
		
		// retrieve configuration
		$conf = pixelpost\Config::create();

		// generate the signature
		$auth = new Auth();
		$signature = $auth->set_lifetime($conf->plugin_auth->lifetime)
   				          ->set_domain($event->http_request->get_host())
					      ->set_username($user['name'])
			              ->set_password_hash($user['pass'])
				          ->set_challenge($token['challenge'])
					      ->get_signature();

		// check signature
		if (self::$_signature != $signature) return false;
		
		// check if the token is perempted.
		if (self::$_token != $auth->get_token())
		{
			throw new ApiException('old_token', 'This token have expired.');
		}
		
		// store authentified username and id
		self::$_username = $user['name'];
		self::$_userId   = $token['user_id'];		
	}
	
	/**
	 * Return if a user is granted to $grantRequested
	 * Possible grants are : read | write | config | delete
	 * 
	 * @return bool 
	 */
	public static function is_granted($grantRequested)
	{
		// check the authentification
		if (!self::is_auth()) return false;
		
		// retrieve all user's grant
		try
		{
			$grants = Model::user_grant_list_by_user(self::$_userId);		
		}
		catch(ModelExceptionNoResult $e)
		{
			return false;
		}

		// check if the user is granted to $grantRequested
		$granted = false;
		
		foreach($grants as $grant)
		{
			if ($grant['name'] == $grantRequested) return true;
		}
		
		return false;
	}
}
