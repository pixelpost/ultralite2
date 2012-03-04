<?php

namespace pixelpost\plugins\auth;

use pixelpost,
	pixelpost\Config,
	pixelpost\Filter,
	pixelpost\Event,
	pixelpost\PluginInterface,
	pixelpost\plugins\api\Exception as ApiException;

/**
 * Auth management for pixelpost.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin implements PluginInterface
{
	/**
	 * @var string The api method called
	 */
	protected static $_api_method = '';

	/**
	 * @var int The token id of the auth
	 */
	protected static $_api_token = 0;

	/**
	 * @var string The token nonce of the auth
	 */
	protected static $_api_nonce = '';

	/**
	 * @var string The authentified username if auth success
	 */
	protected static $_user_name = '';

	/**
	 * @var string The authentified password hashed if auth success
	 */
	protected static $_user_pass = '';

	/**
	 * @var int The authentified user_id if auth success
	 */
	protected static $_user_id  = 0;

	public static function version()
	{
		return '0.0.1';
	}

	public static function depends()
	{
		return array('api' => '0.0.1', 'router' => '0.0.1');
	}

	public static function install()
	{
		$configuration = '{ "lifetime" : 300 }';

		$conf = Config::create();

		$conf->plugin_auth = json_decode($configuration);

		$conf->save();

		Model::table_create();

		return true;
	}

	public static function uninstall()
	{
		$conf = Config::create();

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
		$selfClass   = '\\' . __CLASS__;
		$apiClass    = '\\' . __NAMESPACE__ . '\\Api';
		$adminClass  = '\\' . __NAMESPACE__ . '\\Admin';
		$routerClass = '\pixelpost\plugins\Router\Plugin';

		// check api auth before api event method is called
		Event::register('api.request.raw',  $selfClass . '::api_request');
		// check api hmac before api response is sent
		Event::register('api.response.raw', $selfClass . '::api_response');
		// check admin auth before admin event method is called
		Event::register('request.admin',    $selfClass . '::request_admin', 99);

		Event::register('api.auth.version',        $apiClass . '::auth_version');
		Event::register('api.auth.request',        $apiClass . '::auth_request');
		Event::register('api.auth.token',          $apiClass . '::auth_token');
		Event::register('api.auth.refresh',        $apiClass . '::auth_refresh');
		Event::register('api.auth.config.get',     $apiClass . '::auth_config_get');
		Event::register('api.auth.config.set',     $apiClass . '::auth_config_set');
		Event::register('api.auth.user.add',       $apiClass . '::auth_user_add');
		Event::register('api.auth.user.set',       $apiClass . '::auth_user_set');
		Event::register('api.auth.user.get',       $apiClass . '::auth_user_get');
		Event::register('api.auth.user.del',       $apiClass . '::auth_user_del');
		Event::register('api.auth.user.list',      $apiClass . '::auth_user_list');
		Event::register('api.auth.grant.add',      $apiClass . '::auth_grant_add');
		Event::register('api.auth.grant.set',      $apiClass . '::auth_grant_set');
		Event::register('api.auth.grant.get',      $apiClass . '::auth_grant_get');
		Event::register('api.auth.grant.del',      $apiClass . '::auth_grant_del');
		Event::register('api.auth.grant.list',     $apiClass . '::auth_grant_list');
		Event::register('api.auth.user.grant.add', $apiClass . '::auth_user_grant_add');
		Event::register('api.auth.user.grant.del', $apiClass . '::auth_user_grant_del');

		Event::register('admin.template.footer', $adminClass . '::template_footer');
		Event::register('admin.template.css',    $adminClass . '::template_css');
		Event::register('admin.template.js',     $adminClass . '::template_js');

		Event::register('admin.auth',            $routerClass . '::route');
		Event::register('admin.auth.api-bridge', $adminClass  . '::page_api_bridge');
	}

	/**
	 * Verify if a user is authentified for admin pages. if not print the login
	 * pages and break the request.admin chain (cause the original admin page
	 * called is not generated).
	 *
	 * @param pixelpost\Event $event
	 * @return bool
	 */
	public static function request_admin(Event $event)
	{
		// retrieve the web admin page called
		list(,$page) = $event->request->get_params() + array('admin', 'index');

		// skip page don't need authentification to be checked
		switch($page)
		{
		case '404':
			return true;
		case 'auth-login':
			WebAuth::login($event->request);
			return false;
		case 'auth-forget':
			WebAuth::forget($event->request);
			return false;
		case 'auth-reset':
			WebAuth::reset($event->request);
			return false;
		case 'auth-disconnect':
			WebAuth::disconnect($event->request);
			return false;
		default:
			// check if user is authentificated
			if (WebAuth::check($id, $name))
			{
				// register the identification (permit to internal api call to be
				// authentified too).
				self::$_user_id   = $id;
				self::$_user_name = $name;
				// call admin page
				return true;
			}
			else
			{
				// publish authentification form
				WebAuth::auth();
				// stop signal request.admin chain (admin plugin is not called).
				return false;
			}
		}
	}

	/**
	 * Return the authentified username or an empty string
	 *
	 * @return string
	 */
	public static function get_username()
	{
		return self::$_user_name;
	}

	/**
	 * Return the authentified user_id or 0
	 *
	 * @return string
	 */
	public static function get_user_id()
	{
		return self::$_user_id;
	}

	/**
	 * Return if a user is authentified
	 *
	 * @return bool
	 */
	public static function is_auth()
	{
		// check if user is allready authentified
		if (self::$_user_id != 0) return true;
	}

	/**
	 * Check authentication if provided in api request.
	 *
	 * @param pixelpost\Event $event
	 */
	public static function api_request(Event $event)
	{
		// if we a auth request, seems look good with auth data, we store it
		if (!isset($event->request->token))   return;
		if (!isset($event->request->hmac))    return;
		if (!isset($event->request->method))  return;
		if (!isset($event->request->request)) return;

		// retrieve the token infos
		try
		{
			$token = Model::token_get($event->request->token);
		}
		catch(ModelExceptionNoResult $e)
		{
			throw new ApiException('bad_token', 'The token is not valid.');
		}

		// retrieve user data
		$user = Model::user_get_by_id($token['user_id']);

		// retrieve configuration
		$conf = Config::create();

		// prepare auth class
		$auth = new Auth();
		$auth->set_lifetime($conf->plugin_auth->lifetime)
			 ->set_key($conf->uid)
			 ->set_username($user['name'])
			 ->set_password_hash($user['pass'])
			 ->set_challenge($token['challenge'])
			 ->set_nonce($token['nonce']);

		// check if the token is perempted.
		if ($auth->get_token() != $event->request->token)
		{
			throw new ApiException('old_token', 'The token has expired.');
		}

		// extract method and request parts of the request
		$method  = $event->request->method;
		$request = Filter::object_to_array($event->request->request);

		// check signature
		if ($auth->hmac($method, $request) != $event->request->hmac)
		{
			throw new ApiException('bad_hmac', 'The hmac is not valid.');
		}

		// store authentified username and id, generate a new nonce
		self::$_api_token  = $token['id'];
		self::$_api_nonce  = $token['nonce'];
		self::$_user_id    = $token['user_id'];
		self::$_user_name  = $user['name'];
		self::$_user_pass  = $user['pass'];
	}

	/**
	 * Create hmac and nonce in api response if necessary
	 *
	 * @param pixelpost\Event $event
	 */
	public static function api_response(Event $event)
	{
		if (self::$_api_token == 0) return;

		// prepare auth class
		$auth = new Auth();
		$auth->set_key(Config::create()->uid)
			 ->set_password_hash(self::$_user_pass)
			 ->set_username(self::$_user_name)
			 ->set_nonce(self::$_api_nonce);

		// create the new nonce
		$nonce = $auth->get_nonce();

		// hmac the response
		$hmac  = $auth->hmac($nonce, $event->response['response']);

		// replace the nonce in for this token
		Model::token_update_nonce(self::$_api_token, $nonce);

		// add the needed data to the response
		$event->response += compact('nonce', 'hmac');
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
			$grants = Model::user_grant_list_by_user(self::$_user_id);
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
