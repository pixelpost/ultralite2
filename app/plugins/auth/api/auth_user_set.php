<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.set');

// check required data
if (!isset($event->request->username)) throw new Exception\FieldRequired('auth.user.set', 'username');

if (trim($event->request->username) == '') throw new Exception\FieldEmpty('username');

// check optionnal data
$username = false;
$password = false;

if (isset($event->request->newname)) $username = $event->request->newname;
if (isset($event->request->password)) $password = $event->request->password;

if ($username !== false && trim($username) == '') throw new Exception\FieldEmpty('newname');
if ($password !== false && trim($password) == '') throw new Exception\FieldEmpty('password');

if ($username !== false && $username == $event->request->username) $username  = false;

// check if username exists
try
{
	list($userId, $userPassword) = Model::user_get_by_name($event->request->username);
}
catch(ModelExceptionNoResult $e) 
{	
	throw new Exception\FieldNonExists('username');
}

// check if optionnal newname is already exists
if ($username !== false)
{
	try
	{
		Model::user_get_by_name($username);

		throw new Exception\FieldNotValid('newname', 'user already exists');
	}
	catch(ModelExceptionNoResult $e) {}
}

// update the user
if ($username === false) $username = $event->request->username;
if ($password === false) $password = $userPassword;

Model::user_update($userId, $username, $password);

$event->response = array('message' => 'user updated');