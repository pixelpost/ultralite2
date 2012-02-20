<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.grant.add');

if (!isset($event->request->name)) throw new Exception\FieldRequired('auth.grant.add', 'name');

if (trim($event->request->name) == '') throw new Exception\FieldEmpty('name');

try
{
	Model::grant_get($event->request->username);

	throw new Exception\FieldNotValid('name', 'grant already exists');
}
catch(ModelExceptionNoResult $e) {}

$grantId = Model::grant_add($event->request->name);

$event->response = array('message' => 'user added');