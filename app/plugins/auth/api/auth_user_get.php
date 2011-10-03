<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.get');

// check required data
if (!isset($event->request->username)) throw new Exception\FieldRequired('auth.user.get', 'username');

if (trim($event->request->username) == '') throw new Exception\FieldEmpty('username');

try
{
	list($id, $password) = Model::user_get_by_name($event->request->username);
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNonExists('username');
}

$event->response = array('id' => $id);
