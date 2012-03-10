<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.grant.get';

// the request
$request = $event->request;

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// input validation
$grant = self::get_required('grant', $request, $method);

// check grant exists
if (!self::check_grant_name($grant)) throw new FieldNonExists('grant');

$event->response = array('name' => $grant);