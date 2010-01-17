<?php

/**
* Base Controller
*/
class Controller
{
	var $site;
	var $output;
	
	function __construct()
	{
		// Initalize Global $site properties
		$this->site = new Site;
	}
	
	/**
	 * To avoid template notice errors, redirect 
	 * any non-existent properties to the Void.
	 */
	public function __get($name)
	{
		return new Void;
	}
	
	/**
	 * When isset() or empty() are called on 
	 * non-existent properties, return false.
	 */
	public function __isset($name)
	{
		return false;
	}
	
	public function indexAction($uri=array())
	{
		return $this->output();
	}
	
	public function output()
	{
		$template = Loader::find('template',true);
		
		if (!file_exists($template))
		{
			return Error::message(404, 'Oh No!', 'We can\'t seem to locate the template file. Please try visiting the <a href="/">home page</a>.');
		}
		
		ob_start();
		include $template;
		$this->output = ob_get_contents();
		ob_end_clean();
		
		return $this->output;
	}
}
