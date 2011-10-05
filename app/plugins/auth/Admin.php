<?php

namespace pixelpost\plugins\auth;

use pixelpost;

class Admin
{
	public static function template_footer(pixelpost\Event $event)
	{
		$event->response[] = pixelpost\Template::create()
				->assign('user', Plugin::get_username())
				->render('auth/tpl/admin-footer.php');
	}
	
	public static function template_css(pixelpost\Event $event)
	{
		$event->response[] = pixelpost\Template::create()
				->render('auth/tpl/admin-css.php');
	}
}