<?php

/**
* Post Controller
*/
class Controller_Post extends Controller
{
	var $post;
	
	public function indexAction($uri=array())
	{
		
		$this->post = new Post($uri['post']);
		// $this->paginator = new Void;
		
		if (!$this->post->success) {
			return Error::quit(404, 'So Sorry!', 'The post you are trying to view doesn\'t exist. Please try visiting the <a href="'.Config::current()->url.'">home page</a>.');
		}
		
		return $this->output();
	}
	
}
