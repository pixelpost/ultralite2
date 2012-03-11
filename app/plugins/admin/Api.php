<?php

namespace pixelpost\plugins\admin;

use pixelpost\core\Event;

class Api
{
	public static function api_version(Event $event)
	{
		$event->response = array('version' => Plugin::version());
	}
}