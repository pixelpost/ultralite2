<?php
/**
 * Loader
 * 
 * Directory scanner and autoloader
 *
 * @package Pixelpost
 * @author Jay Williams
 */

class Loader
{
	public static $paths = array();
	
	public static $files = array();

	private static $instance;


	public static function & getInstance()
	{
		static $instance = null;
		
		return $instance = (empty($instance)) ? new self() : $instance;
	}


	private function __construct()
	{
		// Setup Default paths:
		
		// Controller paths to Scan:
		self::$paths['controller'][] = APPPATH.'controllers/';

		// Class paths to Scan:
		self::$paths['class'][] = APPPATH.'classes/';

		// Language paths to Scan:
		self::$paths['language'][] = APPPATH.'languages/';

		// Page paths to Scan:
		self::$paths['template'][] = CONTENTPATH.'templates/simple/';
	}


	public static function scan()
	{
		self::getInstance();
		
		self::$files = array();
		
		foreach (self::$paths as $type => $paths)
		{
			self::$files[$type] = array();
			
			foreach ($paths as $path)
			{
				// Add a trailing slash, if it doesn't exist
				if(!substr($path,-1) == '/')
					$path = $path . '/';
				
				if(!is_dir($path))
					continue;
				
				$matches = glob($path.$type.'*.php');
				foreach ($matches as $file)
				{
					self::$files[$type][basename($file,'.php')] = $file;
				}
			}
			
			// Sort the files alphabetically
			ksort(self::$files[$type]);
		}
		
		return true;
	}
	
	public static function exists($type,$name)
	{
		self::getInstance();
		
		if (array_key_exists($name, self::$files[$type] ))
			return self::$files[$type][$name];
		else
			return false;
	}
	
	public static function find($type,$names=null)
	{
		self::getInstance();
		
		if (!array_key_exists($type,self::$files)) 
			return false;
		
		// If no names are specfied, use the page URI
		if (empty($names))
			$names = array_keys(Uri::get());
		elseif(is_string($names))
			$names = explode('_',$names);
		
		
		// Add the first section, if its not listed
		if (!array_key_exists($type,$names))
			array_unshift($names,$type);
		
		$total = count($names);

		for ($i=0; $i < $total; $i++)
		{
			$name = implode($names,'_');
			
			$file = self::exists($type,$name);
			if ($file) return $file;
			
			// Remove the last section on each pass
			array_pop($names);
		}
	}
	
	public static function load($file)
	{
		if (!file_exists($file))
			return false;
			
		return include $file;
	}
	
	public static function autoload($class_name)
	{
		self::getInstance();
		
		$type = 'class';
		$name = strtolower($type.'_'.$class_name);
		
		// If the scanner hasn't run yet, we can still include the core classes
		if (empty(self::$files) && self::load(APPPATH.'classes/'.$name.'.php'))
		 	return true;
		
		return self::load(self::exists($type,$name));
		
	}

} //endclass
