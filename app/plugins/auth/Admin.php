<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Template,
	pixelpost\core\Event,
	pixelpost\core\Config,
	pixelpost\plugins\api\Plugin as api;

class Admin
{
	public static function template_nav(Event $event)
	{
		$event->response[] = Template::create()
			  ->assign('url', 'auth/account')
			  ->assign('name', 'account')
			  ->render('admin/tpl/_menu.php');
	}

	public static function template_footer(Event $event)
	{
		$event->response[] = Template::create()
				->assign('user', Plugin::get_entity_name())
				->render('auth/tpl/admin-footer.php');
	}

	public static function template_css(Event $event)
	{
		$event->response[] = Template::create()->render('auth/tpl/admin-css.php');
	}

	public static function template_js(Event $event)
	{
		$event->response[] = Template::create()->render('auth/tpl/admin-js.php');
	}

	public static function page_api_bridge(Event $event)
	{
		// this is use web authentication instead of api authentication to
		// call api methods.
		// So, no tokens, no hmac, no nonce. More simple for admin JS calls.
		$event->redirect('request.api');
	}

	public static function page_account(Event $event)
	{
		require __DIR__ . SEP . 'admin' . SEP . 'account.php';
	}
}