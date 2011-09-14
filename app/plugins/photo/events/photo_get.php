<?php

require_once __DIR__ . SEP . 'Model.php';

// check if the request is correct
if (!isset($event->request->id))
{
	throw new ApiException('bad_request', "'api.photo.get' method need a specified 'id' field.");
}

if (!isset($event->request->fields))
{
	throw new ApiException('bad_request', "'api.photo.get' method need a specified 'fields' field.");
}

// exec the request
// we don't catch ModelExceptionSqlError because the api plugin
// allready deal with unknwon exception and send it only in debug mode
// unless it send a classic unknow error
try
{
	// the requested fields
	$fields = $event->request->fields;
	
	$toDo = self::_photo_fecther_generator($fields);

	// retrieve requested fields and send them in the response
	$reply = Model::photo_get($event->request->id, $fields, $toDo);

	// send the reply
	$event->response = $reply;
}
catch(ModelExceptionNoResult $e)
{
	throw new ApiException('no_result', "There is no photo corresponding to the 'id' : {$event->request->id}");
}
