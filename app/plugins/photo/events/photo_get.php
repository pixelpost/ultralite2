<?php

require_once __DIR__ . SEP . 'Model.php';

// check if the request is correct
if (!isset($event->data->id))
{
	throw new ApiException('bad_request', "'api.photo.get' method need a specified 'id' field.");
}

if (!isset($event->data->fields))
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
	$fields = $event->data->fields;

	// some flags needed because photos urls are not stored in database
	$urlThumb    = false;
	$urlResized  = false;
	$urlOriginal = false;
	$isFilename  = false;
	$idPubDate   = false;
	$needUrl     = false;

	// we inspect the request and set our flags
	if (in_array('thumb-url',    $fields)) $urlThumb    = true;
	if (in_array('resized-url',  $fields)) $urlResized  = true;
	if (in_array('original-url', $fields)) $urlOriginal = true;
	if (in_array('publish-date', $fields)) $isPubDate   = true;
	if (in_array('filename',     $fields)) $isFilename  = true;

	// if we need to send an photo url
	$needUrl = $urlThumb || $urlResized || $urlOriginal;

	// if we need to send an url we need to retreive the photo filename
	if (!$isFilename && $needUrl) $fields[] = 'filename';

	// retreive requested fields and send them in the response
	$reply = Model::photo_get($event->data->id, $fields);

	// we terminate the response by adding the specified url
	if ($needUrl)
	{
		$url = function($s) use ($reply) 
		{ 
			return self::_photo_get_image_location($reply['filename'], $size);
		};

		if ($urlThumb)    $reply['thumb-url']    = $url('thumb');
		if ($urlResized)  $reply['resized-url']  = $url('resized');
		if ($urlOriginal) $reply['original-url'] = $url('original');				

		if (!$isFilename) unset($reply['filename']);							
	}
	
	// format the date in RFC3339 if user asked for
	if ($idPubDate)
	{
		$reply['publish-date'] = $reply['publish-date']->format(\DateTime::RFC3339);
	}

	// send the reply
	$event->response = $reply;
}
catch(ModelExceptionNoResult $e)
{
	throw new ApiException('no_result', "There is no photo corresponding to the 'id' : {$event->data->id}");
}
