<?php

namespace pixelpost\plugins\web;

use pixelpost;

class Page
{
	public static function page_index(pixelpost\Event $event)
	{
		require __DIR__ . SEP . 'page' . SEP . 'home.php';
	}

	public static function page_404(pixelpost\Event $event)
	{
		require __DIR__ . SEP . 'page' . SEP . '404.php';
	}
}