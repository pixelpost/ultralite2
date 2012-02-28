<?php

namespace pixelpost\plugins\photo;

use pixelpost\Config,
	pixelpost\Event,
	pixelpost\PluginInterface,
	RecursiveIteratorIterator as RII,
	RecursiveDirectoryIterator as RDI;

/**
 * Photo plugin, provide API methods for managing photo content
 *
 * TODO: Better validation of data in entry of event
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin implements PluginInterface
{

	public static function version()
	{
		return '0.0.1';
	}

	public static function depends()
	{
		return array('api' => '0.0.1', 'auth' => '0.0.1');
	}

	public static function install()
	{
		$configuration = '{
			"directory" : "photos",
			"original"  : "original",
			"resized"   : "resized",
			"thumb"     : "thumb",
			"quality"   : 90,
			"sizes"     : {
				"resized" : {
					"type" : "larger-border",
					"size" : 500
				},
				"thumb" : {
					"type" : "square",
					"size" : 150
				}
			}
		}';

		$conf = Config::create();
		$conf->plugin_photo = json_decode($configuration);
		$conf->save();

		Model::table_create();

		$path = ROOT_PATH . SEP . 'photos';

		mkdir($path                   , 0775);
		mkdir($path . SEP . 'original', 0775);
		mkdir($path . SEP . 'resized' , 0775);
		mkdir($path . SEP . 'thumb'   , 0775);

		mkdir(PRIV_PATH . SEP . 'upload', 0775);

		return true;
	}

	public static function uninstall()
	{
		$conf = Config::create();

		$photo_dir  = ROOT_PATH . SEP . $conf->plugin_photo->directory;
		$upload_dir = PRIV_PATH . SEP . 'upload';

		unset($conf->plugin_photo);

		$conf->save();

		Model::table_delete();

		foreach(new RII(new RDI($photo_dir), RII::CHILD_FIRST) as $file)
		{
			$method = $file->isDir() ? 'rmdir' : 'unlink';
			$method($file->getPathName());
		}

		foreach(new RII(new RDI($upload_dir), RII::CHILD_FIRST) as $file)
		{
			$method = $file->isDir() ? 'rmdir' : 'unlink';
			$method($file->getPathName());
		}

		rmdir($photo_dir);
		rmdir($upload_dir);

		return true;
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		$api    = '\\' . __NAMESPACE__ . '\\Api';
		$admin  = '\\' . __NAMESPACE__ . '\\Admin';
		$router = '\pixelpost\plugins\router\Plugin';

		Event::register('api.photo.version',    $api . '::photo_version');
		Event::register('api.photo.add',        $api . '::photo_add');
		Event::register('api.photo.del',        $api . '::photo_del');
		Event::register('api.photo.set',        $api . '::photo_set');
		Event::register('api.photo.get',        $api . '::photo_get');
		Event::register('api.photo.list',       $api . '::photo_list');
		Event::register('api.photo.count',      $api . '::photo_count');
		Event::register('api.photo.path',       $api . '::photo_path');
		Event::register('api.photo.size',       $api . '::photo_size');
		Event::register('api.photo.config.get', $api . '::config_get');
		Event::register('api.photo.config.set', $api . '::config_set');

		Event::register('api.upload.init',      $api . '::upload_init');
		Event::register('api.upload.send',      $api . '::upload_send');
		Event::register('api.upload.end',       $api . '::upload_end');
		Event::register('api.upload.max-size',  $api . '::upload_max_size');

		Event::register('admin.photos',          $router . '::route');
		Event::register('admin.photos.index',    $admin  . '::page_index');

		Event::register('admin.template.nav',    $admin  . '::template_nav');
		Event::register('admin.template.widget', $admin  . '::template_widget');
	}
}

