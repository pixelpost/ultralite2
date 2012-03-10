<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.user.grant.del';

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// the request
$request = $event->request;

// input validation
$user  = self::get_required('user' , $request, $method);
$grant = self::get_required('grant', $request, $method);

// check user exists
if (!self::check_user_name($user, $user_id)) throw new FieldNonExists('user');

// check grant exists
if (!self::check_grant_name($grant, $grant_id)) throw new FieldNonExists('grant');

// remove grant to all user's entities
try
{
	foreach (Model::entity_list_by_user($user_id) as $entity)
	{
		Model::entity_grant_unlink($entity['id'], $grant_id);
	}
}
catch (ModelExceptionNoResult $e) {}

$event->response = array('message' => 'user have no longer the grant access');
