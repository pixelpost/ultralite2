<?php

namespace pixelpost\plugins\auth;

use stdClass,
	pixelpost\Filter;

/**
 * Auth management
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Auth
{
	protected $_lifetime  = 500;
	protected $_username  = '';
	protected $_password  = '';
	protected $_key       = '';
	protected $_challenge = '';
	protected $_nonce     = '';

	/**
	 * Change the auth lifetime
	 *
	 * @param int $lifetime The lifetime in second
	 * @return pixelpost\plugins\auth\Auth
	 */
	public function set_lifetime($lifetime)
	{
		Filter::is_int($lifetime);

		$this->_lifetime = $lifetime;

		return $this;
	}

	/**
	 * Set the auth username
	 *
	 * @param string $username The user name
	 * @return pixelpost\plugins\auth\Auth
	 */
	public function set_username($username)
	{
		Filter::is_string($username);

		$this->_username = $username;

		return $this;
	}

	/**
	 * Change the auth password (md5 hash)
	 *
	 * @param string $password The MD5 hash of the user password
	 * @return pixelpost\plugins\auth\Auth
	 */
	public function set_password_hash($password)
	{
		Filter::is_string($password);

		$this->_password = $password;

		return $this;
	}

	/**
	 * Set the auth secret key
	 *
	 * @param string $key The secret shared by both parts
	 * @return pixelpost\plugins\auth\Auth
	 */
	public function set_key($key)
	{
		Filter::is_string($key);

		$this->_key = $key;

		return $this;
	}

	/**
	 * Set the auth challenge
	 *
	 * @param string A challenge key (see: get_challenge())
	 * @return pixelpost\plugins\auth\Auth
	 */
	public function set_challenge($challenge)
	{
		Filter::is_string($challenge);

		$this->_challenge = $challenge;

		return $this;
	}

	/**
	 * Set the auth nonce
	 *
	 * @param string A nonce (see: get_nonce())
	 * @return pixelpost\plugins\auth\Auth
	 */
	public function set_nonce($nonce)
	{
		Filter::is_string($nonce);

		$this->_nonce = $nonce;

		return $this;
	}

	/**
	 * Generate and return a salt - Based on lifetime
	 *
	 * @return int
	 */
	public function get_salt()
	{
		return ceil(time() / $this->_lifetime) * $this->_lifetime;
	}

	/**
	 * Generate and return the secret - Based on username, password, key
	 *
	 * @return string
	 */
	public function get_secret()
	{
		return md5($this->_username . $this->_password . $this->_key);
	}

	/**
	 * Generate and return a challenge - Based on username, password, key
	 *
	 * @return string
	 */
	public function get_challenge()
	{
		return md5($this->get_secret() . microtime() . uniqid());
	}

	/**
	 * Generate and return a nonce - Based on nothing
	 *
	 * @return string
	 */
	public function get_nonce()
	{
		return md5(rand(1, 1000) . microtime() . uniqid());
	}

	/**
	 * Generate and return a token - Based on username, password, key, lifetime, challenge
	 *
	 * @return string
	 */
	public function get_token()
	{
		return md5($this->_challenge . $this->get_secret() . $this->get_salt());
	}

	/**
	 * Generate and return a hmac - Based on username, password, key, nonce
	 *
	 * @param string $data The $data to hmac
	 * @return string
	 */
	public function get_hmac($data)
	{
		return md5($this->_nonce . $data . $this->get_secret());
	}

	/**
	 * Generate and return a signature - Based on username, password, key, lifetime, challenge
	 *
	 * @param string $data The $data to sign
	 * @return string
	 */
	public function get_signature($data)
	{
		return md5($this->get_token() . $data . $this->get_secret());
	}

	/**
	 * Generate a HMAC based on a API request.
	 *
	 * see: get_hmac() method for requirement
	 *
	 * @param array $data The data to hmac
	 * @return string
	 */
	public function hmac($header, array $data)
	{
		return $this->get_hmac($header . $this->_serialize($data));
	}

	/**
	 * Serialize the body part of a request for sign_api_request() method
	 *
	 * @param array $data The data to serialize
	 * @return string
	 */
	protected function _serialize(array $data)
	{
		$serial = '';

		foreach($data as $key => $value)
		{
			if (!is_scalar($value))
			{
				$value = $this->_serialize((array) $value);
			}

			if (is_bool($value))
			{
				$value = $value ? 'true' : 'false';
			}

			$serial .= sprintf('%s:%s,', strval($key), $value);
		}

		return sprintf('{%s}', $serial);
	}
}