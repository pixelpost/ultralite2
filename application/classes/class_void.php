<?php
/**
 * Void
 * 
 * An empty class, which will go on forever and still return noting.
 * Also an easy way to avoid PHP Notice errors in templates.
 *
 * @package Pixelpost
 * @author Jay Williams
 */

class Void
{ 
	public function __construct()
	{
		return $this;
	}
	
	public function __get($name)
	{
		return $this;
	}
	
	public function __isset($name)
	{
		return false;
	}

	public function __call($name, $arguments)
	{
		return $this;
	}
	
	public function __toString()
	{
		return '';
	} 
}