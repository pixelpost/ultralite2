<?php

namespace pixelpost\plugins\admin;

use pixelpost\core\Event,
	pixelpost\core\Template,
	pixelpost\core\Config,
	pixelpost\plugins\pixelpost\Plugin as PP;

class Page
{
	protected static $_page_setting_active = false;

	public static function page_index(Event $event)
	{
		require __DIR__ . '/page/home.php';
	}

	public static function page_404(Event $event)
	{
		require __DIR__ . '/page/404.php';
	}

	public static function page_settings(Event $event)
	{
		static::$_page_setting_active = true;

		PP::route($event);
	}

	public static function page_settings_index(Event $event)
	{
		require __DIR__ . '/page/settings/index.php';
	}

	public static function template_nav_phpinfo(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('url', 'phpinfo')
			  ->assign('name', 'php info')
			  ->render('admin/tpl/_menu.php');
	}

	public static function template_nav(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('is_active', static::$_page_setting_active)
			  ->assign('url', 'settings')
			  ->assign('name', 'settings')
			  ->render('admin/tpl/_menu.php');
	}

	public static function template_widget(Event $event)
	{
		$event->response[] = Template::create()
			->assign('count', 'v' . Config::create()->version)
			->assign('text', 'settings')
			->render('admin/tpl/_widget.php');
	}
}