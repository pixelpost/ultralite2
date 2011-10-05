<?php

namespace pixelpost\plugins\admin;

use pixelpost;

class Api
{
	public static function api_version(pixelpost\Event $event)
	{
		$event->response = array('version' => Plugin::version());
	}
}