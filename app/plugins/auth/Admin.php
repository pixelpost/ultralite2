<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Template,
	pixelpost\core\Event;

class Admin
{
	protected static $_page_account_active = false;

	public static function template_nav(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('is_active', static::$_page_account_active)
			  ->assign('url', 'auth/account')
			  ->assign('name', 'account')
			  ->render('admin/tpl/_menu.php');
	}

	public static function template_navbar(Event $event)
	{
		$event->response[] = Template::create()
				->assign('user', Plugin::get_entity_name())
				->render('auth/tpl/admin-navbar.php');
	}

	public static function page_account(Event $event)
	{
		static::$_page_account_active = true;

		require __DIR__ . '/admin/account.php';
	}

	public static function page_about(Event $event)
	{
		Template::create()->publish('auth/tpl/about.php');
	}
}