<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.user.del';

// the request
$request = $event->request;

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// input validation
$user = self::get_required('user', $request, $method);

// check user exists
if (!self::check_user_name($user, $id)) throw new FieldNonExists('user');

Model::user_del($id);

$event->response = array('message' => 'user deleted');