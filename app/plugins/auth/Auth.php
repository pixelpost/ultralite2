<?php

namespace pixelpost\plugins\auth;

use pixelpost;

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
	protected $_domain    = '';
	protected $_challenge = '';

	public function set_lifetime($lifetime)
	{
		pixelpost\Filter::is_int($lifetime);

		$this->_lifetime = $lifetime;

		return $this;
	}

	public function set_username($username)
	{
		pixelpost\Filter::is_string($username);

		$this->_username = $username;

		return $this;
	}

	public function set_password_hash($password)
	{
		pixelpost\Filter::is_string($password);

		$this->_password = $password;

		return $this;
	}

	public function set_domain($domain)
	{
		pixelpost\Filter::is_string($domain);

		$this->_domain = $domain;

		return $this;
	}

	public function set_challenge($challenge)
	{
		pixelpost\Filter::is_string($challenge);

		$this->_challenge = $challenge;

		return $this;
	}

	public function get_nonce()
	{
		return ceil(time() / $this->_lifetime) * $this->_lifetime;
	}

	public function get_secret()
	{
		return md5($this->_username . $this->_password . $this->_domain);
	}

	public function get_challenge()
	{
		return md5($this->get_secret() . microtime() . uniqid());
	}

	public function get_token()
	{
		return md5($this->_challenge . $this->get_secret() . $this->get_nonce());
	}

	public function get_signature()
	{
		return md5($this->get_token() . $this->get_secret() . $this->get_nonce());
	}
}