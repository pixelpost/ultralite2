<?php

namespace pixelpost;

/**
 * Request support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
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
	 * @return Request
	 */
	public static function create()
	{
		return new static;
	}

	/**
	 * Load the request from the Apache server data.
	 *
	 * @return Request
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
			if (strpos($serverPort, ':') === false)
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

		// load post datas id they exists
		$this->set_post($_POST);

		// check if data exists on the input
		$this->_data = file_get_contents('php://input');

		return $this;
	}

	/**
	 * Load the request with the $url
	 *
	 * @param string $url
	 * @return Request
	 */
	public function set_url($url)
	{
		Filter::assume_string($url);

		$parse = parse_url($url);

		$query = false;

		if (isset($parse['scheme']))
			$this->_scheme = $parse['scheme'];
		if (isset($parse['host']))
			$this->_host = $parse['host'];
		if (isset($parse['port']))
			$this->_port = $parse['port'];
		if (isset($parse['user']))
			$this->_user = $parse['user'];
		if (isset($parse['pass']))
			$this->_pass = $parse['pass'];
		if (isset($parse['fragment']))
			$this->_frag = $parse['fragment'];
		if (isset($parse['path']))
			$this->_path = ltrim($parse['path'], '/');
		if (isset($parse['query']))
			parse_str($parse['query'], $query);

		if ($query !== false)
			$this->set_query($query);

		// delete userdir from path
		if ($this->is_userdir() &&
				strlen($this->_path) >= strlen($this->_userdir) &&
				substr($this->_path, 0, strlen($this->_userdir)) == $this->_userdir)
		{
			$this->_path = ltrim(substr($this->_path, strlen($this->_userdir)), '/');
		}

		$this->_isTrailingSlash = (substr($this->_path, -1) == '/');

		$this->_path = rtrim($this->_path, '/');

		$this->_params = explode('/', $this->_path);

		return $this;
	}

	/**
	 * Add a user directory (like: '~username/myapp' for
	 * 'http://foo.com/~username/myapp/') the userdir is skipped in the url path
	 * and not enter int the url params. (see: get_params())
	 *
	 * @param string $userdir
	 * @return Request
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
	 * @param int $protocol
	 * @return Request
	 */
	public function set_protocol($protocol)
	{
		Filter::assume_int($protocol);

		switch ($protocol)
		{
			case PROTO_HTTP_09 : $this->_protocol = 'HTTP/0.9';
				break;
			case PROTO_HTTP_10 : $this->_protocol = 'HTTP/1.0';
				break;
			case PROTO_HTTP_11 : $this->_protocol = 'HTTP/1.1';
				break;
			default : $this->_protocol = 'HTTP/1.1';
				break;
		}

		return $this;
	}

	/**
	 * Change the GET params
	 *
	 * @param array $params
	 * @return Request
	 */
	public function set_query(array $params)
	{
		if (intval(ini_get('magic_quotes_gpc')))
		{
			$this->_query = array();
			foreach ($params as $i => $v)
				$this->_query[$i] = stripslashes($v);
		}
		else
		{
			$this->_query = $params;
		}

		return $this;
	}

	/**
	 * Change the POST params
	 *
	 * @param array $params
	 * @return Request
	 */
	public function set_post(array $params)
	{
		if (intval(ini_get('magic_quotes_gpc')))
		{
			$this->_post = array();
			foreach ($params as $i => $v)
			{
				// we not going far away than 2 path deep.
				if (is_array($v))
				{
					foreach ($v as $ii => $val)
					{
						if (!is_array($val))
						{
							$v[$ii] = \stripslashes($val);
						}
					}
					$this->_post[$i] = $v;
				}
				else
				{
					$this->_post[$i] = \stripslashes($v);
				}
			}
		}
		else
		{
			$this->_post = $params;
		}

		return $this;
	}

	/**
	 * Change the data received on input with the request
	 *
	 * @param string $data
	 * @return Request
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
	 * @param int $method (see: class constant)
	 * @return Request
	 */
	public function set_method($method)
	{
		Filter::assume_int($method);

		switch ($method)
		{
			case self::METHOD_GET : $this->_method = 'GET';
				break;
			case self::METHOD_POST : $this->_method = 'POST';
				break;
			case self::METHOD_PUT : $this->_method = 'PUT';
				break;
			case self::METHOD_HEAD : $this->_method = 'HEAD';
				break;
			case self::METHOD_DELETE : $this->_method = 'DELETE';
				break;
			case self::METHOD_TRACE : $this->_method = 'TRACE';
				break;
			case self::METHOD_CONNECT : $this->_method = 'CONNECT';
				break;
			case self::METHOD_OPTIONS : $this->_method = 'OPTIONS';
				break;
			default : $this->_method = 'GET';
				break;
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

		if (!$this->is_std_port())
			$url .= ':' . $this->_port;
		if ($this->is_userdir())
			$url .= '/' . $this->_userdir;

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

		if ($this->is_path())
			$url .= $this->get_path();
		if ($this->_isTrailingSlash)
			$url .= '/';
		if ($this->is_query())
			$url .= '?' . http_build_query($this->_query);
		if ($this->is_fragment())
			$url .= '#' . $this->get_fragment();

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
	 * Return the GET datas
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
		if ($this->is_https())
		{
			return ($this->_port == 443);
		}
		else
		{
			return ($this->_port == 80);
		}
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
