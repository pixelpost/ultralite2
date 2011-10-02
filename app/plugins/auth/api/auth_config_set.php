<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

if (!Plugin::is_granted('config'))
{
	throw new ApiException('unauthorized', 'You have not the rights necessary to call this method.');
}

if (isset($event->request->lifetime))
{
	$lifetime = $event->request->lifetime;

	if (!is_numeric($lifetime))
	{
		throw new ApiException('bad_lifetime', "The 'lifetime' need to be a integer.");
	}

	$conf = pixelpost\Config::create();
	$conf->plugin_auth->lifetime = abs(intval($lifetime));
	$conf->save();
}

$event->response = array('message', 'configuration updated');
