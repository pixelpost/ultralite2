<?php

namespace pixelpost\plugins\admin;

class Api
{
	public static function admin_version(pixelpost\Event $event)
	{
		$event->response = array('version' => Plugin::version());
	}
}