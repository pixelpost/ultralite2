<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

require_once dirname(__DIR__) . SEP . 'Model.php';
require_once dirname(__DIR__) . SEP . 'Image.php';

// check if the request is correct
if (!isset($event->request->file))
{
	throw new ApiException('bad_request', "'api.photo.add' method need a specified 'file' field.");
}

if (!file_exists($event->request->file))
{
	throw new ApiException('bad_data', "the specified 'file' not exists.");
}

try
{
	// the temp image file (uploaded)
	$filename = $event->request->file;

	// create a uniq image filename width jpeg ext
	$uid      = md5($filename . date("YmdHis") . rand(0, 200)) . '.jpg';

	// generate the location of the three image format : original, resized, thumb
	$pathGenerator  = self::_photo_location_generator(true);
	// generate the resized and thumb file
	$thumbGenerator = self::_photo_thumbnail_generator();

	$original = $pathGenerator($uid, 'original');
	$resized  = $pathGenerator($uid, 'resized');
	$thumb    = $pathGenerator($uid, 'thumb');

	// load the temp image (uploaded) in GD2
	$image = new Image($filename, pixelpost\Config::create()->photo_plugin->quality);
	
	// store the original size in jpg to it's final path
	if (!$image->convert_to_jpeg($original))
	{
		unlink($filename);
		throw new ApiException('internal_error', "can't generate original image.");		
	}

	// store the resized size in jpg to it's final path in regards of user conf
	if (!$thumbGenerator($image, $resized, 'resized'))
	{
		unlink($filename);
		unlink($original);
		throw new ApiException('internal_error', "can't generate resized image.");
	}

	// store the thumb size in jpg to it's final path in regards of user conf
	if (!$thumbGenerator($image, $thumb, 'thumb'))
	{
		unlink($filename);
		unlink($original);
		unlink($resized);
		throw new ApiException('internal_error', "can't generate thumb image.");
	}

	try
	{
		// store the Image in database and put the photo id in the event response
		$event->response = array('id' => Model::photo_add($uid));
		// delete the uploaded file
		unlink($filename);
	}
	catch(ModelExceptionSqlError $e)
	{
		unlink($filename);
		unlink($original);
		unlink($resized);
		unlink($thumb);
		throw $e;
	}
}
catch(\Exception $e)
{
	throw $e;
	throw new ApiException('internal_error', "can't work on the image.", $e);			
}
