<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

// check if the request is correct
if (!isset($event->request->fields))
{
	throw new ApiException('bad_request', "'api.photo.list' method need a specified 'fields' field.");
}

if (count($event->request->fields) == 0)
{
	throw new ApiException('bad_request', "'api.photo.get' method need a specified at least one 'fields'.");
}

$options = pixelpost\Filter::objectToArray($event->request);

$fields  = $options['fields'];

if (isset($options['pager']))
{
	if (!isset($options['pager']['page']))
	{
		throw new ApiException('bad_format', "'pager' require a field 'page'.");
	}
	if (!isset($options['pager']['max-per-page']))
	{
		throw new ApiException('bad_format', "'pager' require a field 'max-per-page'.");
	}	
}

if (isset($options['filter']) && 
	isset($options['filter']['publish-date-interval']))
{	
	// look at this beautiful reference
	$start =& $options['filter']['publish-date-interval']['start'];
	$end   =& $options['filter']['publish-date-interval']['end'];

	if (pixelpost\Filter::validate_date($start))
	{
		throw new ApiException('bad_format', "'start' need to be a valid RFC3339 date.");			
	}
	if (pixelpost\Filter::validate_date($end))
	{
		throw new ApiException('bad_format', "'start' need to be a valid RFC3339 date.");
	}

	pixelpost\Filter::strToDate($start);
	pixelpost\Filter::strToDate($end);
}

// exec the request
// we don't catch ModelExceptionSqlError because the api plugin
// allready deal with unknwon exception and send it only in debug mode
// unless it send a classic unknow error
try
{
	$toDo = self::_photo_fetcher_generator($fields);

	// retrieve requested fields and send them in the response
	$reply = Model::photo_list($fields, $options, $toDo);

	// send the reply
	$event->response = array('photo' => $reply);
}
catch(ModelExceptionNoResult $e)
{
	$event->response = array();
}
