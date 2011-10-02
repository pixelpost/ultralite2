<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiError;
use pixelpost\plugins\auth\Plugin as Auth;

// check grants
if (!Auth::is_granted('read')) throw new ApiError\Ungranted('photo.get');

// check if the request is correct
if (!isset($event->request->id)) throw new ApiError\FieldRequired('photo.get', 'id');

if (!isset($event->request->fields)) throw new ApiError\FieldRequired('photo.get', 'fields');

if (count($event->request->fields) == 0)  throw new ApiError\FieldEmpty('fields');

// exec the request
// we don't catch ModelExceptionSqlError because the api plugin
// allready deal with unknwon exception and send it only in debug mode
// unless it send a classic unknow error
try
{
	// the requested fields
	$fields = $event->request->fields;

	$toDo = self::_photo_fetcher_generator($fields);

	// retrieve requested fields and send them in the response
	$reply = Model::photo_get($event->request->id, $fields, $toDo);

	// send the reply
	$event->response = $reply;
}
catch(ModelExceptionNoResult $e)
{
	throw new ApiError\FieldNonExists('id', $event->request->id);
}
