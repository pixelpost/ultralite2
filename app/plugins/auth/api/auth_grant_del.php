<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.grant.del';

// the request
$request = $event->request;

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// input validation
$grant = self::get_required('grant', $request, $method);

// check grant exists
if (!self::check_grant_name($grant, $id)) throw new FieldNonExists('grant');

Model::grant_del($id);

$event->response = array('message' => 'grant deleted');