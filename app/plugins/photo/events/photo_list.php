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

$options = array();

// I do not very love this part
// this need  a more beautiful construction
if (isset($event->request->pager))  $options['pager']  = (array) $event->request->pager;
if (isset($event->request->sort))   $options['sort']   = (array) $event->request->sort;
if (isset($event->request->filter))
{
	$options['filter'] = (array) $event->request->filter;

	if (isset($options['filter']['publish-date-interval']))
	{
		$options['filter']['publish-date-interval'] = (array) $options['filter']['publish-date-interval'];

		$options['filter']['publish-date-interval']['start'] =
			new \DateTime($options['filter']['publish-date-interval']['start']);

		$options['filter']['publish-date-interval']['end']   =
			new \DateTime($options['filter']['publish-date-interval']['end']);
	}
}
// end of hate part

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
	$reply = Model::photo_list($fields, $options, $toDo);

	// send the reply
	$event->response = array('photo' => $reply);
}
catch(ModelExceptionNoResult $e)
{
	$event->response = array();
}
