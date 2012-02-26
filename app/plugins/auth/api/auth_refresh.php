<?php

namespace pixelpost\plugins\auth;

use pixelpost\Config,
	pixelpost\plugins\api\Exception\FieldRequired,
	pixelpost\plugins\api\Exception\FieldNotValid;

// check the request
if (!isset($event->request->token)) throw new FieldRequired('auth.refresh', 'token');

// check the token exists
try
{
	$token = Model::token_get($event->request->token);
}
catch(ModelExceptionNoResult $e)
{
	throw new FieldNotValid('token');
}

// retrieve user and the password correspondig to the token
$user = Model::user_get_by_id($token['user_id']);

// retrieve configuration
$conf     = Config::create();
$lifetime = $conf->plugin_auth->lifetime;
$key      = $conf->uid;

// set auth module
$auth = new Auth();
$auth->set_lifetime($lifetime)
	 ->set_key($key)
	 ->set_username($user['name'])
	 ->set_password_hash($user['pass'])
	 ->set_challenge($token['challenge']);

// generate new token, nonce and check the request signature
$nonce     = $auth->get_nonce();
$new_token = $auth->get_token();
$sign      = $auth->get_signature($event->request->token);
$signature = $auth->get_signature($nonce);

if ($sign != $event->request->signature)
{
	throw new FieldNotValid('signature');
}

// store the token in database
Model::token_update_token($token['id'], $new_token);
Model::token_update_nonce($token['id'], $nonce);

// return the response
$event->response = compact('nonce', 'signature');
