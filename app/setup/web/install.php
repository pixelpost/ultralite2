<?php

use DateTimeZone,
	pixelpost\core\Template,
	pixelpost\core\Request,
	pixelpost\core\Filter;

// create the app env
// use relative path here to be compatible with classic or phar method.
require_once '../bootstrap.php';

// create a well configured template
$tpl = Template::create();
$tpl->set_cache_raw_template(false);
$tpl->set_template_path(APP_PATH . '/setup/tpl/web');
$tpl->installed = false;

// test all pixelpost requirement before installation
$warnings = require APP_PATH . '/setup/install/tests.php';

if (count($warnings))
{
	$tpl->warnings = $warnings;
	$tpl->publish('requirements.tpl');
	exit();
}

// Load the http request and retrieve
$request = Request::create()->auto();

// Ask some information
if (!$request->is_post())
{
	$tpl->phpTZ     = ini_get('date.timezone');
	$tpl->timezones = DateTimeZone::listIdentifiers();
	$tpl->publish('form.tpl');
	exit();
}

// try to process to the installation
$error = require APP_PATH . '/setup/install/process.php';

if (!empty($error))
{
	$tpl->error = $error;
	$tpl->data  = Filter::array_to_object($post);
	$tpl->publish('fail.tpl');
}
else
{
	$tpl->installed = true;
	$tpl->public    = $base_url . 'public/';
	$tpl->publish('success.tpl');
}