<?php
// check the php version if we have 5.3.0 at least
// PHP_VERSION_ID exists since verison 5.2.7
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
{
	include './tpl/cli/php-min-version.tpl';
	exit();
}

require_once '../bootstrap.php';

// load command line args…
$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

// create a template…
$tpl = pixelpost\core\Template::create();
$tpl->set_cache_raw_template(false);
$tpl->set_template_path(APP_PATH . '/setup/tpl/cli');

// default command if none provided…
if ($argc == 1) $argv[1] = 'help';

// what to do…
switch ($argv[1])
{
	default:
	case 'help':    require APP_PATH . '/setup/cli/help.php';    break;
	case 'version': require APP_PATH . '/setup/cli/version.php'; break;
	case 'extract': require APP_PATH . '/setup/cli/extract.php'; break;
	case 'install': require APP_PATH . '/setup/cli/install.php'; break;
}
