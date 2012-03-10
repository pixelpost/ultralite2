<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.entity.get';

// the request
$request = $event->request;

// more grant check come later
if (!Plugin::is_auth()) throw new Ungranted($method);

// input validation
$entity = self::get_required('entity', $request, $method);

// check entity exists
try
{
	// create $id, $name, $id_me, $user_id, $private_key
	extract(Model::entity_get_by_public_key($entity));

	// create $user_name, $user_pass and $user_email
	extract(Model::user_get_by_id($user_id), EXTR_PREFIX_ALL, 'user_');

	$user       = $user_name;
	$public_key = $entity;
}
catch(ModelExceptionNoResult $e)
{
	throw new FieldNonExists('entity');
}

// check grants
if (!Plugin::is_granted('admin', $user_id)) throw new Ungranted($method);

$event->response = compact('name', 'user', 'public_key');

// return private key, only in admin web interface
if (Plugin::is_auth_admin()) $event->response += compact('private_key');
