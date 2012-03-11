<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Config,
	pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.entity.add';

// the request
$request = $event->request;

// more grant check come later
if (!Plugin::is_auth()) throw new Ungranted($method);

// input validation
$user = self::get_required('user', $request, $method);
$name = self::get_required('name', $request, $method);

// check user exists
if (!self::check_user_name($user, $id, $pass)) throw new FieldNonExists('user');

// check grants
if (!Plugin::is_granted('admin', $id)) throw new Ungranted($method);

// a good salt
$salt = Config::create()->uid . $user . $name . $id . $pass;

// create keys
$public_key  = md5(uniqid() . 'public_key'  . microtime() . $salt);
$private_key = md5(uniqid() . 'private_key' . microtime() . $salt);

// register the entity
Model::entity_add($id, $name, $public_key, $private_key);

// return the public key as identifier
$event->response = array('entity' => $public_key);