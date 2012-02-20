<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!isset($event->request->challenge))	throw new Exception\FieldRequired('auth.token', 'challenge');

if (!isset($event->request->signature))	throw new Exception\FieldRequired('auth.token', 'signature');

// check the challenge in database
try
{
	$challenge = Model::challenge_get($event->request->challenge);

	// instantly delete the used challenge
	Model::challenge_del($challenge['id']);
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNotValid('challenge');
}

$now = new \DateTime();

if ($now > $challenge['expire'])
{
	Model::challenge_del($challenge['id']);

	throw new Exception\FieldNotValid('challenge');
}

// retrieve user and the password correspondig to the challenge
$user = Model::user_get_by_id($challenge['user_id']);

// retrieve configuration
$conf = pixelpost\Config::create();

$auth = new Auth();
$token = $auth->set_lifetime($conf->plugin_auth->lifetime)
			  ->set_domain($event->http_request->get_host())
			  ->set_username($user['name'])
			  ->set_password_hash($user['pass'])
			  ->set_challenge($event->request->challenge)
			  ->get_token();

if ($event->request->signature != $auth->get_signature())
{
	throw new ApiException('auth_fail', "The authentification failed.");
}

// all is good, we store the token
Model::token_add($token, $event->request->challenge, $challenge['user_id']);

$event->response = array('token' => $token, 'signature' => $auth->get_signature());
