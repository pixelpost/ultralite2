<?php

namespace pixelpost;

error_reporting(-1);
assert_options(ASSERT_ACTIVE, false);

ini_set('date.timezone',                 'UTC');
ini_set('default_socket_timeout',        '10');
ini_set('default_mimetype',              'text/html');
ini_set('default_charset',               'UTF-8');
ini_set('mbstring.language',             'neutral');
ini_set('mbstring.internal_encoding',    'UTF-8');
ini_set('mbstring.http_output',          'pass');
ini_set('allow_url_include',             'off');
ini_set('short_open_tag',                'off');
ini_set('html_errors',                   'off');
ini_set('display_errors',                'stdout');

defined('VERSION')   or define('VERSION',   '0.0.1',                true);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__),       true);
defined('APP_PATH')  or define('APP_PATH',  ROOT_PATH . '/app',     true);
defined('CORE_PATH') or define('CORE_PATH', APP_PATH  . '/core',    true);
defined('PLUG_PATH') or define('PLUG_PATH', APP_PATH  . '/plugins', true);
defined('PUB_PATH')  or define('PUB_PATH',  ROOT_PATH . '/public',  true);
defined('PRIV_PATH') or define('PRIV_PATH', ROOT_PATH . '/private', true);
defined('LOG_FILE')  or define('LOG_FILE',  PRIV_PATH . '/log',     true);

spl_autoload_register(function($name)
{
	// some security checking
	if (strpos($name, '/') !== false) return false;
	if (strpos($name, '.') !== false) return false;

	// the main namespace, all other is ignored
	$ns  = __NAMESPACE__;
	$len = strlen($ns);

	// remove the beginning backslash
	$class = (substr($name, 0, 1) == '\\') ? substr($name, 1) : $name;

	// check if the class start with the main namespace
	if (substr($class, 0, $len) != $ns) return false;

	// remove the main namespace of the class name and apply psr-0
	$file = APP_PATH . str_replace('\\', '/', substr($class, $len)) . '.php';

	is_file($file) and require_once $file;

	return class_exists($name);
});
