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
		
		return $this->output();
	}
	
}
