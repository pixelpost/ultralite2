<?php
/**
 * Don't forget than parent script have to provide variables:
 *
 * @var $argc int                     Command line number of arguments
 * @var $argv array                   Command line arguments
 * @var $tpl  pixelpost\core\Template Empty template file
 */

use pixelpost\core\Template,
	pixelpost\core\Request,
	pixelpost\core\Filter;

// change the default template location
$tpl->set_template_path(APP_PATH . '/setup/tpl/cli/install');

// hello user :)
$tpl->publish('welcome.tpl');

// for more readable code
$read = function($template = null) use ($tpl)
{
	is_null($template) or $tpl->publish($template);

	return trim(fgets(STDIN));
};

// test all pixelpost requirement before installation
do
{
	$warnings = require APP_PATH . '/setup/install/tests.php';
	$retry    = count($warnings);

	if ($retry)
	{
		$tpl->warnings = $warnings;

		if (strtoupper($read('requirements.tpl')) == 'E') exit();
	}
}
while($retry);

// Ask some information
do
{
	$post = array(
		'url'      => $read('ask-url.tpl'),
		'title'    => $read('ask-title.tpl'),
		'username' => $read('ask-username.tpl'),
		'password' => md5($read('ask-password.tpl')),
		'email'    => $read('ask-email.tpl'),
		'timezone' => ini_get('date.timezone'),
	);

	if (substr($post['url'], 0, 4) != 'http')
	{
		$post['url'] = 'http://' . $post['url'];
	}

	$tpl->assign($post);
}
while(strtoupper($read('resume.tpl')) == 'N');

// Load a http request for the process.php script
$request = Request::create()->set_url($post['url'])->set_post($post);

// try to process to the installation
do
{
	$error = require APP_PATH . '/setup/install/process.php';
	$retry = !empty($error);

	if ($retry)
	{
		$tpl->error = $error;

		if (strtoupper($read('fail.tpl')) == 'E') exit();
	}
}
while($retry);

$tpl->publish('success.tpl');
