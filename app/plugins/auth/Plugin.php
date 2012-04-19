<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Config,
	pixelpost\core\Filter,
	pixelpost\core\Event,
	pixelpost\core\PluginInterface,
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
	 * @var int The authenticated user_id if auth success
	 */
	protected static $_user_id  = 0;

	/**
	 * @var int The authenticated entity_id if auth success
	 */
	protected static $_entity_id = 0;

	/**
	 * @var string The authenticated entity public key if auth success
	 */
	protected static $_entity_pub = '';

	/**
	 * @var string The authenticated entity private key if auth success
	 */
	protected static $_entity_priv = '';

	/**
	 * @var int The authenticated entity name if auth success
	 */
	protected static $_entity_name = '';

	public static function version()
	{
		return '0.0.1';
	}

	public static function depends()
	{
		return array(
			'api'       => '0.0.1',
			'admin'     => '0.0.1',
			'pixelpost' => '0.0.1',
		);
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
		$self  = __CLASS__;
		$api   = __NAMESPACE__ . '\Api';
		$admin = __NAMESPACE__ . '\Admin';
		$pp    = 'pixelpost\plugins\pixelpost\Plugin';

		Event::register_list(array(
			// api events
			array('api.request.raw',           $self   . '::api_request'),
			array('api.response.raw',          $self   . '::api_response'),
			array('api.auth.version',          $api    . '::auth_version'),
			array('api.auth.request',          $api    . '::auth_request'),
			array('api.auth.token',            $api    . '::auth_token'),
			array('api.auth.refresh',          $api    . '::auth_refresh'),
			array('api.auth.destroy',          $api    . '::auth_destroy'),
			array('api.auth.config.get',       $api    . '::auth_config_get'),
			array('api.auth.config.set',       $api    . '::auth_config_set'),
			array('api.auth.user.add',         $api    . '::auth_user_add'),
			array('api.auth.user.set',         $api    . '::auth_user_set'),
			array('api.auth.user.get',         $api    . '::auth_user_get'),
			array('api.auth.user.del',         $api    . '::auth_user_del'),
			array('api.auth.user.list',        $api    . '::auth_user_list'),
			array('api.auth.entity.add',       $api    . '::auth_entity_add'),
			array('api.auth.entity.set',       $api    . '::auth_entity_set'),
			array('api.auth.entity.get',       $api    . '::auth_entity_get'),
			array('api.auth.entity.del',       $api    . '::auth_entity_del'),
			array('api.auth.entity.list',      $api    . '::auth_entity_list'),
			array('api.auth.grant.add',        $api    . '::auth_grant_add'),
			array('api.auth.grant.set',        $api    . '::auth_grant_set'),
			array('api.auth.grant.get',        $api    . '::auth_grant_get'),
			array('api.auth.grant.del',        $api    . '::auth_grant_del'),
			array('api.auth.grant.list',       $api    . '::auth_grant_list'),
			array('api.auth.user.grant.add',   $api    . '::auth_user_grant_add'),
			array('api.auth.user.grant.del',   $api    . '::auth_user_grant_del'),
			array('api.auth.entity.grant.add', $api    . '::auth_entity_grant_add'),
			array('api.auth.entity.grant.del', $api    . '::auth_entity_grant_del'),
			// admin web interface
			array('request.admin',             $self   . '::request_admin', 99),
			array('admin.template.nav',        $admin  . '::template_nav', 150),
			array('admin.template.navbar',     $admin  . '::template_navbar'),
			array('admin.template.js',         $admin  . '::template_js'),
			array('admin.auth',                $pp     . '::route'),
			array('admin.auth.api-bridge',     $admin  . '::page_api_bridge'),
			array('admin.auth.account',        $admin  . '::page_account'),
		));
	}

	/**
	 * Verify if a user is authenticated for admin pages. if not print the login
	 * pages and break the request.admin chain (cause the original admin page
	 * called is not generated).
	 *
	 * @param pixelpost\core\Event $event
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
				// authenticated too).
				self::$_user_id     = $id;
				self::$_entity_name = $name;
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
	 * Return the authenticated entity name or an empty string
	 *
	 * @return string
	 */
	public static function get_entity_name()
	{
		return self::$_entity_name;
	}

	/**
	 * Return the authenticated entity public key or an empty string
	 *
	 * @return string
	 */
	public static function get_entity_public_key()
	{
		return self::$_entity_pub;
	}

	/**
	 * Return the authenticated token id or 0
	 *
	 * @return int
	 */
	public static function get_token_id()
	{
		return self::$_api_token;
	}

	/**
	 * Return the authenticated entity id or 0
	 *
	 * @return int
	 */
	public static function get_entity_id()
	{
		return self::$_entity_id;
	}

	/**
	 * Return the authenticated user_id or 0
	 *
	 * @return string
	 */
	public static function get_user_id()
	{
		return self::$_user_id;
	}

	/**
	 * Return if there is an authentication
	 *
	 * @return bool
	 */
	public static function is_auth()
	{
		// check if user is already authenticated
		return (self::$_user_id != 0);
	}

	/**
	 * Return if a user is authenticated
	 *
	 * @return bool
	 */
	public static function is_auth_admin()
	{
		// check if it is a entity which is logged
		return (self::$_entity_id == 0);
	}

	/**
	 * Check authentication if provided in api request.
	 *
	 * @param pixelpost\core\Event $event
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

		// retrieve entity data
		$entity = Model::entity_get_by_id($token['entity_id']);

		// retrieve configuration
		$conf = Config::create();

		// prepare auth class
		$auth = new Auth();
		$auth->set_lifetime($conf->plugin_auth->lifetime)
			 ->set_public_key($entity['public_key'])
			 ->set_private_key($entity['private_key'])
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

		// store authenticated entityname and id, generate a new nonce
		self::$_api_token   = $token['id'];
		self::$_api_nonce   = $token['nonce'];
		self::$_entity_id   = $token['entity_id'];
		self::$_entity_pub  = $entity['public_key'];
		self::$_entity_priv = $entity['private_key'];
		self::$_user_id     = $entity['user_id'];
	}

	/**
	 * Create hmac and nonce in api response if necessary
	 *
	 * @param pixelpost\core\Event $event
	 */
	public static function api_response(Event $event)
	{
		if (self::$_api_token == 0) return;

		// prepare auth class
		$auth = new Auth();
		$auth->set_private_key(self::$_entity_priv)
			 ->set_public_key(self::$_entity_pub)
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
	 * Return if a user is granted to $grantRequested or to 'self' grant if a
	 * user id is provided.
	 * Possible grants are : read | write | config | delete
	 *
	 * @return bool
	 */
	public static function is_granted($grantRequested, $user_id = 0)
	{
		// check the authentification
		if (!self::is_auth()) return false;

		// check virtual 'self' grant if a user id is provided
		if ($user_id && $user_id == self::$_user_id) return true;

		// the entity grants (for better perf on multiple call)
		static $grants = null;

		if (is_null($grants))
		{
			// which entity is logged ?
			$id = self::$_entity_id ?: Model::user_get_entity_id(self::$_user_id);

			// retrieve all user's grant
			try
			{
				$grants = Model::entity_grant_list_by_entity($id);
			}
			catch(ModelExceptionNoResult $e)
			{
				return false;
			}
		}

		// check if the user is granted to $grantRequested
		foreach($grants as $grant)
		{
			if ($grant['name'] == $grantRequested) return true;
		}

		return false;
	}
}