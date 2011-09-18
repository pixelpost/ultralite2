<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

if (!isset($event->request->username))
{
	throw new ApiException('bad_request', "'auth.request' need a 'username' field.");
}

// check the user in database
try
{
	$user = Model::user_get_by_name($event->request->username);			
}
catch(ModelExceptionNoResult $e)
{
	throw new ApiException('bad_username', "Requested authentification failed.");			
}

// retrieve configuration
$conf = pixelpost\Config::create();

// create the challenge
$auth = new Auth();
$challenge = $auth->set_lifetime($conf->plugin_auth->lifetime)
				  ->set_domain($event->http_request->get_host())
				  ->set_username($event->request->username)
				  ->set_password_hash($user['pass'])
				  ->get_challenge();

// store it in database
Model::challenge_add($challenge, $user['id'], $conf->plugin_auth->lifetime);

$event->response = array('challenge' => $challenge, 'lifetime' => $lifetime);		
