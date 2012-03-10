<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.entity.list';

// the request
$request = $event->request;

// more grant check come later
if (!Plugin::is_auth()) throw new Ungranted($method);

// input validation
$user  = self::get_optional('user' , $request, $method);
$grant = self::get_optional('grant', $request, $method);

// check user exists, if not provided get use the authenticated one
if ($user && !self::check_user_name($user, $id))
{
	throw new FieldNonExists('user');
}
else
{
	$id = Plugin::get_user_id();
}

// check grants
if (!Plugin::is_granted('admin', $id)) throw new Ungranted($method);

// where goes the results
$forAdmin = Plugin::is_auth_admin();

if ($grant)
{
	// return entity have a grant (name, public_key, private_key)
	if (!self::check_grant_name($grant, $grant_id)) throw new FieldNonExists('grant');

	$list = Model::entity_grant_list_by_grant($grant_id, function(&$e) use ($id, $forAdmin)
	{
		// mask the users personnal entity
		if ($e['is_me'] || $e['user_id'] != $id) return false;

		if (!$forAdmin) unset($e['private_key']);

		unset($e['is_me'], $e['id'], $e['user_id']);

		$e['entity'] = $e['public_key'];
	});
}
else
{
	// return all entity (name, public_key, private_key)
	$list = Model::entity_list_by_user($id, function(&$e) use ($forAdmin)
	{
		// mask the user entity
		if ($e['is_me']) return false;

		if (!$forAdmin) unset($e['private_key']);

		unset($e['is_me'], $e['id']);

		$e['entity'] = $e['public_key'];
	});
}

$event->response = compact('list');
