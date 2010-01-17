<?php
/**
 * Site Class
 * 
 * A simple way for templates to access config options.
 * All options are encoded with HTML entities and they 
 * do not update or change the original config.
 *
 * @package Pixelpost
 * @author Jay Williams
 */

class Site
{
	
	public function __construct()
	{
	}

	public function __isset($property)
	{
		return isset(Config::current()->$property);
	}

	public function __get($property)
	{
		$this->$property = Helper::entities(Config::current()->$property);
		return $this->$property;
	}

} //endclass
