<?php

/**
* Archive Controller
*/
class Controller_Archive extends Controller
{
	var $archive;
	
	public function indexAction($uri=array())
	{
		
		$this->archive = new Archive($uri['archive']);
		// $this->paginator = new Void;
		
		if (!$this->archive->success) {
			return Error::quit(404, 'So Sorry!', 'The archive you are trying to view doesn\'t exist. Please try visiting the <a href="'.Config::current()->url.'">home page</a>.');
		}
		
		return $this->output();
	}
	
}
