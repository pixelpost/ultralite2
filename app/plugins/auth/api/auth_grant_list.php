<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNotValid,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.grant.list';

// the request
$request = $event->request;

// more grant check come later
if (!Plugin::is_auth()) throw new Ungranted($method);

// input validation
$user   = self::get_optional('user',   $request, $method);
$entity = self::get_optional('entity', $request, $method);

// check there is only one filter
if ($user && $entity) throw new FieldNotValid('user', 'only one filter at a time is valid');

if ($user)
{
	// return grants of a user
	if (!self::check_user_name($user, $id)) throw new FieldNonExists('user');

	// check grants
	if (!Plugin::is_granted('admin', $id)) throw new Ungranted($method);

	// return user grant
	$list = Model::entity_grant_list_by_entity(Model::user_get_entity_id($id), function(&$item)
	{
		$item = array(
			'grant' => $item['name'],
			'name'  => $item['name'],
		);
	});
}
elseif ($entity)
{
	// return grants of a entity
	if (!self::check_entity_key($entity, $id, $user)) throw new FieldNonExists('entity');

	// check grants
	if (!Plugin::is_granted('admin', $user)) throw new Ungranted($method);

	// return entity grant
	$list = Model::entity_grant_list_by_entity($id, function(&$item)
	{
		$item = array(
			'grant' => $item['name'],
			'name'  => $item['name'],
		);
	});
}
else
{
	// check grants
	if (Plugin::is_granted('read')) throw new Ungranted($method);

	// return all grants
	$list = Model::grant_list(function(&$item)
	{
		$item = array(
			'grant' => $item['name'],
			'name'  => $item['name'],
		);
	});
}

$event->response = compact('list');