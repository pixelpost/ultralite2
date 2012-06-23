<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Template,
	pixelpost\plugins\api\Plugin as Api;

// create the user form
$form = new classes\UserForm(Plugin::get_entity_name(), true);

// check if form is posted and process data
$form->check($event->request);

// create API request
$user = array('user' => $form->user_id);

// retrieve user data
$user += Api::call_api_method('auth.user.get', $user);
$user += array('gravatar' => md5(strtolower($user['email'])));

// retrieve user entities and grants
$entities = current(Api::call_api_method('auth.entity.list'));
$grants   = current(Api::call_api_method('auth.grant.list', $user));

// create the form template
$form_tpl = $form->render($user);

Template::create()
	->assign(compact('form_tpl', 'user', 'entities', 'grants'))
	->publish('auth/tpl/account.tpl');
