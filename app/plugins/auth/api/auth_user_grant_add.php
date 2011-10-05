<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.grant.add');

// check required data
if (!isset($event->request->user)) throw new Exception\FieldRequired('auth.user.grant.add', 'user');
if (!isset($event->request->grant)) throw new Exception\FieldRequired('auth.user.grant.add', 'grant');

if (trim($event->request->user) == '') throw new Exception\FieldEmpty('user');
if (trim($event->request->grant) == '') throw new Exception\FieldEmpty('grant');

try
{
	// create $user_id and $user_password
	extract(Model::user_get_by_name($event->request->user), EXTR_PREFIX_ALL, 'user_');
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNonExists('user');
}

try
{
	$grantId = Model::grant_get($event->request->grant);
}
catch(ModelExceptionNoResult $e)
{
	throw new Exception\FieldNonExists('grant');
}

Model::user_grant_link($user_id, $grantId);

$event->response = array('message' => 'user have now the grant access');
