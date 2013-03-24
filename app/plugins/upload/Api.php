<?php

namespace pixelpost\plugins\upload;

use pixelpost\core\Event,
	pixelpost\core\Filter,
	pixelpost\plugins\auth\Plugin   as Auth,
	pixelpost\plugins\api\Exception as ApiError
	;

/**
 * Provide API methods for managing Uploads
 *
 * @copyright 2013 Alban LEROUX <seza@paradoxal.org>
 * @license   http://creativecommons.org/licences/by-sa/3.0/ Creative Commons
 * @version   0.0.1
 * @since     File available since Release 1.0.0
 */
class Api
{
	protected static $valid_mime = array(
		'image/jpeg' => '.jpg',
		'image/png'  => '.png',
		'image/gif'  => '.gif',
		'image/bmp'  => '.bmp',
	);

	public static function upload_version(Event $event)
	{
		$event->response = array('version' => Plugin::version());
	}

	public static function upload_init(Event $event)
	{
		require __DIR__ . '/api/upload_init.php';
	}

	public static function upload_send(Event $event)
	{
		require __DIR__ . '/api/upload_send.php';
	}

	public static function upload_end(Event $event)
	{
		require __DIR__ . '/api/upload_end.php';
	}

	public static function upload_max_size(Event $event)
	{
		if (!Auth::is_granted('write')) throw new ApiError\Ungranted('upload.max-size');

		$memory_limit  = Filter::shortland_size_to_bytes(ini_get('memory_limit'));
		$post_max_size = Filter::shortland_size_to_bytes(ini_get('post_max_size'));

		$event->response = array('max_size' => min($memory_limit, $post_max_size));
	}
}