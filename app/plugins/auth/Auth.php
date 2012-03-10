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
	protected $_lifetime    = 500;
	protected $_public_key  = '';
	protected $_private_key = '';
	protected $_challenge   = '';
	protected $_nonce       = '';

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
	 * Set the auth public key
	 *
	 * @param string $public_key
	 * @return pixelpost\plugins\auth\Auth
	 */
	public function set_public_key($public_key)
	{
		Filter::is_string($public_key);

		$this->_public_key = $public_key;

		return $this;
	}

	/**
	 * Set the auth private key
	 *
	 * @param string $private_key
	 * @return pixelpost\plugins\auth\Auth
	 */
	public function set_private_key($private_key)
	{
		Filter::is_string($private_key);

		$this->_private_key = $private_key;

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
	 * Generate and return the secret - Based on public_key, private_key
	 *
	 * @return string
	 */
	public function get_secret()
	{
		return md5($this->_public_key . $this->_private_key);
	}

	/**
	 * Generate and return a challenge - Based on public_key, private_key
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
	 * Generate and return a token - Based on public_key, private_key, lifetime, challenge
	 *
	 * @return string
	 */
	public function get_token()
	{
		return md5($this->_challenge . $this->get_secret() . $this->get_salt());
	}

	/**
	 * Generate and return a hmac - Based on public_key, private_key, nonce
	 *
	 * @param string $data The $data to hmac
	 * @return string
	 */
	public function get_hmac($data)
	{
		return md5($this->_nonce . $data . $this->get_secret());
	}

	/**
	 * Generate and return a signature - Based on public_key, private_key, lifetime, challenge
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