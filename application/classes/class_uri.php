<?php
/**
 * Uri
 *
 * @package Pixelpost
 * @author Jay Williams
 */

class Uri
{
	public static $parameters = array();


	public static $uri = '';


	public static $clean_url = false;


	private static $instance;


	public static function & getInstance()
	{
		static $instance = null;
		
		return $instance = (empty($instance)) ? new self() : $instance;
	}

	private function __construct()
	{
		
		// Detect if Clean URLs are enabled
		self::$clean_url = (isset($_GET['clean_url']) && $_GET['clean_url'] == 'true')? true : false;
		unset($_GET['clean_url']);
		
		self::$uri = (isset($_GET['uri']))? self::clean($_GET['uri']) : null;
		unset($_GET['uri']);
		
		if (self::$uri)
		{
			$fragments =  explode('/', self::$uri);
			$total = count($fragments);

			for ($i=0; $i < $total; $i = $i+2)
			{
				self::$parameters[$fragments[$i]] = (@is_numeric($fragments[$i+1]))? (int) @$fragments[$i+1]: (string) @$fragments[$i+1];
			}
		}
	}


	/**
	 * Retrieve URI Parameters
	 * 
	 *     Uri::get('post'); # Returns the 'post' parameter
	 *
	 * @param string $key 
	 * @return bool|string FALSE if key is not found, the string result if the key is found.
	 */
	public static function get($key=null)
	{
		self::getInstance();
		
		if (empty($key))
		{
			return self::$parameters;
		}
		
		if(array_key_exists($key, self::$parameters))
		{
			return self::$parameters[$key];
		}
		
		return false;
	}

	public static function set($key=null,$value=null)
	{
		self::getInstance();
		
		if (empty($key))
		{
			return false;
		}
		
		return self::$parameters[$key] = $value;
	}

	/**
	 * Total number of parameters
	 * 
	 *    Uri::total();
	 *
	 * @return int
	 */
	public static function total()
	{
		self::getInstance();
		
		return count(self::$parameters);
	}
	

	/**
	 * Create Uri
	 *
	 * @param array $uri input array
	 * @return void
	 * @author Jay Williams
	 */
	public static function create($uri=null)
	{
		self::getInstance();
		
		$output = '';
		
		if(!empty($uri))
		{
			foreach ((array)$uri as $key => $value) 
			{
				$output .= '/' . $key . '/' . $value;
			}

			$output = trim($output,'/');
		}
		
		if (!self::$clean_url) 
		{
			$output = '?uri=' . $output;
		}
		
		return $output;
	}
	
	/**
	 * Clean URI
	 * 
	 * Remove any unsafe characters and return a limited ascii result.
	 * Backslashes are converted to forward slashes, just in case a user mistyped the URI.
	 * Slashes are stripped form the beginning and end of the string as well.
	 * 
	 *   Uri::clean('/my/uri/string/');
	 *
	 * @param string $uri Unsafe Raw URI
	 * @return string $uri Cleaned URI
	 */
	public static function clean($uri = NULL)
	{
		if(empty($uri))
			return '';
		
		$uri = strtolower($uri);
		$uri = preg_replace('/[^a-z0-9\/\-\,\_\+\!\'\(\)]/', '', $uri);
		$uri = preg_replace('/\/+/', '/', $uri);
		$uri = trim($uri,'/');
		
		return $uri;
	}


} //endclass
