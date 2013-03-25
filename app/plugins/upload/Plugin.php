<?php

namespace pixelpost\plugins\upload;

use pixelpost\core\Fs
	pixelpost\core\PluginInterface;

class Plugin implements PluginInterface
{
	const UPLOAD_DIR = 'upload';

	public static function get_upload_dir()
	{
		return PRIV_PATH . '/' . static::UPLOAD_DIR;
	}

	public static function version()
	{
		return '0.0.1';
	}

	public static function depends()
	{
		return array(
			'main' => '0.0.1',
		);
	}

	public static function install()
	{
		mkdir(static::get_upload_dir(), 0775);

		return true;
	}

	public static function uninstall()
	{
		Fs::delete(static::get_upload_dir());

		return true;
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		$api = __NAMESPACE__ . '\Api';

		Event::register_list(array(
			array('api.upload.version',    $api . '::upload_version'),
			array('api.upload.init',       $api . '::upload_init'),
			array('api.upload.send',       $api . '::upload_send'),
			array('api.upload.end',        $api . '::upload_end'),
			array('api.upload.max-size',   $api . '::upload_max_size'),
		));
	}
}