<?php

namespace pixelpost\plugins\auth;

use DateTime,
	pixelpost\Config,
	pixelpost\plugins\api\Exception\FieldRequired,
	pixelpost\plugins\api\Exception\FieldNotValid;

// check the request
if (!isset($event->request->challenge))	throw new FieldRequired('auth.token', 'challenge');
if (!isset($event->request->signature))	throw new FieldRequired('auth.token', 'signature');

// check the challenge exists
try
{
	$challenge = Model::challenge_get($event->request->challenge);
}
catch(ModelExceptionNoResult $e)
{
	throw new FieldNotValid('challenge');
}

// check the challenge not expire
$now = new DateTime();

if ($now > $challenge['expire'])
{
	Model::challenge_del($challenge['id']);

	throw new FieldNotValid('challenge');
}

// retrieve user and the password correspondig to the challenge
$user = Model::user_get_by_id($challenge['user_id']);

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
	 ->set_challenge($event->request->challenge);

// generate new token, nonce and check the request signature
$nonce     = $auth->get_nonce();
$token     = $auth->get_token();
$sign      = $auth->get_signature($event->request->challenge);
$signature = $auth->get_signature($nonce);

if ($sign != $event->request->signature)
{
	throw new FieldNotValid('signature');
}

// delete the challenge, no one can try to auth himself with it now
Model::challenge_del($challenge['id']);

// store the token in database
Model::token_add($token, $event->request->challenge, $nonce, $challenge['user_id']);

// return the response
$event->response = compact('nonce', 'signature');
