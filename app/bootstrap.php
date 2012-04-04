<?php

// Step 0. Hello world, here is the entry point
namespace pixelpost;

use pixelpost\core\Config,
	pixelpost\core\Filter,
	pixelpost\core\Plugin,
	pixelpost\core\Request,
	pixelpost\core\Event;

// Step 1. A little bit of PHP conf
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

// Step 2. A little of constant creation
defined('VERSION')   or define('VERSION',   '0.0.1',                true);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__),       true);
defined('APP_PATH')  or define('APP_PATH',  ROOT_PATH . '/app',     true);
defined('CORE_PATH') or define('CORE_PATH', APP_PATH  . '/core',    true);
defined('PLUG_PATH') or define('PLUG_PATH', APP_PATH  . '/plugins', true);
defined('PRIV_PATH') or define('PRIV_PATH', ROOT_PATH . '/private', true);
defined('LOG_FILE')  or define('LOG_FILE',  PRIV_PATH . '/log',     true);

// Step 3. A little of error handling
set_error_handler(function ($errno, $errstr, $errfile, $errline)
{
	throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function ($exception)
{
	$debug = !defined('DEBUG') or DEBUG;

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

// Step 4. We need a cool autoloader
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

// Step 5. We need to parse the config file and set properly the environnement
$conf = Config::load(PRIV_PATH . '/config.json');

// Debug can also be switched on by setting the Apache envrionment
// variable `APPLICATION_ENV` to `development` in .htaccess
$debug = ($conf->debug or 'development' == getenv('APPLICATION_ENV'));

defined('DEBUG')       or define('DEBUG',       $debug,                      true);
defined('PROCESS_ID')  or define('PROCESS_ID',  uniqid(),                    true);
defined('WEB_URL')     or define('WEB_URL',     $conf->url,                  true);
defined('CONTENT_URL') or define('CONTENT_URL', $conf->url . 'app/plugins/', true);

DEBUG or  error_reporting(0);
DEBUG and assert_options(ASSERT_ACTIVE, true);

date_default_timezone_set($conf->timezone);

assert('pixelpost\core\Log::info("(bootstrap) new process: %s", PROCESS_ID)');
assert('pixelpost\core\Log::debug("(bootstrap) VERSION: %s",     VERSION)');
assert('pixelpost\core\Log::debug("(bootstrap) ROOT_PATH: %s",   ROOT_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) APP_PATH: %s",    APP_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) CORE_PATH: %s",   CORE_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) PLUG_PATH: %s",   PLUG_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) PRIV_PATH: %s",   PRIV_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) WEB_URL: %s",     WEB_URL)');
assert('pixelpost\core\Log::debug("(bootstrap) CONTENT_URL: %s", CONTENT_URL)');

// Step 6. Check auto update if needed
if (Filter::compare_version($conf->version, VERSION))
{
	assert('pixelpost\core\Log::info("(bootstrap) upgrade detected")');

	require_once APP_PATH . '/update.php';
}

// Step 7. Registers activated plugins
assert('pixelpost\core\Log::info("(bootstrap) plugin registration")');

Plugin::make_registration();

// Step 8. We need to parse the incoming request
assert('pixelpost\core\Log::info("(bootstrap) Web request creation")');

$request = Request::create()->set_userdir($conf->userdir)->auto();

// Step 9. We just said we have a new request ! Enjoy :)
assert('pixelpost\core\Log::info("(bootstrap) Handle %s", $request->get_request_url())');

$event = Event::signal('request.new', array('request' => $request));

assert('pixelpost\core\Log::info("(bootstrap) Terminated")');
