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
	list($userId, $password) = Model::user_get_by_name($event->request->user);
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

Model::user_grant_link($userId, $grantId);

$event->response = array('message' => 'user have now the grant access');
