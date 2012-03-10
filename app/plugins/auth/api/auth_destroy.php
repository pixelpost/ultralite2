<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\Internal;

if (!Plugin::is_auth()) throw new Ungranted('auth.destroy');

if (Plugin::is_auth_admin()) throw new Internal('No API auth, this is a WebAuth');

// remove entity's token in database
try
{
	Model::token_del(Plugin::get_token_id());
}
catch (ModelExceptionNoResult $e) {}

$event->response = array('message' => 'disconnected');
