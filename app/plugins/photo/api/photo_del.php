<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiError;
use pixelpost\plugins\auth\Plugin as Auth;

// check grants
if (!Auth::is_granted('delete')) throw new ApiError\Ungranted('photo.del');

// check if the request is correct
if (!isset($event->request->id)) throw new ApiError\FieldRequired('photo.del', 'id');

// exec the request
// we don't catch ModelExceptionSqlError because the api plugin
// allready deal with unknwon exception and send it only in debug mode
// unless it send a classic unknow error
try
{
	// retrieve the photo filemane
	$infos    = Model::photo_get($event->request->id, array('filename'));
	$filename = $infos['filename'];

	// delete the photo in database
	Model::photo_del($event->request->id);

	$pathGenerator = self::_photo_location_generator(true);

	unlink($pathGenerator($filename, 'original'));
	unlink($pathGenerator($filename, 'resized'));
	unlink($pathGenerator($filename, 'thumb'));

	$event->response = array('message' => 'photo deleted');
}
catch(ModelExceptionNoResult $e)
{
	throw new ApiError\FieldNonExists('id', $event->request->id);
}
