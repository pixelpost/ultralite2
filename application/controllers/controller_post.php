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
			return Error::quit(404, 'Oh No!', 'We can\'t seem to locate the template file. Please try visiting the <a href="/">home page</a>.');
		}
		
		return $this->output();
	}
	
}
