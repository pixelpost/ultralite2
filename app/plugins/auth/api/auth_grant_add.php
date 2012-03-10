<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNotValid;

// method
$method = 'auth.grant.add';

// the request
$request = $event->request;

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// input validation
$name = self::get_required('name', $request, $method);

// check grant exists
if (self::check_grant_name($name)) throw new FieldNotValid('name', 'grant already exists');

Model::grant_add($name);

$event->response = array('grant' => $name);