<?php

namespace pixelpost\plugins\admin;

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
		
	public static function page_api_test(pixelpost\Event $event)
	{
		require __DIR__ . SEP . 'page' . SEP . 'api_test.php';
	}
}