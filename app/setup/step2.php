<?php

use pixelpost\core,
	pixelpost\setup\DependencyManager as DM,
	pixelpost\plugins\auth,
	pixelpost\plugins\photo;

try
{
	$error = '';
	$rollbackTo = 0;

	// Load the request and retrieve step1 form and userdir in url data
	$request  = core\Request::create()->auto();
	$post     = $request->get_post();
	$userdir  = $request->get_params();

	if (PHAR) array_pop($userdir); // remove index.php

	array_pop($userdir); // remove setup
	array_pop($userdir); // remove app (or pixelpost.phar.php)

	$userdir  = implode('/', $userdir);
	$base_url = $request->set_userdir($userdir)->get_base_url();

	// need ADMIN_URL constant for WebAuth class (see: plugin auth)
	define('ADMIN_URL', $base_url . 'admin/');

	// create the /private directory
	if (mkdir(PRIV_PATH, 0775) == false)
	{
		throw new Exception(sprintf('Cannot create `%s`.', PRIV_PATH));
	}

	$rollbackTo = 1;

	// create the /public directory
	if (mkdir(PUB_PATH, 0775) == false)
	{
		throw new Exception(sprintf('Cannot create `%s`.', PUB_PATH));
	}

	$rollbackTo = 2;

	// copy the /config.json file
	$src = APP_PATH  . '/setup/samples/config_sample.json';
	$dst = PRIV_PATH . '/config.json';

	if (copy($src, $dst) == false)
	{
		throw new Exception(sprintf('Cannot copy `%s` to `%s`.', $src, $dst));
	}

	$rollbackTo = 3;

	// copy the /.htaccess file
	$src = APP_PATH  . '/setup/samples/htaccess_sample';
	$dst = ROOT_PATH . '/.htaccess';

	if (copy($src, $dst) == false)
	{
		throw new Exception(sprintf('Cannot copy `%s` to `%s`.', $src, $dst));
	}

	$rollbackTo = 4;

	// copy the /private/.htaccess file
	$src = APP_PATH  . '/setup/samples/htaccess_priv_sample';
	$dst = PRIV_PATH . '/.htaccess';

	if (copy($src, $dst) == false)
	{
		throw new Exception(sprintf('Cannot copy `%s` to `%s`.', $src, $dst));
	}

	$rollbackTo = 5;

	// copy the /index.php file
	$file = PHAR ? basename(Phar::running()) : 'app/app.php';
	$dst  = ROOT_PATH . '/index.php';
	$tpl  = core\Template::create();
	$tpl->set_cache_raw_template(false);
	$tpl->set_template_path(APP_PATH . '/setup/samples');
	$tpl->assign('file', $file);

	if (file_put_contents($dst, $tpl->render('index_sample.php')) == false)
	{
		throw new Exception(sprintf('Cannot copy `%s` to `%s`.', $src, $dst));
	}

	$rollbackTo = 6;

	// create the database
	core\Db::create();

	$rollbackTo = 7;

  // Load config (must be called before plugin detection)
	$conf = core\Config::load(PRIV_PATH . '/config.json');

	// detect all plugins already in the package (and store the list in conf)
	core\Plugin::detect();

	// Update and save the config file
	$conf->userdir  = $userdir;
	$conf->url      = $base_url;
	$conf->timezone = $post['timezone'];
	$conf->title    = $post['title'];
	$conf->email    = $post['email'];
	$conf->uid      = md5(uniqid() . microtime() . $request->get_request_url());
	$conf->version  = VERSION;
	$conf->packaged = array_keys(core\Filter::object_to_array($conf->plugins));
	$conf->save();

	// create the install plugin order
	$manager = new DM($conf->packaged);

	foreach($manager->process() as $plugin)
	{
		if (core\Plugin::active($plugin)) continue;

		$e = core\Plugin::get_last_error();
		$m = 'Error activating plugin `%s`. Error: %s.';
		throw new Exception(sprintf($m, $plugin, $e));
	}

	$rollbackTo = 8;

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

	// authentificate the user
	auth\WebAuth::register($userName, $userPass, $userId, $request->get_host());
}
catch(Exception $e)
{
	$error = $e->getMessage();

	if ($rollbackTo >= 8) photo\Plugin::uninstall();
	if ($rollbackTo >= 7) unlink(PRIV_PATH . '/sqlite3.db');
	if ($rollbackTo >= 6) unlink(ROOT_PATH . '/index.php');
	if ($rollbackTo >= 5) unlink(PRIV_PATH . '/.htaccess');
	if ($rollbackTo >= 4) unlink(ROOT_PATH . '/.htaccess');
	if ($rollbackTo >= 3) unlink(PRIV_PATH . '/config.json');
	if ($rollbackTo >= 2) rmdir(PUB_PATH);
	if ($rollbackTo >= 1) rmdir(PRIV_PATH);
}

$template = ($error != '') ? 'step2-fail.tpl' : 'step2-success.tpl';

$tpl = core\Template::create();
$tpl->set_cache_raw_template(false);
$tpl->set_template_path(APP_PATH . '/setup/tpl');

$tpl->use_public = ($error == '');
$tpl->home       = ADMIN_URL;
$tpl->data       = core\Filter::array_to_object($post);
$tpl->error      = $error;

$tpl->publish($template);
