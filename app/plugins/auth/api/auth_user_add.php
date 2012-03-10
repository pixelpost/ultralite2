<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNotValid;

// method
$method = 'auth.user.add';

// the request
$request = $event->request;

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// input validation
$name     = self::get_required('name'    , $request, $method);
$password = self::get_required('password', $request, $method);
$email    = self::get_required('email',    $request, $method);

// check email format
if (false === $email = filter_var($email, FILTER_VALIDATE_EMAIL))
{
	throw new FieldNotValid('email', 'it not seems to be correct.');
}

// check name exists
if (self::check_user_name($name))
{
	throw new FieldNotValid('name', 'name already exists');
}

Model::user_add($name, $password, $email);

// this can feel stupid but it's important to keep a distinction between
// user identifier and user name, even if today it is the same.
$event->response = array('user' => $name);