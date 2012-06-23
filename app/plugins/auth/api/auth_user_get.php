<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.user.get';

// the request
$request = $event->request;

// more grant check come later
if (!Plugin::is_auth()) throw new Ungranted($method);

// input validation
$user = self::get_required('user', $request, $method);

// check user exists
if (!self::check_user_name($user, $id, $pass, $mail)) throw new FieldNonExists('user');

// if the user connected request its own data ? (self grant)
$user_id    = Plugin::get_user_id();
$self_grant = ($user_id == $id) ? $user_id : 0;

// check grants
if (!Plugin::is_granted('admin', $self_grant)) throw new Ungranted($method);

// this can feel stupid but it's important to keep a distinction between
// user identifier and user name, even if today it is the same.
$event->response = array('name' => $user, 'email' => $mail);
