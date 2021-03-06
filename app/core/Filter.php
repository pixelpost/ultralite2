<?php

namespace pixelpost\core;

/**
 * Filter support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Filter
{

	/**
	 * Check $param is a string
	 *
	 * @param  mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_string($param)
	{
		if (!is_string($param)) throw new Error(1, array('string'));
	}

	/**
	 * Check $param is an int
	 *
	 * @param  mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_int($param)
	{
		if (!is_int($param)) throw new Error(1, array('int'));
	}

	/**
	 * Check $param is a float
	 *
	 * @param  mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_float($param)
	{
		if (!is_float($param)) throw new Error(1, array('float'));
	}

	/**
	 * Check $param is a bool
	 *
	 * @param  mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_bool($param)
	{
		if (!is_bool($param)) throw new Error(1, array('bool'));
	}

	/**
	 * Check $param is null
	 *
	 * @param  mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_null($param)
	{
		if (!is_null($param)) throw new Error(1, array('null'));
	}

	/**
	 * Check $param is an array
	 *
	 * @param mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_array($param)
	{
		if (!is_array($param)) throw new Error(1, array('array'));
	}

	/**
	 * Check $param is a resource
	 *
	 * @param mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_resource($param)
	{
		if (!is_resource($param)) throw new Error(1, array('resource'));
	}

	/**
	 * Check $param is an object
	 *
	 * @param mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_object($param)
	{
		if (!is_object($param)) throw new Error(1, array('object'));
	}

	/**
	 * Check $param is a DateTime object
	 *
	 * @param mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_date($param)
	{
		if (!$param instanceof \DateTime) throw new Error(1, array('DateTime'));
	}

	/**
	 * Check $param is a numeric value/format
	 *
	 * @param mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_numeric($param)
	{
		if (!is_numeric($param)) throw new Error(1, array('numeric'));
	}

	/**
	 * Check $param is a scalar
	 *
	 * @param mixed $param
	 * @throws pixelpost\core\Error
	 */
	public static function is_scalar($param)
	{
		if (!is_scalar($param)) throw new Error(1, array('scalar'));
	}

	/**
	 * Ensure $var is a string
	 *
	 * @param mixed $var
	 */
	public static function assume_string(&$var)
	{
		try
		{
			self::is_scalar($var);

			$var = strval($var);
		}
		catch (\Exception $e)
		{
			$var = '';
		}
	}

	/**
	 * Ensure $var is a int
	 *
	 * @param mixed $var
	 */
	public static function assume_int(&$var)
	{
		try
		{
			self::is_scalar($var);

			$var = intval($var);
		}
		catch (\Exception $e)
		{
			$var = 0;
		}
	}

	/**
	 * Ensure $var is a float
	 *
	 * @param mixed $var
	 */
	public static function assume_float(&$var)
	{
		try
		{
			self::is_scalar($var);

			$var = floatval($var);
		}
		catch (\Exception $e)
		{
			$var = 0.0;
		}
	}

	/**
	 * Ensure $var is a bool
	 *
	 * @param mixed $var
	 */
	public static function assume_bool(&$var)
	{
		try
		{
			self::is_scalar($var);

			$var = (bool) $var;
		}
		catch (\Exception $e)
		{
			$var = false;
		}
	}

	/**
	 * Ensure $var is an array
	 *
	 * @param mixed $var
	 */
	public static function assume_array(&$var)
	{
		if (!is_array($var)) $var = array($var);
	}

	/**
	 * Validate an email adresse format
	 *
	 * @param string $mail
	 * @return bool
	 */
	public static function validate_email($mail)
	{
		self::assume_string($mail);

		// http://www.faqs.org/rfcs/rfc2822.html
		// http://www.faqs.org/rfcs/rfc1035.html

		$a = '[-a-z0-9!\#\$%&\'*+\\/=?^_`{|}~]';

		$d = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)';

		$regex = '#^' . $a . '+(\.' . $a . '+)*@' . '(' . $d . '{1,63}\.)+' . $d . '{2,63}$#i';

		return (preg_match($regex, $mail) == 1);
	}

	/**
	 * Check $param is a valid DateTime string [in $format]
	 *
	 * @param  string $date
	 * @param  string $format
	 * @return bool
	 */
	public static function validate_date($param, $format = '')
	{
		if ($format != '') return (bool) date_create_from_format ($format, $param);

		return !(strtotime($param) === false);
	}

	/**
	 * Remove all accents of a string
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function format_without_accent($string)
	{
		self::assume_string($string);

		$accent   = utf8_decode('ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËéèêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ');
		$noaccent = utf8_decode('AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn');

		return utf8_encode(strtr((string) utf8_decode($string), $accent, $noaccent));
	}

	/**
	 * Delete all non ASCII char, try to replace some common by iphen or
	 * equivalent.
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function format_for_url($string)
	{
		self::assume_string($string);

		$s = array('&', 'æ', 'œ', 'Œ', 'Æ', '©', '®', "\r", "\n", "\t", ' ', '\'');
		$r = array('-', 'ae', 'oe', 'oe', 'ae', '(c)', '(r)', '-', '-', '-', '-', '-');

		$string  = self::format_without_accent($string);
		$string  = strtolower(str_replace($s, $r, strip_tags($string)));

		$invalid = preg_replace('#[0-9a-z_-]#', '', $string);

		if ($invalid != '') $string = str_replace(str_split($invalid), '', $string);

		$string = preg_replace('#-+#', '-', $string);

		return self::urlencode($string);
	}

	/**
	 * Delete all XML illegal character
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function format_for_xml($string)
	{
		self::assume_string($string);

		$s = array('&', "\r", "\n", '<', '>');

		return str_replace($s, '', $string);
	}

	/**
	 * Check the string encoding, ideal for incoming data !
	 *
	 * @param string $str
	 * @param string $toEncoding
	 */
	public static function check_encoding(&$str, $toEncoding = 'UTF-8')
	{
		if (mb_check_encoding($str, $toEncoding) == false)
		{
			$str = mb_convert_encoding($str, $toEncoding, mb_detect_encoding($str));
		}
	}

	/**
	 * Same as urlencode() in php core but fix the apache 404 bug due to %2f
	 * (slashes) interpretation. (this came up when we urlencode an url for
	 * example, and this data is not in the queryString part (cf, url_rewrite).
	 *
	 * @param  string $str
	 * @return string
	 */
	public static function urlencode($str)
	{
		self::assume_string($str);

		// évite le bug apache erreur 404 à cause des / (%2f)
		// interdit dans les url (seulement autorisée dans la query string)
		return str_replace(array('%2f','%2F'), '%252F', urlencode($str));
	}

	/**
	 * Convert multidimentionnal array to arrayObject
	 *
	 * @param  array $array
	 * @return ArrayObject
	 */
	public static function array_to_arrayObject($array)
	{
		$func = '\\' . __CLASS__ . '::' . __FUNCTION__;

		if (is_array($array)) return new \ArrayObject(array_map($func, $array), \ArrayObject::ARRAY_AS_PROPS);

		return $array;
	}

	/**
	 * Convert multidimentionnal array to object
	 *
	 * @param  array $array
	 * @return stdClass
	 */
	public static function array_to_object($array)
	{
		$func = '\\' . __CLASS__ . '::' . __FUNCTION__;

		if (is_array($array)) return (object) array_map($func, $array);

		return $array;
	}

	/**
	 * Return only a column from a multidimentionnal array
	 *
	 * @param array $array
	 * @param mixed $column
	 * @return array
	 */
	public static function array_column($array, $column)
	{
		$a = array();

		foreach ($array as $key => $value)
		{
			if (isset($value[$column])) $a[$key] = $value[$column];
		}

		return $a;
	}

	/**
	 * Convert multidimantionnal object to array
	 *
	 * @param  stdClass $object
	 * @return array
	 */
	public static function object_to_array($object)
	{
		$func = '\\' . __CLASS__ . '::' . __FUNCTION__;

		// use get_object_vars don't work correctly if object properties are
		// numbers.
		if (is_object($object)) $object = (array) $object;

		if (is_array($object)) return array_map($func, $object);

		return $object;
	}

	/**
	 * Convert a string to a Datetime object
	 *
	 * @param string $date
	 */
	public static function str_to_date(&$date)
	{
		$date = new \DateTime($date);
	}

	/**
	 * Compare two version number formater like A.B.C-alpha where:
	 * A is the major version (BC break possible)
	 * B is the minor version (Non BC beak)
	 * C is the bug fixes release version
	 *
	 * Return true if $new is upper to $old
	 * Return null if $new is equal to $old
	 * Return false if $old is upper to $new
	 *
	 * @param  string $old
	 * @param  string $new
	 * @return mixed
	 */
	public static function compare_version($old, $new)
	{
		$cmp = version_compare($old, $new);

		if ($cmp < 0)   return true;
	    if ($cmp === 0) return null;

		return false;
	}

	/**
	* Return shortland size value (e.g 8M, 512K) in bytes.
	*
	* @param  string $size
	* @return int
	*/
	public static function shortland_size_to_bytes($size)
	{
		$size = trim($size);

		switch (mb_strtolower($size[mb_strlen($size) - 1]))
		{
			case 'g': $size *= 1024;
			case 'm': $size *= 1024;
			case 'k': $size *= 1024;
		}

		return $size;
	}
}

