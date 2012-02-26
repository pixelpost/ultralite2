<?php

namespace pixelpost\plugins\auth;

use pixelpost\Template,
	pixelpost\Event,
	pixelpost\Config,
	pixelpost\plugins\api\Plugin as api;

class Admin
{
	public static function template_footer(Event $event)
	{
		$event->response[] = Template::create()
				->assign('key', Config::create()->uid)
				->assign('user', Plugin::get_username())
				->render('auth/tpl/admin-footer.php');
	}

	public static function template_css(Event $event)
	{
		$event->response[] = Template::create()->render('auth/tpl/admin-css.php');
	}
}