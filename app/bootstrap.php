<?php

// Hello world, here is the entry point
namespace pixelpost;

// A little bit of PHP conf
error_reporting(-1);
assert_options(ASSERT_ACTIVE, false);

ini_set('date.timezone',              'UTC');
ini_set('default_socket_timeout',     '10');
ini_set('default_mimetype',           'text/html');
ini_set('default_charset',            'UTF-8');
ini_set('mbstring.language',          'neutral');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('mbstring.http_output',       'pass');
ini_set('allow_url_include',          'off');
ini_set('short_open_tag',             'off');
ini_set('html_errors',                'off');
ini_set('display_errors',             'stdout');

// A little of constant creation
defined('VERSION')   or define('VERSION',   '0.0.1',                true);
defined('PHAR')      or define('PHAR',      false,                  true);
defined('CLI')       or define('CLI',       PHP_SAPI == 'cli',      true);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__),       true);
defined('APP_PATH')  or define('APP_PATH',  ROOT_PATH . '/app',     true);
defined('CORE_PATH') or define('CORE_PATH', APP_PATH  . '/core',    true);
defined('PLUG_PATH') or define('PLUG_PATH', APP_PATH  . '/plugins', true);
defined('PUB_PATH')  or define('PUB_PATH',  ROOT_PATH . '/public',  true);
defined('PRIV_PATH') or define('PRIV_PATH', ROOT_PATH . '/private', true);
defined('LOG_FILE')  or define('LOG_FILE',  PRIV_PATH . '/log',     true);
defined('CONF_FILE') or define('CONF_FILE', PRIV_PATH . '/config',  true);
defined('BOOT_FILE') or define('BOOT_FILE', PRIV_PATH . '/boot',    true);

// A little of error handling
set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
	throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function ($exception)
{
	$debug = (!defined('DEBUG') or DEBUG);

	if (class_exists('\pixelpost\core\Event'))
	{
		$event = \pixelpost\core\Event::signal('error.new', compact('exception'));

		if (!$event->is_processed() && $debug)
		{
			echo $exception;
		}
	}
	elseif ($debug)
	{
		echo $exception;
	}
});

// We need a cool autoloader (PSR-0 compatible)
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

	// finally load the class file and return the autoloding status
	is_file($file) and require_once $file;

	return class_exists($name);
});
