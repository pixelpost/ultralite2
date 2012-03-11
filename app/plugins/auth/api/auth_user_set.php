<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNotValid,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.user.set';

// the request
$request = $event->request;

// more grant check come later
if (!Plugin::is_auth()) throw new Ungranted($method);

// input validation
$user     = self::get_required('user'    , $request, $method);
$name     = self::get_optional('name'    , $request, $method);
$password = self::get_optional('password', $request, $method);
$email    = self::get_optional('email',    $request, $method);

// check if user exists
if (!self::check_user_name($user, $id, $pass, $mail)) throw new FieldNonExists('user');

// check grants (require admin grants or just self update)
if (!Plugin::is_granted('admin', $id)) throw new Ungranted($method);

// check if optionnal newname is already exists
if ($name && $name != $user && self::check_user_name($name))
{
	throw new FieldNotValid('name', 'user already exists');
}

// update the user
$name     = $name     ?: $user;
$password = $password ?: $pass;
$email    = $email    ?: $mail;

Model::user_update($id, $name, $password, $email);

$event->response = array('user' => $name);