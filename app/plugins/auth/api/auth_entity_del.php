<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.entity.del';

// the request
$request = $event->request;

// more grant check come later
if (!Plugin::is_auth()) throw new Ungranted($method);

// input validation
$entity = self::get_required('entity', $request, $method);

// check entity exists
if (!self::check_entity_key($entity, $id, $user)) throw new FieldNonExists('entity');

// check grants
if (!Plugin::is_granted('admin', $user)) throw new Ungranted($method);

// delete the entity
Model::entity_del($id);

$event->response = array('message' => 'entity deleted');