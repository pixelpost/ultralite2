<?php
/**
 * Site
 * 
 * Access to the global site configuration
 *
 * @package Pixelpost
 * @author Jay Williams
 */

class Site
{

	public function __construct()
	{
		
	}

	/**
	 * Check if property or sub-class exists
	 */
	public function __isset($property)
	{
		$class_name = __CLASS__ . '_' . ucfirst($property);
		
		// var_dump("ISSET: $class_name");
		
		if (isset($this->$property))
			return true;
		elseif (class_exists($class_name))
			return true;
		else
			return false;
	}

	/**
	 * Load sub-class, on request
	 */
	public function __get($property)
	{
		$class_name = __CLASS__ . '_' . ucfirst($property);
		
		// var_dump("GET: $class_name");
		
		if (class_exists($class_name))
			return new $class_name();
		
		// Return an empty placeholder, if no class exists
		return new Void;
	}


} //endclass
