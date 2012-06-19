<?php

namespace pixelpost\plugins\admin;

use pixelpost\core\Event,
	pixelpost\core\Template,
	pixelpost\core\Config,
	pixelpost\plugins\pixelpost\Plugin as PP;

class Page
{
	protected static $_page_setting_active = false;

	public static function __callStatic($__name, $__args)
	{
		$event = current($__args);

		require __DIR__ . '/page/' . str_replace('_', '/', $__name) . '.php';
	}

	public static function settings(Event $event)
	{
		static::$_page_setting_active = true;

		PP::route($event);
	}

	public static function template_nav_phpinfo(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('url', 'phpinfo')
			  ->assign('name', 'php info')
			  ->render('admin/tpl/_menu.tpl');
	}

	public static function template_nav(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('is_active', static::$_page_setting_active)
			  ->assign('url', 'settings')
			  ->assign('name', 'settings')
			  ->render('admin/tpl/_menu.tpl');
	}

	public static function template_widget(Event $event)
	{
		$event->response[] = Template::create()
			->assign('count', 'v' . Config::create()->version)
			->assign('text', 'settings')
			->render('admin/tpl/_widget.tpl');
	}
}