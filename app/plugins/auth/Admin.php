<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Template,
	pixelpost\core\Event;

class Admin
{
	protected static $_page_account_active     = false;
	protected static $_tab_setting_user_active = false;

	public static function template_nav(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('is_active', static::$_page_account_active)
			  ->assign('url', 'auth/account')
			  ->assign('name', 'account')
			  ->render('admin/tpl/_menu.tpl');
	}

	public static function template_navbar(Event $event)
	{
		$event->response[] = Template::create()
				->assign('user', Plugin::get_entity_name())
				->render('auth/tpl/admin-navbar.tpl');
	}

	public static function template_settings_tab(Event $event)
	{
		if (!Plugin::is_granted('admin')) return;

		$event->response[] = Template::create()
			  ->assign('is_active', static::$_tab_setting_user_active)
			  ->assign('url', 'settings/users')
			  ->assign('name', 'Users')
			  ->render('admin/tpl/_menu.tpl');
	}

	public static function page_account(Event $event)
	{
		static::$_page_account_active = true;

		require __DIR__ . '/admin/account.php';
	}

	public static function page_users(Event $event)
	{
		static::$_tab_setting_user_active = true;

		require __DIR__ . '/admin/users.php';
	}

	public static function page_user(Event $event)
	{
		static::$_tab_setting_user_active = true;

		require __DIR__ . '/admin/user.php';
	}

	public static function page_about(Event $event)
	{
		Template::create()->publish('auth/tpl/about.tpl');
	}

	public static function secure_settings(Event $event)
	{
		if (!Plugin::is_granted('config'))
		{
			Template::create()
				->assign('title', 'settings')
				->publish('auth/tpl/unauth.tpl');

			// stop the event processing
			return false;
		}
	}
}