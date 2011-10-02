<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiError;
use pixelpost\plugins\auth\Plugin as Auth;

// check grants
if (!Auth::is_granted('config')) throw new ApiError\Ungranted('photo.config.set');

$conf = pixelpost\Config::create();

$myConf = $conf->plugin_photo;

$newConf  = pixelpost\Filter::object_to_array($event->request);
$newConf += pixelpost\Filter::object_to_array($myConf);

// change a directory
$checkDir = function($name, $base) use ($myConf, $newConf, &$conf)
{
	if ($newConf[$name] == $myConf->$name) return;
	
	if ($base != '') $base .= SEP;

	$oldPath = ROOT_PATH . SEP . $base . $myConf->$name;
	$newPath = ROOT_PATH . SEP . $base . $newConf[$name];

	if (file_exists($newPath))
	{
		throw new ApiError\FieldNotValid($name, "dir name '$newPath' already exists");
	}

	if (rename($oldPath, $newPath)) $conf->plugin_photo->$name = $newConf[$name];
};

$checkNumber = function($number, $min, $max, $message)
{
	if (!is_numeric($number))  throw new ApiError\FieldOutBounds($name, $min, $max);

	$int = abs(intval($number));
	
	if ($int < $min || $int > $max)  throw new ApiError\FieldOutBounds($name, $min, $max);
	
	return $int;
};

$checkSize = function($name) use ($myConf, $newConf, &$conf, $checkNumber)
{
	$change = false; // need to resize all photo ?

	if ($newConf['sizes'][$name]['type'] == $myConf->sizes->$name->type)
	{
		$change = true;

		$options = array('larger-border', 'fixed-width', 'fixed-height', 'fixed', 'sqare');
				
		switch($newConf['sizes'][$name]['type'])
		{
			case 'larger-border': break;
			case 'fixed-width'  : break;
			case 'fixed-height' : break;
			case 'fixed'        : break;
			case 'square'       : break;
			default: 
				throw new ApiError\FieldNotInList('type', $options);
		}

		$conf->plugin_photo->sizes->$name->type = $newConf['sizes'][$name]['type'];				
	}
		

	if ($newConf['sizes'][$name]['type'] == 'fixed')
	{
		$width  = $newConf['sizes'][$name]['width'];
		$height = $newConf['sizes'][$name]['height'];

		$width  = $checkNumber($width,  10, 2000, 'width');
		$height = $checkNumber($height, 10, 2000, 'height');

		// $change == false => $myConf->..->width/height exists before
		if (!$change && $width  != $myConf->sizes->$name->width)  $change = true;
		if (!$change && $height != $myConf->sizes->$name->height) $change = true;
		
		$conf->plugin_photo->sizes->$name->width  = $width;
		$conf->plugin_photo->sizes->$name->height = $height;				
	}
	else
	{		
		$size = $newConf['sizes'][$name]['size'];
		
		$size = $checkNumber($size, 10, 2000, 'size');
		
		if (!$change && $size != $myConf->sizes->$name->size) $change = true;
		
		$conf->plugin_photo->sizes->$name->size  = $size;
	}
	
	return $change;
};

try
{
	$checkDir('original',  $myConf->directory);
	$checkDir('resized',   $myConf->directory);
	$checkDir('thumb',     $myConf->directory);
	$checkDir('directory', '');	

	$change = false;
	$change = $change || $checkSize('resized');
	$change = $change || $checkSize('thumb');

	$conf->plugin_photo->quality = $checkNumber($newConf['quality'], 40, 100, 'quality');

	if ($change)
	{
		// TODO bach the rezising of all existing photo (or not) ?
	}
	
	$conf->save();
	
	$event->response = array('message' => 'configuration updated');
}
catch(ApiException $e)
{
	$conf->save();
	throw $e;
}
