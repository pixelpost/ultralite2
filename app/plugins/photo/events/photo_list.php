<?php

// TODO Need to be completed

// check if the request is correct
if (!isset($event->request->fields))
{
	throw new ApiException('bad_request', "'api.photo.list' method need a specified 'fields' field.");
}

$options = array();

if (isset($event->request->pager))  $options['pager']  = $event->request->pager;
if (isset($event->request->sort))   $options['sort']   = $event->request->sort;
if (isset($event->request->filter)) 
{
	$options['filter'] = $event->request->filter;
		
	if (isset($options['filter']['publish-date-interval']))
	{
		$options['filter']['publish-date-interval']['start'] = 
			new \DateTime($options['filter']['publish-date-interval']['start']);
		
		$options['filter']['publish-date-interval']['end']   = 
			new \DateTime($options['filter']['publish-date-interval']['end']);
	}	
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
	$reply = Model::photo_list($event->request->id, $fields, $options, $toDo);

	// send the reply
	$event->response = $reply;
}
catch(ModelExceptionNoResult $e)
{
	$event->response = array();
}
