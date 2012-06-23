<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Template,
	pixelpost\core\Filter,
	pixelpost\plugins\api\Plugin as Api;

if (!($user = current($event->params)))
{
	$event->redirect('admin.404');
	exit();
}

// try to load the user, if not exists api thows an exception
// Exception can be raised when form try to update user data or in the api call
// Both exception will means that the user won't exists
try
{
	// load the user form, check if posted and valid and update user data
	$form = new classes\UserForm($user, $user == Plugin::get_entity_name());
	$form->check($event->request);

	$user = array('user' => $form->user_id);
	$user += Api::call('auth.user.get', $user);
}
catch(Exception $e)
{
	$event->redirect('admin.404');
	exit();
}

// create the user form
$form_tpl = $form->render($user);

// load all existing grants and user's grants and user's entities
$grants   = current(Api::call('auth.grant.list'));
$granted  = current(Api::call('auth.grant.list', $user));
$entities = current(Api::call('auth.entity.list', $user));

// transform couple ['grant' => 'X', 'name' => 'Y'] to ['X' => 'Y']
// array_combine work only on populated array
if (count($granted)) $granted = array_combine(
	Filter::array_column($granted, 'grant'),
	Filter::array_column($granted, 'grant')
);

Template::create()
	->assign(compact('form_tpl', 'user', 'granted', 'grants', 'entities'))
	->publish('auth/tpl/user.tpl');