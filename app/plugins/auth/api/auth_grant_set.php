<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

// check grants
if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.grant.set');

// check required data
if (!isset($event->request->grant)) throw new Exception\FieldRequired('auth.grant.set', 'grant');

if (!isset($event->request->name)) throw new Exception\FieldRequired('auth.grant.set', 'name');

if (trim($event->request->grant) == '') throw new Exception\FieldEmpty('grant');

if (trim($event->request->name) == '') throw new Exception\FieldEmpty('name');

// check if grant exists
try
{
	$grantId = Model::grant_get($event->request->grant);
}
catch(ModelExceptionNoResult $e) 
{	
	throw new Exception\FieldNonExists('grant');
}

// check if optionnal newname is already exists
try
{
	Model::grant_get($event->request->name);

	throw new Exception\FieldNotValid('name', 'grant already exists');
}
catch(ModelExceptionNoResult $e) {}

// update grant
Model::grant_update($grantId, $event->request->name);

$event->response = array('message' => 'grant updated');