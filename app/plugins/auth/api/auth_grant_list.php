<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.grant.list');

if (isset($event->request->user))
{
	// return grants of a user
	try
	{
		extract(Model::user_get_by_name($event->request->user));
	}
	catch(ModelExceptionNoResult $e)
	{
		throw new Exception\FieldNonExists('user');
	}

	$event->response = array('grant' => Model::user_grant_list_by_user($id));
}
else
{
	// return all grants
	$event->response = array('grant' => Model::grant_list());
}
