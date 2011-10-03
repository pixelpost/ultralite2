<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.list');


if (isset($event->request->grant))
{
	// return user have a grant
	try
	{
		$grantId = Model::grant_get($event->request->grant);
	}
	catch(ModelExceptionNoResult $e)
	{
		throw new Exception\FieldNonExists('grant');
	}
	
	$event->response = array('user' => Model::user_grant_list_by_grant($grantId));
}
else
{
	// return all user
	$event->response = array('user' => Model::user_list());
}
