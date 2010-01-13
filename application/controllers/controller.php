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
			return false;
		
		ob_start();
		include $template;
		$this->output = ob_get_contents();
		ob_end_clean();
		
		return $this->output;
	}
}
