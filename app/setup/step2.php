<?php

use pixelpost\core,
	pixelpost\setup\DependencyManager as DM,
	pixelpost\plugins\auth,
	pixelpost\plugins\photo;

try
{
	$error = '';
	$rollbackTo = 0;

	// create the /private directory
	if (mkdir(PRIV_PATH, 0775) == false)
	{
		throw new Exception(sprintf('Cannot create `%s`.', PRIV_PATH));
	}

	$rollbackTo = 1;

	// copy the /config.json file
	$src = APP_PATH  . '/setup/samples/config_sample.json';
	$dst = PRIV_PATH . '/config.json';

	if (copy($src, $dst) == false)
	{
		throw new Exception(sprintf('Cannot copy `%s` to `%s`.', $src, $dst));
	}

	$rollbackTo = 2;

	// copy the /.htaccess file
	$src = APP_PATH  . '/setup/samples/htaccess_sample';
	$dst = ROOT_PATH . '/.htaccess';

	if (copy($src, $dst) == false)
	{
		throw new Exception(sprintf('Cannot copy `%s` to `%s`.', $src, $dst));
	}

	$rollbackTo = 3;

	// copy the /private/.htaccess file
	$src = APP_PATH  . '/setup/samples/htaccess_priv_sample';
	$dst = PRIV_PATH . '/.htaccess';

	if (copy($src, $dst) == false)
	{
		throw new Exception(sprintf('Cannot copy `%s` to `%s`.', $src, $dst));
	}

	$rollbackTo = 4;

	// copy the /index.php file
	$src = APP_PATH  . '/setup/samples/index_sample.php';
	$dst = ROOT_PATH . '/index.php';

	if (copy($src, $dst) == false)
	{
		throw new Exception(sprintf('Cannot copy `%s` to `%s`.', $src, $dst));
	}

	$rollbackTo = 5;

	// create the database
	core\Db::create();

	$rollbackTo = 6;

	// Load the request and retrieve step1 form and userdir in url data
	$request = core\Request::create()->auto();
	$post    = $request->get_post();
	$userdir = $request->get_params();

	array_pop($userdir); // remove install.php
	array_pop($userdir); // remove app

	// Load, update and save the config file
	$conf           = core\Config::load(PRIV_PATH . '/config.json');
	$conf->userdir  = implode('/', $userdir);
	$conf->url      = $request->set_userdir($conf->userdir)->get_base_url();
	$conf->timezone = $post['timezone'];
	$conf->title    = $post['title'];
	$conf->email    = $post['email'];
	$conf->uid      = md5(uniqid() . microtime() . $request->get_request_url());
	$conf->version  = VERSION;
	$conf->save();

	// detect all plugins already in the package (and store the list in conf)
	core\Plugin::detect();

	// create the install plugin order
	$manager = new DM(array_keys(core\Filter::object_to_array($conf->plugins)));

	foreach($manager->process() as $plugin)
	{
		if (core\Plugin::active($plugin)) continue;

		$e = core\Plugin::get_last_error();
		$m = 'Error activating plugin `%s`. Error: %s.';
		throw new Exception(sprintf($m, $plugin, $e));
	}

	$rollbackTo = 7;

	// add user / password (not use api because api require grant access)
	$userName  = $post['username'];
	$userPass  = $post['password'];
	$userEmail = $post['email'];
	$userId    = auth\Model::user_add($userName, $userPass, $userEmail);
	$entityId  = auth\Model::user_get_entity_id($userId);

	// for our admin user, add all grant access to him
	foreach(auth\Model::grant_list() as $grant)
	{
		auth\Model::entity_grant_link($entityId, $grant['id']);
	}

	// need ADMIN_URL constant for webAuth
	define('ADMIN_URL', $conf->url . $conf->plugin_router->admin . '/');

	// authentificate the user
	auth\WebAuth::register($userName, $userPass, $userId, $request->get_host());
}
catch(Exception $e)
{
	$error = $e->getMessage() . ', on line: ' . $e->getLine() . ' : ' . $e->getFile();

	if ($rollbackTo >= 7) photo\Plugin::uninstall();
	if ($rollbackTo >= 6) unlink(PRIV_PATH . '/sqlite3.db');
	if ($rollbackTo >= 5) unlink(ROOT_PATH . '/index.php');
	if ($rollbackTo >= 4) unlink(PRIV_PATH . '/.htaccess');
	if ($rollbackTo >= 3) unlink(ROOT_PATH . '/.htaccess');
	if ($rollbackTo >= 2) unlink(PRIV_PATH . '/config.json');
	if ($rollbackTo >= 1) rmdir(PRIV_PATH);
}

$tpl = core\Template::create()->set_cache_raw_template(false)->set_template_path(__DIR__ . '/tpl');

$tpl->error = $error;
$tpl->data  = core\Filter::array_to_object($post);
$tpl->home  = ADMIN_URL;

$tpl->publish(($error != '') ? 'step2-fail.tpl' : 'step2-success.tpl');
