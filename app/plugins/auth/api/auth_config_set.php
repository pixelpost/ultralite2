<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Config,
	pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNotValid;

if (!Plugin::is_granted('config')) throw new Ungranted('auth.config.set');

if (isset($event->request->lifetime))
{
	$lifetime = $event->request->lifetime;
	$lifetime = filter_var($lifetime, FILTER_VALIDATE_INT, array('min_range' => 10));

	if ($lifetime === false)
	{
		throw new FieldNotValid('lifetime', 'should be a positive integer upper than 10');
	}

	$conf = Config::create();
	$conf->plugin_auth->lifetime = $lifetime;
	$conf->save();
}

$event->response = array('message' => 'configuration updated');
