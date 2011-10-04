<?php

require __DIR__ . SEP . 'Dependency.php';

use pixelpost\plugins\auth\Model as Auth;

try
{	
	$error = '';
	$rollbackTo = 0;
	
	// create the private directory
	if (mkdir(PRIV_PATH, 0775) == false)
	{
		throw new Exception('Cannot create `' . PRIV_PATH . '`.');
	}
	
	$rollbackTo = 1;

	// copy the config file
	$src = APP_PATH . SEP . 'setup' . SEP . 'config_sample.json';
	$dst = PRIV_PATH . SEP . 'config.json';

	if (copy($src, $dst) == false)
	{
		throw new Exception('Cannot copy `'. $src . '` to `' . $dst . '`.');
	}

	$rollbackTo = 2;
	
	// copy the .htaccess file
	$src = APP_PATH . SEP . 'setup' . SEP . 'htaccess_sample';
	$dst = ROOT_PATH . SEP . '.htaccess';

	if (copy($src, $dst) == false)
	{
		throw new Exception('Cannot copy `'. $src . '` to `' . $dst . '`.');
	}

	$rollbackTo = 3;
	
	// copy the private/.htaccess file
	$src = APP_PATH . SEP . 'setup' . SEP . 'htaccess_priv_sample';
	$dst = PRIV_PATH . SEP . '.htaccess';

	if (copy($src, $dst) == false)
	{
		throw new Exception('Cannot copy `'. $src . '` to `' . $dst . '`.');
	}
	
	$rollbackTo = 4;
	
	// copy the index.php file
	$src = APP_PATH . SEP . 'setup' . SEP . 'index_sample.php';
	$dst = ROOT_PATH . SEP . 'index.php';

	if (copy($src, $dst) == false)
	{
		throw new Exception('Cannot copy `'. $src . '` to `' . $dst . '`.');
	}

	$rollbackTo = 5;
	
	// Load the request
	$request = pixelpost\Request::create()->auto();
	
	// retrieve the posted data
	$post = $request->get_post();
	
	// Load the config file
	$conf = pixelpost\Config::load(PRIV_PATH . SEP . 'config.json');

	// retreive the userdir
	$path = explode('/', $request->get_path()); 

	array_pop($path); // remove install.php
	array_pop($path); // remove app

	$conf->userdir = implode('/', $path);

	// retreive the website url
	$conf->url = $request->set_userdir($conf->userdir)->get_base_url();
	
	// retrieve the timezone
	$conf->timezone = $post['timezone'];
	
	// retrieve the title
	$conf->title = $post['title'];
	
	// retrieve the email
	$conf->email = $post['email'];

	// create an uniq id for this installation
	$conf->uid = md5(uniqid() . microtime() . $request->get_request_url());

	// set the version number of the installation
	$conf->version = VERSION;
	
	// save the configuration file
	$conf->save();

	// create the database
	$db = pixelpost\Db::create();
	
	$rollbackTo = 6;

	// detect all plugins already in the package (and store the list in conf)
	pixelpost\Plugin::detect();
	
	// create the install plugin order
	$manager = new DependencyManager(array_keys(pixelpost\Filter::object_to_array($conf->plugins)));
		
	foreach($manager->process() as $plugin)
	{
		if (pixelpost\Plugin::active($plugin) == false)
		{
			$e = pixelpost\Plugin::get_last_error();
			throw new Exception("Error during plugin '$plugin' setup. error: $e.");
		}
	}
	
	// add user / password (not use api because api require grant access)
	$userId = Auth::user_add($post['username'], $post['password']);

	// for our admin user, add all grant access to him
	foreach(Auth::grant_list() as $grant) Auth::user_grant_link($userId, $grant['id']);
	
}
catch(Exception $e)
{	
	$error = $e->getMessage() . ', on line: ' . $e->getLine() . ' : ' . $e->getFile();
	
	if ($rollbackTo >= 6) unlink(PRIV_PATH . SEP . 'sqlite3.db');
	if ($rollbackTo >= 5) unlink(ROOT_PATH . SEP . 'index.php');
	if ($rollbackTo >= 4) unlink(PRIV_PATH . SEP . '.htaccess');
	if ($rollbackTo >= 3) unlink(ROOT_PATH . SEP . '.htaccess');
	if ($rollbackTo >= 2) unlink(PRIV_PATH . SEP . 'config.json');
	if ($rollbackTo >= 1) rmdir(PRIV_PATH);
}

$template = ($error != '') ? 'step2-fail.tpl' : 'step2-success.tpl';

$tpl = pixelpost\Template::create();

$tpl->set_cache_raw_template(false)->set_template_path(__DIR__ . SEP . 'tpl');

$tpl->error = $error;
$tpl->data  = pixelpost\Filter::array_to_object($post);
$tpl->home  = $conf->url . $conf->plugin_router->admin . '/';

$tpl->publish($template);
