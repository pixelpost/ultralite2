<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNotValid,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.grant.set';

// the request
$request = $event->request;

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// input validation
$grant = self::get_required('grant', $request, $method);
$name  = self::get_required('name' , $request, $method);

// check newname is different
if ($grant == $name) throw new FieldNotValid('name', 'no update was needed');

// check grant exists
if (!self::check_grant_name($grant, $id)) throw new FieldNonExists('grant');

// check if newname already exists
if (self::check_grant_name($name)) throw new FieldNotValid('name', 'name already exists');

// update grant
Model::grant_update($id, $name);

$event->response = array('grant' => $name);