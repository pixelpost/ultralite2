<?php

namespace pixelpost\plugins\photo;

use pixelpost;

/**
 * Photo plugin, provide API methods for managing photo content
 *
 * TODO: Better validation of data in entry of event
 * 
 * Tracks Event :
 * - 'api.photo.version'
 * - 'api.photo.add'
 * - 'api.photo.del'
 * - 'api.photo.set'
 * - 'api.photo.get'
 * - 'api.photo.list'
 * - 'api.photo.count'
 * - 'api.photo.path'
 * - 'api.photo.size'
 * - 'api.photo.config.get'
 * - 'api.photo.config.set'
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin implements pixelpost\PluginInterface
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
		
		$conf = pixelpost\Config::create();		
		$conf->plugin_photo = json_decode($configuration);
		$conf->save();

		Model::table_create();

		$path = ROOT_PATH . SEP . 'photos';
		
		mkdir($path                   , 0775);
		mkdir($path . SEP . 'original', 0775);
		mkdir($path . SEP . 'resized' , 0775);
		mkdir($path . SEP . 'thumb'   , 0775);
		
		return true;
	}

	public static function uninstall()
	{	
		$conf = pixelpost\Config::create();
		
		$photoDir = $conf->plugin_photo->directory;
		
		unset($conf->plugin_photo);
		
		$conf->save();

		Model::table_delete();
		
		foreach(new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator(ROOT_PATH . SEP . $photoDir), 
					\RecursiveIteratorIterator::CHILD_FIRST) as $file)
		{
			$method = $file->isDir() ? 'rmdir' : 'unlink';
			$method($file->getPathName());
		}
		
		rmdir(ROOT_PATH . SEP . $photoDir);
		
		return true;
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		$apiClass   = '\\' . __NAMESPACE__ . '\\Api';
		$adminClass = '\\' . __NAMESPACE__ . '\\Admin';
		
		pixelpost\Event::register('api.photo.version',    $apiClass . '::photo_version');
		pixelpost\Event::register('api.photo.add',        $apiClass . '::photo_add');
		pixelpost\Event::register('api.photo.del',        $apiClass . '::photo_del');
		pixelpost\Event::register('api.photo.set',        $apiClass . '::photo_set');
		pixelpost\Event::register('api.photo.get',        $apiClass . '::photo_get');
		pixelpost\Event::register('api.photo.list',       $apiClass . '::photo_list');
		pixelpost\Event::register('api.photo.count',      $apiClass . '::photo_count');
		pixelpost\Event::register('api.photo.path',       $apiClass . '::photo_path');
		pixelpost\Event::register('api.photo.size',       $apiClass . '::photo_size');
		pixelpost\Event::register('api.photo.config.get', $apiClass . '::config_get');
		pixelpost\Event::register('api.photo.config.set', $apiClass . '::config_set');
		
		pixelpost\Event::register('admin.template.nav',    $adminClass . '::template_nav');
		pixelpost\Event::register('admin.template.widget', $adminClass . '::template_widget');
	}
}

