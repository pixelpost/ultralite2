<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!isset($event->request->token)) throw new Exception\FieldRequired('auth.refresh', 'token');

try
{
	$token = Model::token_get($event->request->token);
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNotValid('token');
}

// retrieve user
$user = Model::user_get_by_id($token['user_id']);

// retrieve configuration
$conf = pixelpost\Config::create();

$auth = new Auth();
$token = $auth->set_lifetime($conf->plugin_auth->lifetime)
			  ->set_domain($event->http_request->get_host())
			  ->set_username($user['name'])
			  ->set_password_hash($user['pass'])
			  ->set_challenge($token['challenge'])
			  ->get_token();

$signature = $auth->get_signature();

// all is good, we store the token
Model::token_add($token, $event->request->challenge, $challenge['user_id']);

$event->response = array('token' => $token, 'signature' => $signature);
