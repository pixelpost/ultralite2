<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

$request = $event->request;

// check if the request is correct
if (!isset($request->id))
{
	throw new ApiException('bad_request', "'api.photo.set' method need a specified 'id' field.");
}
if (!isset($request->fields))
{
	throw new ApiException('bad_request', "'api.photo.set' method need a specified 'fields' field.");
}

$fields = pixelpost\Filter::objectToArray($request->fields);

// remove id and filename fields if they are provided
if (isset($fields['id']))       unset($fields['id']);
if (isset($fields['filename'])) unset($fields['filename']);

// change the date, if present, in RFC3339 format to its equivalent object
if (isset($fields['publish-date']))
{
	if (pixelpost\Filter::validate_date($fields['publish-date'], \DateTime::RFC3339))
	{
		throw new ApiException('bad_format', "'publish-date' need to be a valid RFC3339 date.");
	}
	
	pixelpost\Filter::strToDate($fields['publish-date']);
}

// retrieve requested fields and send them in the response
$changes = Model::photo_set($request->id, $fields);

if ($changes <= 0)
{
	throw new ApiException('no_result', "There no photo corresponding to the 'id' : {$request->id}");
}

$event->response = array('message' => 'photo updated');
