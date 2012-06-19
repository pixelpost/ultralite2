<?php

namespace pixelpost\plugins\admin;

use pixelpost\core\Template,
	pixelpost\core\Config,
	DateTimeZone;

$timezones = DateTimeZone::listIdentifiers();

$conf = Config::create();

$form = array(
	'conftitle'   => $conf->title,
	'confemail'   => $conf->email,
	'confurl'     => $conf->url,
	'confadmin'   => $conf->pixelpost->admin,
	'confapi'     => $conf->pixelpost->api,
	'conftz'      => $conf->timezone,
	'confdebug'   => $conf->debug,
	'confversion' => $conf->version,
	'confuid'     => $conf->uid,
);

if ($event->request->is_post())
{
	$post = $event->request->get_post() + $form;

	$post = filter_var_array($post, array(
		'conftitle' => array('filter' => FILTER_SANITIZE_STRING),
		'confemail' => array('filter' => FILTER_VALIDATE_EMAIL),
		'confurl'   => array('filter' => FILTER_VALIDATE_URL),
		'confadmin' => array('filter' => FILTER_SANITIZE_STRING),
		'confapi'   => array('filter' => FILTER_SANITIZE_STRING),
		'conftz'    => array('filter' => FILTER_SANITIZE_STRING),
		'confdebug' => array('filter' => FILTER_VALIDATE_BOOLEAN),
	));

	if ($post['conftitle'])
	{
		$conf->title = $form['conftitle'] = $post['conftitle'];
	}

	if ($post['confemail'])
	{
		$conf->email = $form['confemail'] = $post['confemail'];
	}

	if ($post['confurl'])
	{
		$conf->userdir = trim(parse_url($post['confurl'], PHP_URL_PATH), '/');

		$conf->url = $form['confurl'] = $post['confurl'];

		$reload = true;
	}

	if ($post['confadmin'])
	{
		$conf->pixelpost->admin = $form['confadmin'] = $post['confadmin'];

		$reload = true;
	}

	if ($post['confapi'])
	{
		$conf->pixelpost->api = $form['confapi'] = $post['confapi'];
	}

	if ($post['conftz'] && in_array($post['conftz'], $timezones))
	{
		$conf->timezone = $form['conftz'] = $post['conftz'];
	}

	$reload = $reload or $form['confdebug'] == $post['debug'];

	$conf->debug = $form['confdebug'] = $post['confdebug'];

	$conf->save();

	if ($reload)
	{
		header('Location:' . $conf->url . $conf->pixelpost->admin . '/settings', 302);
		exit();
	}
}

Template::create()
	->assign($form)
	->assign('is_tab_index', true)
	->assign('timezones', $timezones)
	->publish('admin/tpl/settings/index.tpl');
