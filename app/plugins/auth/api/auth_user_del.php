<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.del');

// check required data
if (!isset($event->request->user)) throw new Exception\FieldRequired('auth.user.del', 'user');

if (trim($event->request->user) == '') throw new Exception\FieldEmpty('user');

try
{
	list($id, $password) = Model::user_get_by_name($event->request->user);
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNonExists('user');
}

Model::user_del($id);

$event->response = array('message' => 'user deleted');