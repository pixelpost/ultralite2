<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

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
 * - 'api.photo.path'
 * - 'api.photo.size'
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin implements pixelpost\PluginInterface
{

	public static function version()
	{
		return '0.0.1';
	}

	public static function install()
	{
		require_once __DIR__ . SEP . 'Model.php';
		
		$configuration = '{
			"original" : "original",
			"resized"  : "resized",
			"thumb"    : "thumb",
			"quality"  : 90,
			"sizes"    : {
				"resized" : {
					"type" : "large-border",
					"size" : 500
				},
				"thumb" : {
					"type" : "square",
					"size" : 150
				}
			}
		}';
		
		$conf = pixelpost\Config::create();		
		$conf->photo_plugin = json_decode($configuration, true);		
		$conf->save();

		Model::table_create();

		$path = ROOT_PATH . SEP . $conf->photos;
		
		mkdir($path                   , 0775);
		mkdir($path . SEP . 'original', 0775);
		mkdir($path . SEP . 'resized' , 0775);
		mkdir($path . SEP . 'thumb'   , 0775);
	}

	public static function uninstall()
	{	
		require_once __DIR__ . SEP . 'Model.php';

		$conf = pixelpost\Config::create();
		
		unset($conf['photo_plugin']);
		
		$conf->save();

		Model::table_delete();
		
		foreach(new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator(ROOT_PATH . SEP . $conf->photos), 
					\RecursiveIteratorIterator::CHILD_FIRST) as $file)
		{
			$method = $file->isDir() ? "rmdir" : 'unlink';			
			$medhod($file->getPathName());
		}
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		pixelpost\Event::register('api.photo.version', '\\' . __CLASS__ . '::photo_version');
		pixelpost\Event::register('api.photo.add',     '\\' . __CLASS__ . '::photo_add');
		pixelpost\Event::register('api.photo.del',     '\\' . __CLASS__ . '::photo_del');
		pixelpost\Event::register('api.photo.set',     '\\' . __CLASS__ . '::photo_set');
		pixelpost\Event::register('api.photo.get',     '\\' . __CLASS__ . '::photo_get');
		pixelpost\Event::register('api.photo.path',    '\\' . __CLASS__ . '::photo_path');
		pixelpost\Event::register('api.photo.size',    '\\' . __CLASS__ . '::photo_size');
		// TODO To be created:
		//pixelpost\Event::register('api.photo.list',    '\\' . __CLASS__ . '::photo_list');
		//pixelpost\Event::register('api.photo.config.get', '\\' . __CLASS__ . '::photo_size');
		//pixelpost\Event::register('api.photo.config.set', '\\' . __CLASS__ . '::photo_size');
	}

	/**
	 * Provide the photo web or local URI 
	 * 
	 * @param  string $filename
	 * @param  string $size
	 * @param  bool   $local
	 * @return string 
	 */
	protected static function _photo_get_image_location($filename, $size, $local = false)
	{
		$conf = pixelpost\Config::create();

		switch ($size)
		{
			case 'original' : $size = $conf->photo_plugin->original; break;
			case 'resized'  : $size = $conf->photo_plugin->resized;  break;
			case 'thumb'    : $size = $conf->photo_plugin->thumb;    break;
			default         : $size = $conf->photo_plugin->resized;  break;
		}
		
		$size = $conf->photo_plugin->$size;
		
		if ($local)
		{
			return ROOT_PATH . SEP . $conf->photos . SEP . $size . SEP . $filename; 
		}
		else
		{
			return SHOT_URL . $size . '/' . $filename;
		}
	}
	
	/**
	 * Return the actual version number of the plugin
	 * 
	 * --------
	 * Request: { }
	 * --------
	 * 
	 * No data need to be provided
	 * 
	 * ---------
	 * Response: { "version" : "0.0.1" }
	 * ---------
	 * 
	 * The string repesentating the version number of the plugin
	 * 
	 * @param pixelpost\Event $event
	 */
	public static function photo_version(pixelpost\Event $event)
	{
		$event->response = array('version' => self::version());
	}

	/**
	 * Add a photo in database
	 *
	 * --------
	 * Request: { "file": "\tmp\tempnam-ulkdfjg.jpg" }
	 * --------
	 * 
	 * The photo file, this file will be moved in his final folder.
	 * 
	 * ---------
	 * Response: { 'id' : 1234 }
	 * ---------
	 * 
	 * The photo id created.
	 * 
	 * @param pixelpost\Event $event
	 */
	public static function photo_add(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'photo_add.php';
	}

	/**
	 * Delete a photo from database
	 *
	 * --------
	 * Request: { "id": 1234 }
	 * --------
	 * 
	 * The photo id to be deleted.
	 * 
	 * ---------
	 * Response: { "message" : "Photo is deleted" }
	 * ---------
	 * 
	 * Confirmation Message.
	 * 
	 * @param pixelpost\Event $event
	 */
	public static function photo_del(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'photo_del.php';
	}

	/**
	 * Change a photo in database, the possible infos are:
	 *
	 * --------
	 * Request:
	 * --------
	 * { 
	 *    "id"     : 1234, 
	 *    "fields" : { "title": "my photo", "visible": true } 
	 * }
	 * 
	 * The photo id to be updated.
	 * The photo fields to be updated like: title | description | publish-date | visible
	 * 
	 * filename:     string
	 * title:        string
	 * description:  string
	 * publish-date: date string formated like RFC3339
	 * visible:      boolean
	 * 
	 * ---------
	 * Response: { "message" : "Photo is updated" }
	 * ---------
	 * 
	 * Confirmation Message.
	 * 
	 * @param pixelpost\Event $event
	 */
	public static function photo_set(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'photo_set.php';
	}

	/**
	 * Retrieve photo infos from database, the possible infos are: 
	 * 
	 * --------
	 * Request: 
	 * --------
	 * { 
	 *	  "id"     : 1234, 
	 *    "fields" : [ "title", "description", "thumb-url" ] 
	 * }
	 * 
	 * The photo id.
	 * The photo fields to be retrieved like: id | filename | title | description | 
	 * publish-date | visible | thumb-url | resized-url | original-url
	 * 
	 * ---------
	 * Response: 
	 * ---------
	 * { 
	 *	  "title":       "My Photo", 
	 *    "description": "My description", 
	 *    "thumb-url":   "http://something.com/photos/thumb/kGj123elakjz32.jpeg" 
	 * }
	 * 
	 * The data which are asked for:
	 * 
	 * id:           int
	 * filename:     string
	 * title:        string
	 * description:  string
	 * publish-date: date string formated like RFC3339
	 * visible:      boolean
	 * thumb-url:    url
	 * resized-url:  url
	 * original-url: url
	 * 
	 * @param pixelpost\Event $event
	 */
	public static function photo_get(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'photo_get.php';
	}

	/**
	 * List photos in database
	 * 
	 * @param pixelpost\Event $event
	 */
	public static function photo_list(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'photo_list.php';
	}
	
	/**
	 * Return a photo path
	 * 
	 * --------
	 * Request: { "id": 1234, "size": "thumb" }
	 * --------
	 * 
	 * Possible sizes are: original | resized | thumb
	 * 
	 * ---------
	 * Response: { "path" : "/var/www/photoblog/photos/thumb/AHkx3Fgke23.jpg" }
	 * ---------
	 * 
	 * Where located the photo on the local storage
	 * 
	 * @param pixelpost\Event $event 
	 */
	public static function photo_path(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'events' . SEP . 'photo_path.php';
	}
	
	/**
	 * Return the size of the photo
	 * 
	 * --------
	 * Request: { }
	 * --------
	 * 
	 * No data needed
	 * 
	 * ---------
	 * Response:
	 * ---------
	 * {
	 *     "resized" : { "type": "fixed", "width": 600, "height": 200 },
	 *     "thumb"   : { "type": "square", "size": 150 }
	 * }
	 * 
	 * The photo size and there format. Possible `type`: larger-border | 
	 * fixed-width | fixed-height | fixed | square
	 * Only fixed type provide `witdh` and `height` data, others provide 
	 * size data.
	 * 
	 * @param pixelpost\Event $event 
	 */
	public static function photo_sizes(pixelpost\Event $event)
	{
		$event->response = pixelpost\Db::create()->photo_plugin->sizes;		
	}

}

