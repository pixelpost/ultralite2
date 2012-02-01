<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.set');

// check required data
if (!isset($event->request->user)) throw new Exception\FieldRequired('auth.user.set', 'user');

if (trim($event->request->user) == '') throw new Exception\FieldEmpty('user');

// check optionnal data
$username = false;
$password = false;

if (isset($event->request->name))     $username = $event->request->name;
if (isset($event->request->password)) $password = $event->request->password;

if ($username !== false && trim($username) == '') throw new Exception\FieldEmpty('name');
if ($password !== false && trim($password) == '') throw new Exception\FieldEmpty('password');

if ($username !== false && $username == $event->request->user) $username  = false;

// check if username exists
try
{
	// create $user_id and $user_password
	extract(Model::user_get_by_name($event->request->user), EXTR_PREFIX_ALL, 'user_');
}
catch(ModelExceptionNoResult $e) 
{	
	throw new Exception\FieldNonExists('user');
}

// check if optionnal newname is already exists
if ($username !== false)
{
	try
	{
		Model::user_get_by_name($username);

		throw new Exception\FieldNotValid('name', 'user already exists');
	}
	catch(ModelExceptionNoResult $e) {}
}

// update the user
if ($username === false) $username = $event->request->user;
if ($password === false) $password = $user_password;

Model::user_update($user_id, $username, $password);

$event->response = array('message' => 'user updated');