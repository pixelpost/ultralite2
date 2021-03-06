<?php

namespace pixelpost\core;

use DateTime, DateTimeZone, Exception;

/**
 * Provide template management.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Template
{
	/**
	 * @var bool Is cache Raw Template file is authorized ?
	 */
	protected $_cacheRawTemplate = true;

	/**
	 * @var Config The App configuration
	 */
	protected $_config = null;

	/**
	 * @var String The template path
	 */
	protected $_tplPath = '';

	/**
	 * @var String The template cache path
	 */
	protected $_tplCache = '';

	/**
	 * Create a new Template
	 *
	 * @return pixelpost\core\Template
	 */
	public static function create()
	{
		$tpl = new static;
		$tpl->set_template_path(PLUG_PATH);
		$tpl->set_template_cache_path(PRIV_PATH . '/cache');
		return $tpl;
	}

	/**
	 * Change the cache setting, by default set it to true
	 *
	 * @param  bool $active
	 * @return pixelpost\core\Template
	 */
	public function set_cache_raw_template($active = true)
	{
		Filter::assume_bool($active);

		$this->_cacheRawTemplate = $active;

		return $this;
	}

	/**
	 * Change the template path
	 *
	 * @param string $path
	 */
	public function set_template_path($path)
	{
		Filter::assume_string($var);

		$this->_tplPath = $path;

		return $this;
	}

	/**
	 * Change the cache template path
	 *
	 * @param string $path
	 */
	public function set_template_cache_path($path)
	{
		Filter::assume_string($var);

		$this->_tplCache = $path;

		return $this;
	}

	/**
	 * Return the App configuration object (pixelpost\core\config::create())
	 * This is a facility method to be called into template file.
	 *
	 * @return pixelpost\core\Config
	 */
	public function config()
	{
		return $this->_config;
	}

	/**
	 * Add data into this template
	 *
	 * @throws pixelpost\core\Error
	 * @param  mixed $var
	 * @param  mixed $value
	 * @return pixelpost\core\Template
	 */
	public function assign($var, $value = null)
	{
		if (is_string($var))     $this->$var = Filter::array_to_arrayObject($value);
		elseif (!is_array($var)) throw Error::create(11);
		else                     foreach ($var as $key => $val) $this->$key = Filter::array_to_arrayObject($val);

		return $this;
	}

	/**
	 * Create a new instance of self.
	 */
	public function __construct()
	{
		$this->_config = Config::create();
	}

	/**
	 * Throw always an Error exception, if this is called, this is because a
	 * non existant template data try to be used.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $key
	 * @return null
	 */
	public function __get($key)
	{
		throw Error::create(12, array($key));

		return null;
	}

	/**
	 * Check if a tempalte data exists. Always return false on protected or
	 * private data (eg. starting by a underscore).
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		if ('_' != mb_substr($key, 0, 1)) return isset($this->$key);

		return false;
	}

	/**
	 * Call a template data as a method if it callable, else throw an Error
	 * exception.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $name
	 * @param  array  $args
	 * @return mixed
	 */
	public function __call($name, array $args)
	{
		if (!isset($name)) throw Error::create(13, array($name));

		if (!is_callable($this->$name)) throw Error::create(14, array($name));

		return call_user_func_array($this->$name, $args);
	}

	/**
	 * Add or Change a template data value. If the data is private or protected,
	 * (eg. starting with a underscore) throw an Error Exception
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $key
	 * @param  mixed $val
	 */
	public function __set($key, $val)
	{
		if ('_' == mb_substr($key, 0, 1)) throw Error::create(15, array($key));

		$this->$key = $val;
	}

	/**
	 * Remove a tempalte data if it's not a private or protected data (eg.
	 * starting with a underscore).
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		if ('_' != mb_substr($key, 0, 1) && isset($this->$key)) unset($this->$key);
	}

	/**
	 * Internal filter: Escape a string for html content
	 *
	 * @param  string $string
	 * @return string
	 */
	protected function _filter_escape($string)
	{
		Filter::check_encoding($string);

		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
	}

	/**
	 * Internal filter: check if a number is between $min and $max (included)
	 *
	 * @param  mixed $number
	 * @param  mixed $min
	 * @param  mixed $max
	 * @return bool
	 */
	protected function _filter_between($number, $min, $max)
	{
		Filter::assume_float($number);
		Filter::assume_float($min);
		Filter::assume_float($max);

		return ($min <= $number && $number <= $max);
	}

	/**
	 * Internal filter: sort a array (keep key associated)
	 *
	 * @param  array $array
	 * @return array
	 */
	protected function _filter_array_sort(array $array)
	{
		asort($array);
		return $array;
	}

	/**
	 * Internal filter: sort a array in reverse order (keep key associated)
	 *
	 * @param  array $array
	 * @return array
	 */
	protected function _filter_array_rsort(array $array)
	{
		arsort($array);
		return $array;
	}

	/**
	 * Internal filter: sort a array in natural order (keep key associated)
	 *
	 * @param  array $array
	 * @return array
	 */
	protected function _filter_array_nsort(array $array)
	{
		natcasesort($array);
		return $array;
	}

	/**
	 * Internal filter: return the first item in an array or null
	 *
	 * @param  array $array
	 * @return mixed
	 */
	protected function _filter_array_first(array $array)
	{
		if (count($array) == 0) return null;
		$keys  = array_keys($array);
		$first = array_shift($keys);
		return $array[$first];
	}

	/**
	 * Internal filter: return the last item in an array or null
	 *
	 * @param  array $array
	 * @return mixed
	 */
	protected function _filter_array_last(array &$array)
	{
		if (count($array) == 0) return null;
		$keys = array_keys($array);
		$last = array_pop($keys);
		return $array[$last];
	}

	/**
	 * Internal filter: return $yes if $data is true else $no.
	 *
	 * @param  mixed $data
	 * @param  mixed $yes
	 * @param  mixed $no
	 * @return mixed
	 */
	protected function _filter_if($data, $yes, $no)
	{
		return ($data) ? $yes : $no;
	}

	/**
	 * Internal filter: Format a DateTime object.
	 *
	 * @param  DateTime $date
	 * @param  int      $datetime
	 * @param  string   $format
	 * @return string
	 */
	protected function _filter_date($date, $type, $format, $print_tz = false)
	{
		$tz  = new DateTimeZone(Config::create()->timezone);
		$ftz = ' T';

		if (is_string($date)) $date = new DateTime($date, $tz);

		$date->setTimezone($tz);

		switch($format)
		{
			case 'longer' :  // Saturday 8th of december 2005, 03:04 PM
				if     ($type == 'date')	 $format = 'l jS \of F Y';
				elseif ($type == 'datetime') $format = 'l jS \of F Y, h:i A';
				else                         $format = 'h:i A';
				break;
			case 'long' :    // Sat 8th of dec 2005, 03:04 PM
				if     ($type == 'date')	 $format = 'D jS \of M Y';
				elseif ($type == 'datetime') $format = 'D jS \of M Y, h:i A';
				else                         $format = 'h:i A';
				break;
			case 'default' :
			case 'small' :   // 8th dec 2005 03:04 PM
				if     ($type == 'date')	 $format = 'jS F Y';
				elseif ($type == 'datetime') $format = 'jS F Y h:i A';
				else                         $format = 'h:i A';
				break;
			case 'smaller' : // 08-12-2005 03:04 PM
				if     ($type == 'date')	 $format = 'd-m-Y';
				elseif ($type == 'datetime') $format = 'd-m-Y h:i A';
				else                         $format = 'h:i A';
				break;
			case 'iso' :     // 2005-12-08 03:04
				if     ($type == 'date')	 $format = 'Y-m-d';
				elseif ($type == 'datetime') $format = 'Y-m-d H:i:s';
				else                         $format = 'H:i:s';
				break;
		}

		if ($print_tz) $format .= $ftz;

		return $date->format($format);
	}

	/**
	 * Call the event $name, pass to the event an empty array named 'response'.
	 * Finally, return the 'response' array event parameter.
	 *
	 * @param  string
	 * @return array
	 */
	protected function _event_signal($name)
	{
		return Event::signal($name, array('response' => array()))->response;
	}

	/**
	 * Internal filter: Format a number to 2 decimal.
	 *
	 * @param  mixed $data
	 * @return string
	 */
	protected function _filter_number($data)
	{
		return number_format($number, 2);
	}


	protected function _filter_asset($asset)
	{
		list($plugin, $path) = explode('::', $asset, 2);

		return CONTENT_URL . $plugin . '/' . $path;
	}

	/**
	 * Compile a template into a raw template. (eg. Transform it into a valid
	 * php code without associating the template data to it).
	 *
	 * @param  string $templateFile
	 * @return string
	 */
	protected function _compile($templateFile)
	{
		$tpl = new TemplateCompiler();
		$tpl->path = $this->_tplPath;
		$tpl->tpl  = file_get_contents($templateFile);

		$tpl->escape_raw_block();
		$tpl->remove_comment();
		$tpl->escape_escape();
		$tpl->extract_block();
		$tpl->make_extends();
		$tpl->compile_block();
		$tpl->make_include();
		$tpl->make_if();
		$tpl->make_for();
		$tpl->make_inline();
		$tpl->unescape_escape();
		$tpl->unescape_raw_block();
		$tpl->replace_php_short_open_tag();

		return $tpl->tpl;
	}

	/**
	 * Return a rendered view data into a template file.
	 * $templateFile is a relative path to PLUG_PATH contant (eg. the plugin
	 * folder).
	 *
	 * @param  string $templateFile
	 * @return string
	 */
	public function render($templateFile)
	{
		ob_start();

		try
		{
			$tpl   = $this->_tplPath  . '/' . $templateFile;
			$cache = $this->_tplCache . '/' . $templateFile;

			if (!file_exists($tpl)) throw new Error(16, array($tpl));

			// if cache is not available, cached file is not created, cache has expired
			if (!$this->_cacheRawTemplate || !file_exists($cache) || filemtime($cache) < filemtime($tpl))
			{
				// create the raw content
				$raw = $this->_compile($tpl);

				// cache is disabled; get a temp file; erase the cache filename
				if (!$this->_cacheRawTemplate)
				{
					$cache = tempnam(realpath(sys_get_temp_dir()), 'pp_tpl_cache_');
				}
				// else check if cache dir exists
				else
				{
					$path = dirname($cache);
					if (!file_exists($path)) mkdir($path, 0775, true);
				}

				// store the raw content in the file (temp or cache)
				file_put_contents($cache, $raw);

				// load the file
				include $cache;

				// if cache is disabled we clean up
				if (!$this->_cacheRawTemplate) unlink($cache);
			}
			// else load directly the cached file
			else include $cache;
		}
		catch(Exception $e) { ob_end_clean(); throw $e; }

		return ob_get_clean();
	}

	/**
	 * Print the rendered data into a template file.
	 * $templateFile is a relative path to PLUG_PATH contant (eg. the plugin
	 * folder).
	 *
	 * @param  string $templateFile
	 * @return pixelpost\core\Template
	 */
	public function publish($templateFile)
	{
		echo $this->render($templateFile);

		return $this;
	}
}
