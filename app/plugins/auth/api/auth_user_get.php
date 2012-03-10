<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.user.get';

// the request
$request = $event->request;

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// input validation
$user = self::get_required('user', $request, $method);

// check user exists
if (!self::check_user_name($user, $id, $pass, $mail)) throw new FieldNonExists('user');

// this can feel stupid but it's important to keep a distinction between
// user identifier and user name, even if today it is the same.
$event->response = array('name' => $user, 'email' => $mail);
