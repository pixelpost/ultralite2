<?php

namespace pixelpost\plugins\admin;

use pixelpost\core\Event,
	pixelpost\core\Template,
	pixelpost\core\Config;

class Page
{
	public static function page_index(Event $event)
	{
		require __DIR__ . SEP . 'page' . SEP . 'home.php';
	}

	public static function page_404(Event $event)
	{
		require __DIR__ . SEP . 'page' . SEP . '404.php';
	}

	public static function page_api_test(Event $event)
	{
		require __DIR__ . SEP . 'page' . SEP . 'api_test.php';
	}

	public static function template_widget(Event $event)
	{
		$event->response[] = Template::create()
			->assign('count', 'v' . Config::create()->version)
			->assign('text', 'settings')
			->render('admin/tpl/_widget.php');
	}
}