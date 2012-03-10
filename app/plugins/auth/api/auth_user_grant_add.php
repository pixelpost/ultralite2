<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.user.grant.add';

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

// add grant to user
Model::entity_grant_link(Model::user_get_entity_id($user_id), $grant_id);

$event->response = array('message' => 'user have now the grant access');
