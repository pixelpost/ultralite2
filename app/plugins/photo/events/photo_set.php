<?php

require_once __DIR__ . SEP . 'Model.php';

// check if the request is correct
if (!isset($event->data->id))
{
	throw new ApiException('bad_request', "'api.photo.set' method need a specified 'id' field.");
}				
if (!isset($event->data->fields))
{
	throw new ApiException('bad_request', "'api.photo.set' method need a specified 'fields' field.");
}

// remove id and filename fields if they are provided
if (isset($event->data->fields['id']))       unset($event->data->fields['id']);
if (isset($event->data->fields['filename'])) unset($event->data->fields['filename']);

// change the date, if present, in RFC3339 format to its equivalent object
if (isset($event->data->fields['publish-date']))
{
	$date = $event->data->fields['publish-date'];
	
	$date = \DateTime::createFromFormat(\DateTime::RFC3339, $date);
	
	if ($date === false)  unset($event->data->fields['publish-date']);
	else                  $event->data->fields['publish-date'] = $date;	
}

// retrieve requested fields and send them in the response
$changes = Model::photo_set($event->data->id, $event->data->fields);

if ($changes <= 0)
{
	throw new ApiException('no_result', "There no photo corresponding to the 'id' : {$event->data->id}");			
}

$event->response = array('message' => 'photo updated');
