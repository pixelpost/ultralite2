<?php
/**
 * Site
 * 
 * Gather shared site information, for templates
 * All content is encoded in proper HTML entities
 *
 * @package Pixelpost
 * @author Jay Williams
 */

class Site
{
	public $entities = true;
	
	public function __construct($entities=null)
	{
		if ($entities !== null)
			$this->entities == (bool) $entities;
		
		$config = Config::current()->site;
		
		// Escape values if $this->entities is true
		if ($this->entities)
		{
			foreach ($config as $setting => $value)
					$this->$setting = Helper::entities($value);
		}
		else
		{
			foreach ($config as $setting => $value)
					$this->$setting = $value;
		}
		

	}

	/**
	 * Checks if a sub-class exists when an empty() or isset() 
	 * function is called on a non-existent property.
	 * 
	 * Input:
	 *    "test" ($post->test)
	 * Output:
	 *    true (if the class "Post_Test" exists)
	 */
	public function __isset($property)
	{
		$class_name = __CLASS__ . '_' . ucfirst($property);
		
		if (class_exists($class_name))
			return true;
		else
			return false;
	}

	/**
	 * Loads the sub class, when requested
	 */
	public function __get($property)
	{
		$class_name = __CLASS__ . '_' . ucfirst($property);
		
		if (class_exists($class_name))
			return new $class_name($this->entities);
		
		// Return an empty placeholder, if no class exists
		return new Void;
	}


} //endclass
