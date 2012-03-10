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

// retrieve entity public and private key correspondig to the challenge
$entity = Model::entity_get_by_id($challenge['entity_id']);

// retrieve the lifetime configuration value
$lifetime = Config::create()->plugin_auth->lifetime;

// set auth module
$auth = new Auth();
$auth->set_lifetime($lifetime)
	 ->set_public_key($entity['public_key'])
	 ->set_private_key($entity['private_key'])
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
Model::token_add($token,
				 $event->request->challenge,
				 $nonce,
				 $challenge['entity_id'],
				 $challenge['session'],
				 $lifetime);

// return the response
$event->response = compact('nonce', 'signature');
