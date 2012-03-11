<?php

namespace pixelpost\plugins\photo;

use pixelpost\core\Filter,
	pixelpost\plugins\api\Exception as ApiError,
	pixelpost\plugins\auth\Plugin as Auth;

// check grants
if (!Auth::is_granted('read')) throw new ApiError\Ungranted('photo.list');

// check if the request is correct
if (!isset($event->request->fields)) throw new ApiError\FieldRequired('photo.list', 'fields');

if (count($event->request->fields) == 0) throw new ApiError\FieldEmpty('fields');

$options = Filter::object_to_array($event->request);

$fields  = $options['fields'];

if (isset($options['pager']))
{
	if (!isset($options['pager']['page']))
	{
		throw new ApiError\FieldRequired('photo.list', 'pager::page');
	}
	if (!isset($options['pager']['max-per-page']))
	{
		throw new ApiError\FieldRequired('photo.list', 'pager::max-per-page');
	}
}

if (isset($options['filter']) &&
	isset($options['filter']['publish-date-interval']))
{
	// look at this beautiful reference
	$start =& $options['filter']['publish-date-interval']['start'];
	$end   =& $options['filter']['publish-date-interval']['end'];

	if (Filter::validate_date($start))
	{
		throw new ApiError\FieldNotValid('start', 'invalid RFC3339 date');
	}
	if (Filter::validate_date($end))
	{
		throw new ApiError\FieldNotValid('start', 'invalid RFC3339 date');
	}

	Filter::str_to_date($start);
	Filter::str_to_date($end);
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
