<?php

namespace pixelpost\core;

/**
 * Request support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Request
{
	const METHOD_HEAD    = 1;
	const METHOD_GET     = 2;
	const METHOD_POST    = 4;
	const METHOD_PUT     = 8;
	const METHOD_DELETE  = 16;
	const METHOD_TRACE   = 32;
	const METHOD_CONNECT = 64;
	const METHOD_OPTIONS = 128;

	const PROTO_HTTP_09  = 0;
	const PROTO_HTTP_10  = 1;
	const PROTO_HTTP_11  = 2;

	/**
	 * Containing all information about the request, each var have it's own
	 * getter/setter.
	 */
	protected $_protocol        = 'HTTP/1.1';
	protected $_method          = 'GET';
	protected $_scheme          = 'http';
	protected $_port            = 80;
	protected $_host            = '';
	protected $_userdir         = '';
	protected $_path            = '';
	protected $_frag            = '';
	protected $_data            = '';
	protected $_query           = array();
	protected $_post            = array();
	protected $_params          = array();
	protected $_isTrailingSlash = false;

	/**
	 * Create a new instance of the Request class.
	 *
	 * @return pixelpost\core\Request
	 */
	public static function create()
	{
		return new static;
	}

	/**
	 * Load the request from the Apache server data.
	 *
	 * @return pixelpost\core\Request
	 */
	public function auto()
	{
		// get the Apache data and recreate the url called
		// actually, we just can't get the fragment url, apache don't send it !
		$this->_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

		if (isset($_SERVER['HTTPS']) === false) $_SERVER['HTTPS'] = 'off';

		$scheme = ($_SERVER['HTTPS'] != '' && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';

		$serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
		// SERVER_PORT send incorrect value, so use HTTP_HOST
		$serverPort = isset($_SERVER['HTTP_HOST'])   ? $_SERVER['HTTP_HOST']   : 80;
		$serverPath = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

		if ($serverPort != 80)
		{
			if (mb_strpos($serverPort, ':') === false)
			{
				$serverPort = $_SERVER['SERVER_PORT'];
			}
			else
			{
				$serverPortArray = explode(':', $serverPort);
				$serverPort = array_pop($serverPortArray);

				if (!is_numeric($serverPort))
				{
					$serverPort = $_SERVER['SERVER_PORT'];
				}
			}
		}

		// send the url to be parsed. That's a little redondant but we sure
		// about the method and format of all element.
		$this->set_url($scheme . '://' . $serverName . ':' . $serverPort . $serverPath);

		// load post data id they exists
		$this->set_post($_POST);

		// check if data exists on the input
		$this->_data = file_get_contents('php://input');

		return $this;
	}

	/**
	 * Load the request with the $url
	 *
	 * @param  string $url
	 * @return pixelpost\core\Request
	 */
	public function set_url($url)
	{
		Filter::assume_string($url);

		$parse = parse_url($url);

		$query = false;

		isset($parse['scheme'])   and $this->_scheme = $parse['scheme'];
		isset($parse['host'])     and $this->_host   = $parse['host'];
		isset($parse['port'])     and $this->_port   = $parse['port'];
		isset($parse['user'])     and $this->_user   = $parse['user'];
		isset($parse['pass'])     and $this->_pass   = $parse['pass'];
		isset($parse['fragment']) and $this->_frag   = $parse['fragment'];
		isset($parse['path'])     and $this->_path   = ltrim($parse['path'], '/');
		isset($parse['query'])    and mb_parse_str($parse['query'], $query);

		if ($query !== false) $this->set_query($query);

		// delete userdir from path
		if ($this->is_userdir() &&
			mb_strlen($this->_path) >= mb_strlen($this->_userdir) &&
			mb_substr($this->_path, 0, mb_strlen($this->_userdir)) == $this->_userdir)
		{
			$this->_path = ltrim(mb_substr($this->_path, mb_strlen($this->_userdir)), '/');
		}

		$this->_isTrailingSlash = (mb_substr($this->_path, -1) == '/');

		$this->_path   = rtrim($this->_path, '/');

		$this->_params = explode('/', $this->_path);

		return $this;
	}

	/**
	 * Add a user directory (like: '~username/myapp' for
	 * 'http://foo.com/~username/myapp/') the userdir is skipped in the url path
	 * and not enter int the url params. (see: get_params())
	 *
	 * @param  string $userdir
	 * @return pixelpost\core\Request
	 */
	public function set_userdir($userdir)
	{
		Filter::assume_string($userdir);

		$this->_userdir = trim($userdir, '/');

		return $this;
	}

	/**
	 * Change the protocol. (see class constant)
	 *
	 * @param  int $protocol
	 * @return pixelpost\core\Request
	 */
	public function set_protocol($protocol)
	{
		switch ($protocol)
		{
			case PROTO_HTTP_09 : $this->_protocol = 'HTTP/0.9'; break;
			case PROTO_HTTP_10 : $this->_protocol = 'HTTP/1.0'; break;
			case PROTO_HTTP_11 : $this->_protocol = 'HTTP/1.1'; break;
			default            : $this->_protocol = 'HTTP/1.1'; break;
		}

		return $this;
	}

	/**
	 * Change the GET params
	 *
	 * @param  array $params
	 * @return pixelpost\core\Request
	 */
	public function set_query(array $params)
	{
		$this->_query = $params;

		return $this;
	}

	/**
	 * Change the POST params
	 *
	 * @param  array $params
	 * @return pixelpost\core\Request
	 */
	public function set_post(array $params)
	{
		$this->_post = $params;

		return $this;
	}

	/**
	 * Change the data received on input with the request
	 *
	 * @param  string $data
	 * @return pixelpost\core\Request
	 */
	public function set_data($data)
	{
		Filter::assume_string($data);

		$this->_data = $data;

		return $this;
	}

	/**
	 * Change the url method call
	 *
	 * @param  int $method (see: class constant)
	 * @return pixelpost\core\Request
	 */
	public function set_method($method)
	{
		switch ($method)
		{
			case static::METHOD_GET     : $this->_method = 'GET';     break;
			case static::METHOD_POST    : $this->_method = 'POST';    break;
			case static::METHOD_PUT     : $this->_method = 'PUT';     break;
			case static::METHOD_HEAD    : $this->_method = 'HEAD';    break;
			case static::METHOD_DELETE  : $this->_method = 'DELETE';  break;
			case static::METHOD_TRACE   : $this->_method = 'TRACE';   break;
			case static::METHOD_CONNECT : $this->_method = 'CONNECT'; break;
			case static::METHOD_OPTIONS : $this->_method = 'OPTIONS'; break;
			default                     : $this->_method = 'GET';     break;
		}

		return $this;
	}

	/**
	 * Return the protocol use for the request: 'HTTP/0.9', 'HTTP/1.0' or 'HTTP/1.1'
	 *
	 * @return string
	 */
	public function get_protocol()
	{
		return $this->_protocol;
	}

	/**
	 * Return the user directory
	 *
	 * @return string
	 */
	public function get_userdir()
	{
		return $this->_userdir;
	}

	/**
	 * Return the base url: 'scheme + host + port + userdir + /'
	 *
	 * @return string
	 */
	public function get_base_url()
	{
		$url = $this->_scheme . '://' . $this->_host;

		$this->is_std_port() or $url .= ':' . $this->_port;
		$this->is_userdir() and $url .= '/' . $this->_userdir;

		return $url . '/';
	}

	/**
	 * Return the requested url
	 *
	 * @return string
	 */
	public function get_request_url()
	{
		$url = $this->get_base_url();

		$this->is_path()        and $url .= $this->get_path();
		$this->_isTrailingSlash and $url .= '/';
		$this->is_query()       and $url .= '?' . http_build_query($this->_query);
		$this->is_fragment()    and $url .= '#' . $this->get_fragment();

		return $url;
	}

	/**
	 * Return le request method: GET, POST, PUT, DELETE
	 *
	 * @return string
	 */
	public function get_method()
	{
		return $this->_method;
	}

	/**
	 * Return the url scheme withour the ://
	 *
	 * @return string
	 */
	public function get_scheme()
	{
		return $this->_scheme;
	}

	/**
	 * Return the url domain
	 *
	 * @return string
	 */
	public function get_host()
	{
		return $this->_host;
	}

	/**
	 * Return the url port
	 *
	 * @return int
	 */
	public function get_port()
	{
		return $this->_port;
	}

	/**
	 * Return the url path with slashes trimed (without start '/' and final '/')
	 *
	 * @return string
	 */
	public function get_path()
	{
		return $this->_path;
	}

	/**
	 * Return the url fragment without the #
	 *
	 * @return string
	 */
	public function get_fragment()
	{
		return $this->_frag;
	}

	/**
	 * Return the GET data
	 *
	 * @return array
	 */
	public function get_query()
	{
		return $this->_query;
	}

	/**
	 * Return the POST data
	 *
	 * @return array
	 */
	public function get_post()
	{
		return $this->_post;
	}

	/**
	 * Return the INPUT data
	 *
	 * @return string
	 */
	public function get_data()
	{
		return $this->_data;
	}

	/**
	 * Return the url parameters, its an array of each elements/subfolders of
	 * the URL excepted the userdir.
	 *
	 * Ex: 'http://foo.com/~bar/hello/world/page/' with '~bar' set as userdir
	 *
	 * Return: array('hello', 'world', 'page');
	 *
	 * @return array
	 */
	public function get_params()
	{
		return $this->_params;
	}

	/**
	 * Return TRUE if it's a HTTPS request else FALSE
	 *
	 * @return bool
	 */
	public function is_https()
	{
		return ($this->_scheme == 'https');
	}

	/**
	 * Retrun TRUE if there is a fragment in the url else FALSE
	 *
	 * @return bool
	 */
	public function is_fragment()
	{
		return ($this->_frag != '');
	}

	/**
	 * Return TRUE if we use a common port for connection (80 or 443) else FALSE
	 *
	 * This is useful to see if port information is needed in the url or not.
	 *
	 * @return bool
	 */
	public function is_std_port()
	{
		return ($this->is_https() ? ($this->_port == 443) : ($this->_port == 80));
	}

	/**
	 * Return TRUE if there is a userdir else FALSE
	 *
	 * @return bool
	 */
	public function is_userdir()
	{
		return ($this->_userdir != '');
	}

	/**
	 * Return TRUE if the url have a path else FALSE
	 *
	 * @return bool
	 */
	public function is_path()
	{
		return ($this->_path != '');
	}

	/**
	 * Return TRUE if the url contains QUERY data elseFALSE
	 *
	 * @return bool
	 */
	public function is_query()
	{
		return (count($this->_query) > 0);
	}

	/**
	 * Return TRUE if the method request is POST else FALSE
	 *
	 * @return bool
	 */
	public function is_post()
	{
		return (count($this->_post) > 0);
	}

	/**
	 * Return TRUE if INPUT data are sent else FALSE
	 *
	 * @return bool
	 */
	public function is_data()
	{
		return ($this->_data != '');
	}
}
