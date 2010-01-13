<?php

/**
* Base Controller
*/
class Controller
{
	var $site;
	
	function __construct()
	{
		// Initalize Global $site properties
		$this->site = new Void; // Untill we create a proper Site class
	}
	
	public function indexAction($uri=array())
	{
		$this->render();
	}
	
	public function render()
	{
		$template = Loader::find('template',true);
		
		if (!file_exists($template))
			return false;
		
		include $template;
	}
}
