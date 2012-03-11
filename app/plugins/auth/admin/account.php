<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Template,
	pixelpost\plugins\api\Plugin as Api;

$message = '';

// who is connected
$user_online = Plugin::get_entity_name();

// is form posted ?
if ($event->request->is_post())
{
	// retrieve posted data in $post
	$post = filter_var_array($event->request->get_post(), array(
		'name'     => array('filter' => FILTER_SANITIZE_STRING),
		'email'    => array('filter' => FILTER_VALIDATE_EMAIL),
		'password' => array('filter' => FILTER_SANITIZE_STRING),
	));

	// delete not provided and bad value
	if (!$post['name'])     unset($post['name']);
	if (!$post['email'])    unset($post['email']);
	if (!$post['password']) unset($post['password']);

	// remove name if not changed
	if ($post['name'] && $post['name'] == $user_online) unset($post['name']);

	// make the update
	Api::call_api_method('auth.user.set', $post + array('user' => $user_online));

	// template response...
	$message = 'Updated.';

	if (isset($post['name']) || isset($post['password']))
	{
		$message .= ' You need to reconnect on next page.';
	}

	if (isset($post['name'])) $user_online = $post['name'];
}

// retrieve user data
$user = Api::call_api_method('auth.user.get', array('user' => $user_online));

// retrieve user entities
$entities = Api::call_api_method('auth.entity.list', array());

// retrieve user grant
$grants = Api::call_api_method('auth.grant.list', array('user' => $user_online));

Template::create()
	->assign('message',  $message)
	->assign('user',     $user)
	->assign('entities', $entities['list'])
	->assign('grants',   $grants['list'])
	->publish('auth/tpl/account.php');