<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!Plugin::is_granted('config')) throw new Exception\Ungranted('auth.config.set');

if (isset($event->request->lifetime))
{
	$lifetime = $event->request->lifetime;

	if (!is_numeric($lifetime))
	{
		throw new Exception\FieldNotValid('lifetime', 'not an integer');
	}

	$conf = pixelpost\Config::create();
	$conf->plugin_auth->lifetime = abs(intval($lifetime));
	$conf->save();
}

$event->response = array('message', 'configuration updated');
