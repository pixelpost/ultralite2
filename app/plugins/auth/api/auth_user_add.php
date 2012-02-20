<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.add');

if (!isset($event->request->user)) throw new Exception\FieldRequired('auth.user.add', 'user');

if (!isset($event->request->password)) throw new Exception\FieldRequired('auth.user.add', 'password');

if (trim($event->request->user) == '') throw new Exception\FieldEmpty('user');

if (trim($event->request->password) == '') throw new Exception\FieldEmpty('password');

try
{
	Model::user_get_by_name($event->request->user);

	throw new Exception\FieldNotValid('user', 'user already exists');
}
catch(ModelExceptionNoResult $e) {}

$userId = Model::user_add($event->request->user, $event->request->password);

$event->response = array('message' => 'user added');