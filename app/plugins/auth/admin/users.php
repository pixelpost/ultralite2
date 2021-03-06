<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Template,
	pixelpost\plugins\api\Plugin as Api;

// retrieve in firts all admins for flag them in the list
$list = current(Api::call('auth.user.list', array('grant' => 'admin')));

$admins = array();

foreach ($list as $user)
{
	$admins[$user['user']] = true;
}

// retrieve now the whole list of users
$users = array();

foreach(current(Api::call('auth.user.list')) as $user)
{
	$infos = Api::call('auth.user.get', $user);

	$users[] = $user + $infos + array(
		'is_admin' => isset($admins[$user['user']]),
		'gravatar' => md5(strtolower($infos['email'])),
	);
}

Template::create()
	->assign('users', $users)
	->publish('auth/tpl/users.tpl');