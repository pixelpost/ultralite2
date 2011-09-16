<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

// check if the request is correct
if (!isset($event->request->id))
{
	throw new ApiException('bad_request', "'api.photo.set' method need a specified 'id' field.");
}				
if (!isset($event->request->fields))
{
	throw new ApiException('bad_request', "'api.photo.set' method need a specified 'fields' field.");
}

// remove id and filename fields if they are provided
if (isset($event->request->fields->id))       unset($event->request->fields->id);
if (isset($event->request->fields->filename)) unset($event->request->fields->filename);

// change the date, if present, in RFC3339 format to its equivalent object
if (isset($event->request->fields->{'publish-date'}))
{
	$date = $event->request->fields->{'publish-date'};
	
	$date = \DateTime::createFromFormat(\DateTime::RFC3339, $date);
	
	if ($date === false)  unset($event->request->fields->{'publish-date'});
	else                  $event->request->fields->{'publish-date'} = $date;	
}

// retrieve requested fields and send them in the response
$changes = Model::photo_set($event->request->id, (array) $event->request->fields);

if ($changes <= 0)
{
	throw new ApiException('no_result', "There no photo corresponding to the 'id' : {$event->request->id}");			
}

$event->response = array('message' => 'photo updated');
