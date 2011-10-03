<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.del');

// check required data
if (!isset($event->request->username)) throw new Exception\FieldRequired('auth.user.del', 'username');

if (trim($event->request->username) == '') throw new Exception\FieldEmpty('username');

try
{
	list($id, $password) = Model::user_get_by_name($event->request->username);
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNonExists('username');
}

Model::user_del($id);

$event->response = array('message' => 'user deleted');