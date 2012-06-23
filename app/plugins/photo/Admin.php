<?php

namespace pixelpost\plugins\photo;

use pixelpost\core\Event,
	pixelpost\core\Template,
	pixelpost\plugins\api\Plugin as Api;

class Admin
{
	protected static $_page_home_active = false;

	public static function template_nav(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('is_active', static::$_page_home_active)
			  ->assign('url', 'photos')
			  ->assign('name', 'photos')
			  ->render('admin/tpl/_menu.tpl');
	}

	public static function template_widget(Event $event)
	{
		$result = Api::call('photo.count');

		$count  = $result['total'];

		$event->response[] = Template::create()
			  ->assign('count', $count)
			  ->assign('url', 'photos')
			  ->assign('text', 'photos')
			  ->render('admin/tpl/_widget.tpl');
	}

	public static function page_index(Event $event)
	{
		static::$_page_home_active = true;

		require __DIR__ . '/admin/home.php';
	}

	public static function settings(Event $event)
	{
		Template::create()->publish('photo/tpl/admin/about.tpl');
	}
}