<?php

namespace pixelpost\plugins\photo;

use pixelpost;

class Admin
{
	public static function template_nav(pixelpost\Event $event)
	{
		$event->response[] = pixelpost\Template::create()
			  ->assign('url', 'photos/')
			  ->assign('name', 'photos')
			  ->render('admin/tpl/_menu.php');
	}
	
	public static function template_widget(pixelpost\Event $event)
	{
		$e = pixelpost\Event::signal('api.photo.count', array('request' => array()));
		
		$r = $e->is_processed() ? 0 : $e->response->total;
		
		$event->response[] = pixelpost\Template::create()
			  ->assign('count', $r)
			  ->assign('text', 'photos')
			  ->render('admin/tpl/_widget.php');
	}
}