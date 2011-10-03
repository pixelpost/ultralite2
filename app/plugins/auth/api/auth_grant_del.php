<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.grant.del');

// check required data
if (!isset($event->request->name)) throw new Exception\FieldRequired('auth.grant.del', 'name');

if (trim($event->request->name) == '') throw new Exception\FieldEmpty('name');

try
{
	$id = Model::grant_get($event->request->name);
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNonExists('name');
}

Model::grant_del($id);

$event->response = array('message' => 'grant deleted');