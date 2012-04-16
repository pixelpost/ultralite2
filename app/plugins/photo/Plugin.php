<?php

namespace pixelpost\plugins\photo;

use pixelpost\core\Config,
	pixelpost\core\Event,
	pixelpost\core\PluginInterface,
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
		return array(
			'pixelpost' => '0.0.1',
			'api'       => '0.0.1',
			'auth'      => '0.0.1',
		);
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

		$path = ROOT_PATH . '/photos';

		mkdir($path              , 0775);
		mkdir($path . '/original', 0775);
		mkdir($path . '/resized' , 0775);
		mkdir($path . '/thumb'   , 0775);

		mkdir(PRIV_PATH . '/upload', 0775);

		return true;
	}

	public static function uninstall()
	{
		$conf = Config::create();

		$photo_dir  = ROOT_PATH . '/' . $conf->plugin_photo->directory;
		$upload_dir = PRIV_PATH . '/upload';

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
		$api    = __NAMESPACE__ . '\Api';
		$admin  = __NAMESPACE__ . '\Admin';
		$pp     = '\pixelpost\plugins\pixelpost\Plugin';

		Event::register_list(array(
			// api events
			array('api.photo.version',     $api    . '::photo_version'),
			array('api.photo.add',         $api    . '::photo_add'),
			array('api.photo.del',         $api    . '::photo_del'),
			array('api.photo.set',         $api    . '::photo_set'),
			array('api.photo.get',         $api    . '::photo_get'),
			array('api.photo.list',        $api    . '::photo_list'),
			array('api.photo.count',       $api    . '::photo_count'),
			array('api.photo.path',        $api    . '::photo_path'),
			array('api.photo.size',        $api    . '::photo_size'),
			array('api.photo.config.get',  $api    . '::config_get'),
			array('api.photo.config.set',  $api    . '::config_set'),
			array('api.upload.init',       $api    . '::upload_init'),
			array('api.upload.send',       $api    . '::upload_send'),
			array('api.upload.end',        $api    . '::upload_end'),
			array('api.upload.max-size',   $api    . '::upload_max_size'),
			// admin web interface
			array('admin.photos',          $pp     . '::route'),
			array('admin.photos.index',    $admin  . '::page_index'),
			array('admin.template.nav',    $admin  . '::template_nav'),
			array('admin.template.widget', $admin  . '::template_widget'),
		));
	}
}