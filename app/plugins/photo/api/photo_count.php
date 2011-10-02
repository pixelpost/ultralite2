<?php

namespace pixelpost\plugins\photo;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiError;
use pixelpost\plugins\auth\Plugin as Auth;

// check grants
if (!Auth::is_granted('read')) throw new ApiError\Ungranted('photo.count');

// check if the request is correct
$options = pixelpost\Filter::object_to_array($event->request);

if (isset($options['filter']) && 
	isset($options['filter']['publish-date-interval']))
{	
	// look at this beautiful reference
	$start =& $options['filter']['publish-date-interval']['start'];
	$end   =& $options['filter']['publish-date-interval']['end'];

	if (pixelpost\Filter::validate_date($start))
	{
		throw new ApiError\FieldNotValid('start', 'required RFC3339 date');
	}
	if (pixelpost\Filter::validate_date($end))
	{
		throw new ApiError\FieldNotValid('end', 'required RFC3339 date');
	}

	pixelpost\Filter::str_to_date($start);
	pixelpost\Filter::str_to_date($end);
}

// retrieve requested fields and send them in the response
$reply = Model::photo_count($options);

// send the reply
$event->response = array('total' => $reply);
