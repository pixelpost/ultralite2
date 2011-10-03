<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiError;
use pixelpost\plugins\auth\Plugin as Auth;

if (!Auth::is_granted('write')) throw new ApiError\Ungranted('photo.set');

$request = $event->request;

// check if the request is correct
if (!isset($request->id)) throw new ApiError\FieldRequired('photo.set', 'id');

if (!isset($request->fields)) throw new ApiError\FieldRequired('photo.set', 'fields');

$fields = pixelpost\Filter::object_to_array($request->fields);

// remove id and filename fields if they are provided
if (isset($fields['id']))       unset($fields['id']);
if (isset($fields['filename'])) unset($fields['filename']);

// change the date, if present, in RFC3339 format to its equivalent object
if (isset($fields['publish-date']))
{
	if (pixelpost\Filter::validate_date($fields['publish-date'], \DateTime::RFC3339))
	{
		throw new ApiError\FieldNotValid('publish-date', 'invalid RFC3339 date');
	}
	
	pixelpost\Filter::str_to_date($fields['publish-date']);
}

// retrieve requested fields and send them in the response
$changes = Model::photo_set($request->id, $fields);

if ($changes <= 0)
{
	throw new ApiError\FieldNonExists('id', $request->id);
}

$event->response = array('message' => 'photo updated');
