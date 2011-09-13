<?php

require_once __DIR__ . SEP . 'Model.php';
require_once __DIR__ . SEP . 'Image.php';

// check if the request is correct
if (!isset($event->request->file))
{
	throw new ApiException('bad_request', "'api.photo.add' method need a specified 'file' field.");
}

$filename = $event->request->file;

$uid      = md5($filename . date() . time() . rand(0, 200));

$original = self::_photo_get_image_location($uid, 'original');
$resized  = self::_photo_get_image_location($uid, 'resized');
$thumb    = self::_photo_get_image_location($uid, 'thumb');

if (!file_exists($filename))
{
	throw new ApiException('bad_data', "the specified 'file' not exists.");
}

try
{
	if (!rename($filename, $original))
	{
		throw new ApiException('internal_error', "can't move file image in final directory.");		
	}
	
	$image = new Image($original, $conf->photo_plugin->quality);

	$result = false;

	switch($conf->photo_plugin->sizes->resized->type)
	{
		case 'larger-border':
			$size   = $conf->photo_plugin->sizes->resized->size;
			$result = $image->resize_larger_border($resized, $size);
			break;
		case 'fixed-width'  :
			$size   = $conf->photo_plugin->sizes->resized->size;
			$result = $image->resize_fixed_width($resized, $size);
			break;
		case 'fixed-height' :
			$size   = $conf->photo_plugin->sizes->resized->size;
			$result = $image->resize_fixed_height($resized, $size);
			break;
		case 'fixed'        :
			$width  = $conf->photo_plugin->sizes->resized->width;
			$height = $conf->photo_plugin->sizes->resized->height;
			$result = $image->resize_fixed($resized, $width, $height);
			break;
		case 'square'       :
			$size   = $conf->photo_plugin->sizes->resized->size;
			$result = $image->resize_square($resized, $size);
			break;
	}

	if (!$result)
	{
		unlink($original);
		throw new ApiException('internal_error', "can't generate resized image.");
	}

	$result = false;

	switch($conf->photo_plugin->sizes->thumb->type)
	{
		case 'larger-border':
			$size   = $conf->photo_plugin->sizes->thumb->size;
			$result = $image->resize_larger_border($thumb, $size);
			break;
		case 'fixed-width'  :
			$size   = $conf->photo_plugin->sizes->thumb->size;
			$result = $image->resize_fixed_width($thumb, $size);
			break;
		case 'fixed-height' :
			$size   = $conf->photo_plugin->sizes->thumb->size;
			$result = $image->resize_fixed_height($thumb, $size);
			break;
		case 'fixed'        :
			$width  = $conf->photo_plugin->sizes->thumb->width;
			$height = $conf->photo_plugin->sizes->thumb->height;
			$result = $image->resize_fixed($thumb, $width, $height);
			break;
		case 'square'       :
			$size   = $conf->photo_plugin->sizes->thumb->size;
			$result = $image->resize_square($thumb, $size);
			break;
	}

	if (!$result)
	{
		unlink($original);
		unlink($resized);
		throw new ApiException('internal_error', "can't generate resized image.");
	}

	try
	{
		$event->response = array('id' => Model::photo_add($original));
	}
	catch(ModelExceptionSqlError $e)
	{
		unlink($original);
		unlink($resized);
		unlink($thumb);
		throw $e;
	}
}
catch(\Exception $e)
{
	throw new ApiException('internal_error', "can't work on the image.", $e);			
}
