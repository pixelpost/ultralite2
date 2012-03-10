<?php

namespace pixelpost\plugins\auth;

use pixelpost\Config,
	pixelpost\plugins\api\Exception\FieldRequired,
	pixelpost\plugins\api\Exception\FieldNotValid;

// check the request
if (!isset($event->request->token))     throw new FieldRequired('auth.refresh', 'token');
if (!isset($event->request->signature)) throw new FieldRequired('auth.refresh', 'signature');

// check the token exists
try
{
	$token = Model::token_get($event->request->token);
}
catch(ModelExceptionNoResult $e)
{
	throw new FieldNotValid('token');
}

// retrieve entity public and private key correspondig to the challenge
$entity = Model::entity_get_by_id($challenge['entity_id']);

// retrieve the lifetime configuration value
$lifetime = Config::create()->plugin_auth->lifetime;

// set auth module
$auth = new Auth();
$auth->set_lifetime($lifetime)
	 ->set_public_key($entity['public_key'])
	 ->set_private_key($entity['private_key'])
	 ->set_challenge($token['challenge']);

// generate new token, nonce and check the request signature
$nonce     = $auth->get_nonce();
$new_token = $auth->get_token();
$sign      = $auth->get_signature($event->request->token);
$signature = $auth->get_signature($nonce);

if ($sign != $event->request->signature) throw new FieldNotValid('signature');

// store the token in database
Model::token_update_token($token['id'], $new_token, $lifetime);
Model::token_update_nonce($token['id'], $nonce);

// return the response
$event->response = compact('nonce', 'signature');
