<?php

namespace pixelpost\plugins\photo;

use pixelpost\Event,
	pixelpost\Template,
	pixelpost\plugins\api\Plugin as Api;

class Admin
{
	public static function template_nav(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('url', 'photos/')
			  ->assign('name', 'photos')
			  ->render('admin/tpl/_menu.php');
	}

	public static function template_widget(Event $event)
	{
		$result = Api::call_api_method('photo.count', array());

		$count  = $result['total'];

		$event->response[] = Template::create()
			  ->assign('count', $count)
			  ->assign('text', 'photos')
			  ->render('admin/tpl/_widget.php');
	}

	public static function page_index(Event $event)
	{
		require __DIR__ . SEP . 'admin' . SEP . 'home.php';
	}
}