<?php

namespace pixelpost\plugins\photo;

use pixelpost\core\Filter,
	pixelpost\plugins\api\Plugin as Api,
	pixelpost\plugins\api\Exception as ApiError,
	pixelpost\plugins\auth\Plugin as Auth;

// check grants
if (!Auth::is_granted('read')) throw new ApiError\Ungranted('photo.list');

// check if the request is correct
if (!isset($event->request->id)) throw new ApiError\FieldRequired('photo.path', 'id');

if (!isset($event->request->size)) throw new ApiError\FieldRequired('photo.path', 'size');

$id   = $event->request->id;
$size = $event->request->size;

Filter::assume_int($id);

$options = array('original', 'resized', 'thumb');

switch($size)
{
	case 'original': break;
	case 'resized' : break;
	case 'thumb'   : break;
	default        : throw new ApiError\FieldNotInList('photo.path', $options);
}

// prepare your request data
$request = array('id' => $id, 'fields' => array('filename'));

$response = Api::call($request);

// retrieve the photoId filename
$filename = $response['filename'];

// get the path generator
$pathGenerator = self::_photo_location_generator(true);

// send in response the path of the photo
$event->response = array('path' => $pathGenerator($filename, $size));
