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
	 * Provide a closure which accepts two arguments: 
	 * - $filename (string) The filename 
	 * - $size     (string) The size format needed (original, resized, thumb)
	 * 
	 * @param  bool     $local generate a photo path or a photo url
	 * @return \Closure 
	 */
	protected static function _photo_location_generator($local = false)
	{
		$conf   = pixelpost\Config::create();
		$sizes  = $conf->photo_plugin;
		 
		$format = ($local)
				? ROOT_PATH . SEP . $conf->photos . SEP . '%s' . SEP . '%s'
				: SHOT_URL . '%s/%s';
		
		return function($filename, $size) use ($sizes, $format)
		{
			switch ($size)
			{
				case 'original' : $size = $sizes->original; break;
				case 'resized'  : $size = $sizes->resized;  break;
				case 'thumb'    : $size = $sizes->thumb;    break;
				default         : $size = $sizes->resized;  break;
			}
			
			return sprintf($format, $size, $filename);
		};
	}
	
	/**
	 * Provide a closure which accepts three arguments:
	 * - $Image (pixelpost\plugins\photo\Image) The file 
	 * - $path  (string) Where to register the new file
	 * - $size  (string) The size format needed (resized, thumb)
	 * 
	 * @return \Closure 
	 */
	protected static function _photo_thumbnail_generator()
	{
		$conf   = pixelpost\Config::create();		
		
		return function(\pixelpost\plugins\photo\Image $image, $path, $size) use ($conf)
		{
			switch($conf->photo_plugin->sizes->$size->type)
			{
				case 'fixed-width' :
					$size   = $conf->photo_plugin->sizes->$size->size;
					return $image->resize_fixed_width($path, $size);
					
				case 'fixed-height' :
					$size   = $conf->photo_plugin->sizes->$size->size;
					return $image->resize_fixed_height($path, $size);
					
				case 'fixed' :
					$width  = $conf->photo_plugin->sizes->$size->width;
					$height = $conf->photo_plugin->sizes->$size->height;
					return $image->resize_fixed($path, $width, $height);
					
				case 'square' :
					$size   = $conf->photo_plugin->sizes->$size->size;
					return $image->resize_square($path, $size);
					
				default : // larger-border
					$size   = $conf->photo_plugin->sizes->$size->size;
					return $image->resize_larger_border($path, $size);
			}			
		};
	}
	
	/**
	 * Provides a closure witch work on SQL row after they are fetcher.
	 * This method is only usefull for photo.get and photo.list method created
	 * by refactoring and performance issue.
	 * 
	 * Becareful the argument $fields is a reference, this is the field array
	 * should be passed to the Model method.
	 * 
	 * @param  array    $fields Becareful this is a reference
	 * @return \Closure
	 */
	protected static function _photo_fetcher_generator(array &$fields)
	{
		// some flags needed because photos urls are not stored in database
		$urlNeeded   = false;
		$urlOriginal = false;
		$urlResized  = false;
		$urlThumb    = false;
		$isFilename  = false;
		$isPubDate   = false;

		// we inspect the request and set our flags
		if (in_array('original-url', $fields)) $urlOriginal = true;
		if (in_array('resized-url',  $fields)) $urlResized  = true;
		if (in_array('thumb-url',    $fields)) $urlThumb    = true;
		if (in_array('filename',     $fields)) $isFilename  = true;
		if (in_array('publish-date', $fields)) $isPubDate   = true;

		// if we need to send an photo url
		$urlNeeded = $urlThumb || $urlResized || $urlOriginal;

		// if we need to send an url we need to retrieve the photo filename
		if (!$isFilename && $urlNeeded) $fields[] = 'filename';
		
		// the url generator if needed
		$urlGen = ($urlNeeded) ? self::_photo_location_generator() : null;
		
		// return a closure that operator on each SQL fetched row
		return function(&$fetchedRow) use ($urlGen, $urlNeeded, $urlThumb, 
										   $urlResized, $urlOriginal, 
										   $isFilename, $isPubDate)
		{
			// we terminate the response by adding the specified url
			if ($urlNeeded)
			{
				if ($urlOriginal) $fetchedRow['original-url'] = $urlGen($fetchedRow['filename'], 'original');				
				if ($urlResized)  $fetchedRow['resized-url']  = $urlGen($fetchedRow['filename'], 'resized');
				if ($urlThumb)    $fetchedRow['thumb-url']    = $urlGen($fetchedRow['filename'], 'thumb');

				if (!$isFilename) unset($fetchedRow['filename']);							
			}

			// format the date in RFC3339 if user asked for
			if ($isPubDate)
			{
				$fetchedRow['publish-date'] = $fetchedRow['publish-date']->format(\DateTime::RFC3339);
			}
		};
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
	 * List photos in database.
	 * 
	 * --------
	 * Request: 
	 * --------
	 * { 
	 *    "fields" : [ "id", "title", "publish-date", "thumb-url" ],
	 *    "pager"  :
	 *    {
	 *        "page"         : 1,
	 *        "max-per-page" : 10
	 *    },
	 *    "sort"   : 
	 *    { 
	 *        "publish-date" : "desc", 
	 *        "title"        : "asc" 
	 *    },
	 *	  "filter" :
	 *    {
	 *	      "publish-date-interval" :
	 *        {
	 *            "start" : "2011-05-01T00:00:00+00:00",
	 *            "end"   : "2011-05-31T23:59:59+00:00"  
	 *        },
	 *        "visible" : true
	 *    }
	 * }
	 * 
	 * The photo fields to be retrieved like: id | filename | title | description | 
	 * publish-date | visible | thumb-url | resized-url | original-url
	 * 
	 * The pager [optional] field with its argument page and max-per-page. Used
	 * to paginate the resultset.
	 * 
	 * The sort [optional] field is an array of key,value pair with value is asc
	 * or desc and key can be: id | filename | title | description | 
	 * publish-date | visible | thumb-url | resized-url | original-url
	 * 
	 * The filter [optional] field can contain three option filter:
	 * - visible: with value true or false. show only visible photo or not.
	 * - publish-date-interval: retrieve photo published between thoses dates. 
	 * contains two mandatory field: start and end are bounds dare in RFC3339
	 * format.
	 * 
	 * ---------
	 * Response: 
	 * ---------
	 * [
	 *	  { 
	 *		  "id"          : 12, 
	 *		  "title"       : "A butterfly", 
	 *		  "publish-date": "2011-05-03T16:38:12+00:00", 
	 *		  "thumb-url"   : "http://something.com/photos/thumb/kGj123.jpeg" 
	 *	  },
	 *	  { 
	 *		  "id"          : 12, 
	 *		  "title"       : "A butterfly", 
	 *		  "publish-date": "2011-05-12T09:12:54+00:00", 
	 *		  "thumb-url"   : "http://something.com/photos/thumb/ACv3hI.jpeg" 
	 *	  },
	 *    {
	 *        ...
	 *    }
	 * ]
	 * 
	 * Same as photo.get in an array
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

