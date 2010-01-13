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
		$this->site = new Void; // Untill we create a proper Site class
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
