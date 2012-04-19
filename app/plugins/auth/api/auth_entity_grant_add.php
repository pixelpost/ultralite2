<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNotValid,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.entity.grant.add';

// the request
$request = $event->request;

// more grant check come later
if (!Plugin::is_auth()) throw new Ungranted($method);

// input validation
$entity = self::get_required('entity', $request, $method);
$grant  = self::get_required('grant' , $request, $method);

// check entity exists and retrieve entity data
if (!self::check_entity_key($entity, $entity_id, $user_id)) throw new FieldNonExists('entity');

// is a administrator operates?
$is_admin = Plugin::is_granted('admin');

// check grants, admin or (self and WebAuth (an entity not change another one))
// may be we should add a grant for that.
if (!$is_admin && (Plugin::get_user_id() != $user_id || !Plugin::is_auth_admin()))
{
	throw new Ungranted($method);
}

// check that the entity could be granted
if (!$is_admin && !Plugin::is_granted($grant))
{
	throw new FieldNotValid('grant', 'User have not this grant');
}

// check grant exists
if (!self::check_grant_name($grant, $grant_id)) throw new FieldNonExists('grant');

// add the grant to the entity
Model::entity_grant_link($entity_id, $grant_id);

$event->response = array('message' => 'entity have now the grant access');
