<?php

namespace pixelpost\plugins\auth;

use Exception,
	pixelpost\Config,
	pixelpost\Request,
	pixelpost\Template;

/**
 * Web Auth management
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class WebAuth
{
	/**
	 * Generate a new challenge
	 *
	 * @return string
	 */
	protected static function _gen_challenge()
	{
		return str_shuffle((md5(strrev(microtime()) . uniqid() . rand(1, 3000))));
	}

	/**
	 * Return a Auth object.
	 *
	 * @param  string $user
	 * @param  string $pass
	 * @return Auth
	 */
	protected static function _get_auth($user, $pass)
	{
		$conf = Config::create();

		$auth = new Auth();
		$auth->set_username($user)
			 ->set_password_hash($pass)
			 ->set_key(ADMIN_URL . 'auth-login')
			 ->set_lifetime($conf->plugin_auth->lifetime);

		return $auth;
	}

	/**
	 * Generate an authentification key
	 *
	 * @param  int    $id
	 * @param  string $user
	 * @param  string $pass
	 * @return string
	 */
	protected static function _gen_auth_key($id, $user, $pass)
	{
		// get Auth class
		$secret   = self::_get_auth($user, $pass)->get_secret();
		$lifetime = 3600 * 4; // 4 hours validity
		$salt     = ceil(time() / $lifetime) * $lifetime;

		return md5($secret . $id . $user . $pass . $salt);
	}

	/**
	 * Generate a reset key
	 *
	 * @param  int    $id
	 * @param  string $user
	 * @param  string $pass
	 * @return string
	 */
	protected static function _gen_reset_key($id, $user, $pass)
	{
		// get Auth class
		$lifetime = 3600 * 48; // 2 days validity
		$salt     = ceil(time() / $lifetime) * $lifetime;

		return strrev(md5('reset' . $id . $user . $pass . $salt));
	}

	/**
	 * Check if a user id exists in database, set argument $name, $pass and
	 * return true if exists, else return false
	 *
	 * @param  int    $id
	 * @param  string $name
	 * @param  string $pass
	 * @return bool
	 */
	protected static function _check_userid($id, &$name, &$pass)
	{
		try
		{
			extract(Model::user_get_by_id($id));
		}
		catch (ModelExceptionNoResult $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Check if a user name exists in database, set argument $id, $pass and
	 * return true if exists, else return false
	 *
	 * @param  string $user
	 * @param  int    $id
	 * @param  string $pass
	 * @return bool
	 */
	protected static function _check_username($user, &$id, &$pass)
	{
		try
		{
			extract(Model::user_get_by_name($user));
		}
		catch (ModelExceptionNoResult $e)
		{
			return false;
		}

		return true;
	}

	/**
	 * Return the user name stored in cookie if exists else return an empty
	 * string
	 *
	 * @return string
	 */
	protected static function _get_user()
	{
		if (!isset($_COOKIE['PPU'])) return '';
		if (false === $user = base64_decode($_COOKIE['PPU'])) return '';
		return $user;
	}

	/**
	 * Return the user key stored in cookie if exists else return an empty
	 * string
	 *
	 * @return string
	 */
	protected static function _get_key()
	{
		if (!isset($_COOKIE['PPK'])) return '';
		if (false === $key = base64_decode($_COOKIE['PPK'])) return '';
		return $key;
	}

	/**
	 * Check if a user is authentified, if true, feel $id, $user and return true
	 * else return false
	 *
	 * @param  int $id
	 * @param  string $user
	 * @return bool
	 */
	public static function check(&$id, &$user)
	{
		// retrieve cookie data (see admin login page how the auth is generated)
		if ('' === $user = self::_get_user()) return false;
		if ('' === $key  = self::_get_key())  return false;

		// check if user exists (and get it's password)
		if (!self::_check_username($user, $id, $pass)) return false;

		// check if key is valid
		if ($key != self::_gen_auth_key($id, $user, $pass)) return false;

		return true;
	}

	/**
	 * Register a user, return true if the user is well registred else false
	 *
	 * @param  string $user
	 * @param  string $pass
	 * @param  int    $id
	 * @param  string $domain
	 * @return true
	 */
	public static function register($user, $pass, $id, $domain)
	{
		// Register the auth in a cookie.
		// Cookie's path is limited to the admin directory and this domain.
		// We register the user in a permanent cookie and the auth in a session cookie.
		$conf   = Config::create();
		$path   = '/' . $conf->userdir . '/' . $conf->plugin_router->admin . '/';
		$expire = time() + (365 * 24 * 3600);
		$key    = base64_encode(self::_gen_auth_key($id, $user, $pass));
		$user   = base64_encode($user);

		if (headers_sent()) return false;

		if ($domain == 'localhost') $domain = null;

		setcookie('PPU', $user, $expire, $path, $domain, false, true);
		setcookie('PPK', $key,  0,       $path, $domain, false, true);
		return true;
	}

	/**
	 * Publish the authentification form
	 */
	public static function auth()
	{
		Template::create()
			->assign('user', self::_get_user())
			->assign('priv', self::_gen_challenge())
			->publish('auth/tpl/auth.php');
	}

	/**
	 * Receive data from authentification form (see auth() method).
	 * Verify if the couple user / pass is valid or not.
	 *
	 * If user is valid, he is registred (see: register() method).
	 *
	 * This method return JSON data.
	 * See it's usage in authentification form in javascript code.
	 *
	 * @param pixelpost\Request $request
	 */
	public static function login(Request $request)
	{
		try
		{
			// page request is not a http POST
			if (!$request->is_post()) throw new Exception('not posted');

			// retrieve posted data
			$post = $request->get_post();

			// check if we have required posted data
			if (!array_key_exists('user', $post)) throw new Exception('missing user');
			if (!array_key_exists('priv', $post)) throw new Exception('missing priv');
			if (!array_key_exists('key',  $post)) throw new Exception('missing key');

			// retrieve posted user, priv and key
			$user = trim($post['user']);
			$priv = trim($post['priv']);
			$key  = trim($post['key']);

			// check if they are not empty
			if ($user == '') throw new Exception('user is empty');
			if ($priv == '') throw new Exception('priv is empty');
			if ($key  == '') throw new Exception('key is empty');

			// check if user exists (and get it's password)
			if (!self::_check_username($user, $id, $pass))
			{
				throw new Exception('auth invalid');
			}

			// load Auth class from plugin auth
			$auth = self::_get_auth($user, $pass)
				 ->set_challenge($priv)
				 ->set_lifetime(150);

			// check if a generated token correspond to the generated key
			if ($auth->get_token() != $key) throw new Exception('auth invalid');

			if (!self::register($user, $pass, $id, $request->get_host()))
			{
				throw new Exception('auth invalid');
			}

			echo json_encode(array('status' => 'valid', 'message' => 'auth valid'));
		}
		catch(Exception $e)
		{
			echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
		}
	}

	/**
	 * Receive data from authentification form (see auth() method).
	 * Verify if the user exists or not.
	 *
	 * If user exists, send an email to the admin specified a request for
	 * password reset with a link to do it.
	 *
	 * This method return JSON data.
	 * See it's usage in authentification form in javascript code.
	 *
	 * @param pixelpost\Request $request
	 */
	public static function forget(Request $request)
	{
		$message = 'an email is sent to the admin';

		try
		{
			// page request is not a http POST
			if (!$request->is_post()) throw new Exception('not posted');

			// retrieve posted data
			$post = $request->get_post();

			// check if we have required posted data
			if (!array_key_exists('user', $post)) throw new Exception('missing user');

			// retrieve posted user, priv and key
			$user = trim($post['user']);

			// check if they are not empty
			if ($user == '') throw new Exception('username is empty');

			// check if user exists (and get it's password)
			if (self::_check_username($user, $id, $pass))
			{
				// send an email to the admin with a reset link
				$email = Config::create()->email;

				$content = Template::create()
					->assign('user', $user)
					->assign('key', self::_gen_reset_key($id, $user, $pass) . $id)
					->render('auth/tpl/forget.php');

				mail($email, 'Pixelpost reset password request', $content);
			}

			echo json_encode(array('status' => 'valid', 'message' => 'an email is sent to the admin'));
		}
		catch(Exception $e)
		{
			echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
		}
	}

	/**
	 * Publish a reset password form if the link is valid unless redirect to
	 * the admin 404 page.
	 *
	 * If the form is filled, return json data to specify if the password is
	 * well reset or not. When the password is reset, the user is registred
	 * (see register() method) and redirected to the admin home page.
	 *
	 * @param pixelpost\Request $request
	 * @return type
	 */
	public static function reset(Request $request)
	{
		try
		{
			// get url parameters
			$params = $request->get_params();

			array_shift($params);  // skip admin fragment
			array_shift($params);  // skip reset fragment

			// get key url parameters
			$key = array_shift($params);

			// if we have not key
			if ($key === false || strlen($key) < 33) throw new Exception();

			// get the user id from the key
			$id  = intval(substr($key, 32));
			$key = substr($key, 0, 32);

			// check if userID exists
			if (!self::_check_userid($id, $user, $pass)) throw new Exception();

			// check if key is valid
			if ($key != self::_gen_reset_key($id, $user, $pass)) throw new Exception();

			// change the password page
			try
			{
				if (!$request->is_post())
				{
					Template::create()->publish('auth/tpl/reset.php');
					return;
				}
				else
				{
					// retrieve posted data
					$post = $request->get_post();

					// check if we have required posted data
					if (!array_key_exists('pass', $post)) throw new Exception('missing password');

					// retrieve posted password
					$pass = trim($post['pass']);

					// check if it is not empty
					if ($pass == '') throw new Exception('password is empty');

					// change the password in database
					Model::user_update($id, $user, $pass);

					self::register($user, $pass, $id, $request->get_host());
				}

				echo json_encode(array('status' => 'valid', 'message' => 'password is reset'));
			}
			catch(Exception $e)
			{
				echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
			}
		}
		catch(Exception $e)
		{
			header('Location: ' . ADMIN_URL . '404', 301);
			exit();
		}
	}

	/**
	 * Disconnect a user and redirect him to the admin home page.
	 *
	 * @param pixelpost\Request $request
	 */
	public static function disconnect(Request $request)
	{
		$conf   = Config::create();
		$path   = '/' . $conf->userdir . '/' . $conf->plugin_router->admin . '/';
		$expire = time() - (365 * 24 * 3600);
		$domain = $request->get_host();

		if ($domain == 'localhost') $domain = null;

		setcookie('PPK', null, 0, $path, $domain, true);
		header('Location: ' . ADMIN_URL, 302);
	}
}