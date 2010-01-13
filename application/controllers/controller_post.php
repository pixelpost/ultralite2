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
		$this->site = new stdClass;
		$this->paginator = new stdClass;
		
		$this->render();
	}
	
}
