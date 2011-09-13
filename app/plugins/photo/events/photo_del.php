<?php

require_once __DIR__ . SEP . 'Model.php';

// check if the request is correct
if (!isset($event->request->id))
{
	throw new ApiException('bad_request', "'api.photo.del' method need a 
		specified 'id' field.");
}				

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

	unlink(self::_photo_get_image_location($filename, 'original', true));
	unlink(self::_photo_get_image_location($filename, 'resized',  true));
	unlink(self::_photo_get_image_location($filename, 'thumb',    true));

	$event->response = array('message' => 'photo deleted');
}
catch(ModelExceptionNoResult $e)
{
	throw new ApiException('no_result', "There no photo corresponding to the 'id' : {$event->request->id}");
}		
