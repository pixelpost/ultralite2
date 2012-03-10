<?php

namespace pixelpost\plugins\auth;

use pixelpost\Config,
	pixelpost\plugins\api\Exception\FieldRequired;

// check the request
if (!isset($event->request->public_key)) throw new FieldRequired('auth.request', 'public_key');
if (!isset($event->request->session))    throw new FieldRequired('auth.request', 'session');

// check the public_key exists
try
{
	$entity = Model::entity_get_by_public_key($event->request->public_key);

	// retrieve the lifetime configuration value
	$lifetime = Config::create()->plugin_auth->lifetime;

	// set auth module
	$auth = new Auth();
	$auth->set_lifetime($lifetime)
		 ->set_public_key($event->request->public_key)
		 ->set_private_key($entity['private_key']);

	// create the challenge
	$challenge = $auth->get_challenge();
	$session   = $event->request->session;

	// store the challenge in database
	Model::challenge_add($challenge, $entity['id'], $session, $lifetime);
}
catch(ModelExceptionNoResult $e)
{
	// netheir tell entity don't exists. This is an indication for attacker
	$challenge = md5(uniqid());
	$lifetime  = 300;
}

// return the response
$event->response = compact('challenge', 'lifetime');