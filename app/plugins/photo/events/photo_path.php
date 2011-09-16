<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

// check if the request is correct
if (!isset($event->request->id))
{
	throw new ApiException('bad_request', "'api.photo.path' method need a specified 'id' field.");
}

if (!isset($event->request->size))
{
	throw new ApiException('bad_request', "'api.photo.path' method need a specified 'size' field.");
}

$id   = $event->request->id;
$size = $event->request->size;

pixelpost\Filter::assume_int($id);

switch($size)
{
	case 'original': break;
	case 'resized' : break;
	case 'thumb'   : break;
	default        : $size = 'original'; break;
}

/***** how to properly call an event... *****/

// prepare your request data
$request = array('id' => $id, 'fields' => array('filename'));
$request = array('request' => pixelpost\Filter::arrayToObject($request));

// make a try..catch
try
{
	// make the call
	$call = pixelpost\Event::signal('api.photo.get', $request);

	// check if the call is processed
	if (!$call->is_processed())
	{
		throw new \Exception('event `api.photo.get` is not processed');
	}
	// check if the response exists
	if (!isset($call->response))
	{
		throw new \Exception('event `api.photo.get` not provide a response');
	}
}
// handle all pixelpost\plugins\api\Exception can be thrown
// if you don't the user receive the error message of your internal call
catch(ApiException $e)
{
	throw new \Exception('event `api.photo.get` thrown an exception', 0, $e);
}

/***** your done... *****/

// retrieve the photoId filename
$filename = $call->response['filename'];

$pathGenerator = self::_photo_location_generator(true);

// send in response the path of the photo
$event->response = array('path' => $pathGenerator($filename, $size));
