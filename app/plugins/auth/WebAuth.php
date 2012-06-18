<?php

namespace pixelpost\plugins\auth;

use Exception,
	pixelpost\core\Config,
	pixelpost\core\Request,
	pixelpost\core\Template;

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
		$auth->set_public_key($user)
			 ->set_private_key(md5($pass . ADMIN_URL . 'auth-login'))
			 ->set_lifetime($conf->plugin_auth->lifetime);

		return $auth;
	}

	/**
	 * Generate an authentication key
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
	 * @param  string $email
	 * @return bool
	 */
	protected static function _check_userid($id, &$name, &$pass, &$email = null)
	{
		try
		{
			// create $name, $pass, $email
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
	 * @param  string $email
	 * @return bool
	 */
	protected static function _check_username($user, &$id, &$pass, &$email = null)
	{
		try
		{
			// create $name, $pass, $email
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
	 * Check if a user is authenticated, if true, feel $id, $user and return true
	 * else return false
	 *
	 * @param  int $id
	 * @param  string $user
	 * @return bool
	 */
	public static function check(&$id, &$user)
	{
		assert('pixelpost\core\Log::debug("(auth) Webauth::check() check cookie...")');

		// retrieve cookie data (see admin login page how the auth is generated)
		if ('' === $user = self::_get_user()) return false;

		assert('pixelpost\core\Log::debug("(auth) Webauth::check() — username: %s", $user)');

		if ('' === $key  = self::_get_key())  return false;

		assert('pixelpost\core\Log::debug("(auth) Webauth::check() — auth key: %s", $key)');

		assert('pixelpost\core\Log::debug("(auth) Webauth::check() validate data...")');

		// check if user exists (and get it's password)
		if (!self::_check_username($user, $id, $pass)) return false;

		assert('pixelpost\core\Log::debug("(auth) Webauth::check() - valid: username.")');

		// check if key is valid
		if ($key != self::_gen_auth_key($id, $user, $pass)) return false;

		assert('pixelpost\core\Log::debug("(auth) Webauth::check() — valid: auth key.")');

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
		$path   = '/' . $conf->userdir . '/' . $conf->pixelpost->admin . '/';
		$expire = time() + (365 * 24 * 3600);
		$key    = base64_encode(self::_gen_auth_key($id, $user, $pass));
		$user   = base64_encode($user);

		if (headers_sent()) return false;

		if ($domain == 'localhost') $domain = null;

		assert('pixelpost\core\Log::debug("(auth) Webauth::register() create cookie...")');
		assert('pixelpost\core\Log::debug("(auth) Webauth::register() — path: %s",   $path)');
		assert('pixelpost\core\Log::debug("(auth) Webauth::register() — expire: %s", $expire)');
		assert('pixelpost\core\Log::debug("(auth) Webauth::register() — domain: %s", $domain)');
		assert('pixelpost\core\Log::debug("(auth) Webauth::register() — user: %s",   base64_decode($user))');
		assert('pixelpost\core\Log::debug("(auth) Webauth::register() — key: %s",    base64_decode($key))');

		setcookie('PPU', $user, $expire, $path, $domain, false, true);
		setcookie('PPK', $key,  0,       $path, $domain, false, true);

		return true;
	}

	/**
	 * Publish the authentication form
	 */
	public static function auth()
	{
		Template::create()
			->assign('user', self::_get_user())
			->assign('priv', self::_gen_challenge())
			->publish('auth/tpl/auth.php');
	}

	/**
	 * Receive data from authentication form (see auth() method).
	 * Verify if the couple user / pass is valid or not.
	 *
	 * If user is valid, he is registred (see: register() method).
	 *
	 * This method return JSON data.
	 * See it's usage in authentication form in javascript code.
	 *
	 * @param pixelpost\core\Request $request
	 */
	public static function login(Request $request)
	{
		try
		{
			assert('pixelpost\core\Log::debug("(auth) Webauth::login() check post data...")');

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

			assert('pixelpost\core\Log::debug("(auth) Webauth::login() - user: %s", $user)');
			assert('pixelpost\core\Log::debug("(auth) Webauth::login() - priv: %s", $priv)');
			assert('pixelpost\core\Log::debug("(auth) Webauth::login() - key: %s",  $key)');

			// check if they are not empty
			if ($user == '') throw new Exception('user is empty');
			if ($priv == '') throw new Exception('priv is empty');
			if ($key  == '') throw new Exception('key is empty');

			assert('pixelpost\core\Log::debug("(auth) Webauth::login() validate user...")');

			// check if user exists (and get it's password)
			if (!self::_check_username($user, $id, $pass))
			{
				throw new Exception('auth invalid');
			}

			assert('pixelpost\core\Log::debug("(auth) Webauth::login() validate auth...")');

			// load Auth class from plugin auth
			$auth = self::_get_auth($user, $pass)
				 ->set_challenge($priv)
				 ->set_lifetime(150);

			// check if a generated token correspond to the generated key
			if ($auth->get_token() != $key) throw new Exception('auth invalid');

			assert('pixelpost\core\Log::debug("(auth) Webauth::login() register auth...")');

			if (!self::register($user, $pass, $id, $request->get_host()))
			{
				throw new Exception('auth invalid');
			}

			assert('pixelpost\core\Log::debug("(auth) Webauth::login() auth ok.")');

			echo json_encode(array('status' => 'valid', 'message' => 'auth valid'));
		}
		catch(Exception $e)
		{
			assert('pixelpost\core\Log::debug("(auth) Webauth::login() unauth: %s.", $e->getMessage())');

			echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
		}
	}

	/**
	 * Receive data from authentication form (see auth() method).
	 * Verify if the user exists or not.
	 *
	 * If user exists, send an email to the admin specified a request for
	 * password reset with a link to do it.
	 *
	 * This method return JSON data.
	 * See it's usage in authentication form in javascript code.
	 *
	 * @param pixelpost\core\Request $request
	 */
	public static function forget(Request $request)
	{
		$message = 'a reset link has been sent via email';

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

			// check if user exists (and get it's password, email)
			if (self::_check_username($user, $id, $pass, $email))
			{

				$content = Template::create()
					->assign('user', $user)
					->assign('key', self::_gen_reset_key($id, $user, $pass) . $id)
					->render('auth/tpl/forget.php');

				mb_send_mail($email, 'Pixelpost reset password request', $content);

				// send an email to the admin with a reset link
				$email = Config::create()->email;

				mb_send_mail($email, 'Pixelpost reset password request', $content);
			}

			echo json_encode(array('status' => 'valid', 'message' => $message));
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
	 * @param pixelpost\core\Request $request
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
			if ($key === false || mb_strlen($key) < 33) throw new Exception('bad key');

			// get the user id from the key
			$id  = intval(mb_substr($key, 32));
			$key = mb_substr($key, 0, 32);

			// check if userID exists
			if (!self::_check_userid($id, $user, $pass, $email)) throw new Exception('unknown user');

			// check if key is valid
			if ($key != self::_gen_reset_key($id, $user, $pass)) throw new Exception('security key invalid');

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
					Model::user_update($id, $user, $pass, $email);

					self::register($user, $pass, $id, $request->get_host());
				}

				echo json_encode(array('status' => 'valid', 'message' => 'password reset'));
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
	 * @param pixelpost\core\Request $request
	 */
	public static function disconnect(Request $request)
	{
		$conf   = Config::create();
		$path   = '/' . $conf->userdir . '/' . $conf->pixelpost->admin . '/';
		$expire = time() - (365 * 24 * 3600);
		$domain = $request->get_host();

		if ($domain == 'localhost') $domain = null;

		setcookie('PPK', null, 0, $path, $domain, true);
		header('Location: ' . ADMIN_URL, 302);
	}
}