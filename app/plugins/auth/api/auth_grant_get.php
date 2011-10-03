<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.grant.get');

// check required data
if (!isset($event->request->name)) throw new Exception\FieldRequired('auth.grant.get', 'name');

if (trim($event->request->name) == '') throw new Exception\FieldEmpty('name');

try
{
	$id = Model::grant_get($event->request->name);
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNonExists('name');
}

$event->response = array('id' => $id);
