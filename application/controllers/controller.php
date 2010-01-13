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
		$this->site = new stdClass;
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
