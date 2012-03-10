<?php

namespace pixelpost\plugins\auth;

use pixelpost\plugins\api\Exception\Ungranted,
	pixelpost\plugins\api\Exception\FieldNonExists;

// method
$method = 'auth.user.list';

// the request
$request = $event->request;

// check grants
if (!Plugin::is_granted('admin')) throw new Ungranted($method);

// input validation
$grant = self::get_optional('grant', $request, $method);

if ($grant)
{
	// return user have a grant
	if (!self::check_grant_name($grant, $id)) throw new FieldNonExists('grant');

	$list = Model::entity_grant_list_by_grant($id, function(&$item)
	{
		// skip user's personnal entity
		if ($item['is_me']) return false;

		// this can feel stupid but it's important to keep a distinction between
		// user identifier and user name, even if today it is the same.
		$item = array(
			'user' => $item['name'],
			'name' => $item['name']
		);
	});
}
else
{
	// return all user
	$list = Model::user_list(function(&$item)
	{
		// this can feel stupid but it's important to keep a distinction between
		// user identifier and user name, even if today it is the same.
		$item = array(
			'user' => $item['name'],
			'name' => $item['name']
		);
	});
}

$event->response = compact('list');