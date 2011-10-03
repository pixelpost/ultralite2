<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

/**
 * Provide API methods for managing photo content
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Api
{
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
		$myConf = $conf->plugin_photo;
		 
		$format = ($local)
				? ROOT_PATH . SEP . $myConf->directory . SEP . '%s' . SEP . '%s'
				: $conf->url . $myConf->directory . '/%s/%s';
		
		return function($filename, $size) use ($myConf, $format)
		{
			switch ($size)
			{
				case 'original' : $size = $myConf->original; break;
				case 'resized'  : $size = $myConf->resized;  break;
				case 'thumb'    : $size = $myConf->thumb;    break;
				default         : $size = $myConf->resized;  break;
			}
			
			return sprintf($format, $size, $filename);
		};
	}
	
	/**
	 * Provide a closure which accepts three arguments:
	 * - $image (pixelpost\plugins\photo\Image) The file 
	 * - $path  (string) Where to register the new file
	 * - $size  (string) The size format needed (resized, thumb)
	 * 
	 * @return \Closure 
	 */
	protected static function _photo_thumbnail_generator()
	{
		$conf = pixelpost\Config::create()->plugin_photo->sizes;		
		
		return function(\pixelpost\plugins\photo\Image $image, $path, $size) use ($conf)
		{
			$c = $conf->$size;
			
			switch($c->type)
			{
				default             : return $image->resize_larger_border($path, $c->size);
				case 'fixed-width'  : return $image->resize_fixed_width($path, $c->size);					
				case 'fixed-height' : return $image->resize_fixed_height($path, $c->size);
				case 'fixed'        : return $image->resize_fixed($path, $c->width, $c->height);					
				case 'square'       : return $image->resize_square($path, $c->size);					
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
	
	public static function photo_version(pixelpost\Event $event)
	{
		$event->response = array('version' => Plugin::version());
	}

	public static function photo_add(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'photo_add.php';
	}

	public static function photo_del(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'photo_del.php';
	}

	public static function photo_set(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'photo_set.php';
	}

	public static function photo_get(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'photo_get.php';
	}

	public static function photo_list(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'photo_list.php';
	}

	public static function photo_count(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'photo_count.php';
	}
	
	public static function photo_path(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'photo_path.php';
	}
	
	public static function photo_size(pixelpost\Event $event)
	{
		if (!pixelpost\plugins\auth\Auth::is_granted('read')) 
			throw new pixelpost\plugins\api\Exception\Ungranted('photo.size');
		
		$event->response = pixelpost\Config::create()->plugin_photo->sizes;		
	}
	
	public static function config_get(pixelpost\Event $event)
	{
		if (!pixelpost\plugins\auth\Auth::is_granted('read')) 
			throw new pixelpost\plugins\api\Exception\Ungranted('photo.config.set');
		
		$event->response = pixelpost\Config::create()->plugin_photo;		
	}
	
	public static function config_set(pixelpost\Event $event)
	{
		include __DIR__ . SEP . 'api' . SEP . 'photo_path.php';
	}	
}