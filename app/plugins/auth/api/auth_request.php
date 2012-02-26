<?php

namespace pixelpost\plugins\auth;

use pixelpost\Config,
	pixelpost\plugins\api\Exception\FieldRequired,
	pixelpost\plugins\api\Exception\FieldNotValid;

// check the request
if (!isset($event->request->username)) throw new FieldRequired('auth.request', 'username');

// check the username exists
try
{
	$user = Model::user_get_by_name($event->request->username);

	// retrieve configuration
	$conf     = Config::create();
	$lifetime = $conf->plugin_auth->lifetime;
	$key      = $conf->uid;

	// set auth module
	$auth = new Auth();
	$auth->set_lifetime($lifetime)
		 ->set_key($key)
		 ->set_username($event->request->username)
		 ->set_password_hash($user['pass']);

	// create the challenge
	$challenge = $auth->get_challenge();

	// store the challenge in database
	Model::challenge_add($challenge, $user['id'], $lifetime);
}
catch(ModelExceptionNoResult $e)
{
	// netheir tell user don't exists. This is an indication for attacker
	$challenge = md5(uniqid());
	$lifetime  = 300;
}

// return the response
$event->response = compact('challenge', 'lifetime');