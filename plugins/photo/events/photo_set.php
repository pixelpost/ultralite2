<?php

require_once __DIR__ . SEP . 'Model.php';

// check if the request is correct
if (!isset($event->datas->id))
{
	throw new ApiException('bad_request', "'api.photo.set' method need a specified 'id' field.");
}				
if (!isset($event->datas->fields))
{
	throw new ApiException('bad_request', "'api.photo.set' method need a specified 'fields' field.");
}

// remove id and filename fields if they are provided
if (isset($event->datas->fields['id']))       unset($event->datas->fields['id']);
if (isset($event->datas->fields['filename'])) unset($event->datas->fields['filename']);

// change the date, if present, in RFC3339 format to its equivalent object
if (isset($event->datas->fields['publish-date']))
{
	$date = $event->datas->fields['publish-date'];
	
	$date = \DateTime::createFromFormat(\DateTime::RFC3339, $date);
	
	if ($date === false)  unset($event->datas->fields['publish-date']);
	else                  $event->datas->fields['publish-date'] = $date;	
}

// retreive requested fields and send them in the response
$changes = Model::photo_set($event->datas->id, $event->datas->fields);

if ($changes <= 0)
{
	throw new ApiException('no_result', "There no photo corresponding to the 'id' : {$event->datas->id}");			
}

$event->response = 'Photo is updated';
