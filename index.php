<?php

// Step 0. Hello world, here is the entry point
namespace pixelpost;

// Step 1. A little of PHP conf
error_reporting(E_ALL|E_STRICT);

ini_set('date.timezone',                 'GMT');
ini_set('default_socket_timeout',        '10');
ini_set('default_mimetype',              'text/html');
ini_set('default_charset',               'UTF-8');
ini_set('mbstring.internal_encoding',    'UTF-8');
ini_set('mbstring.http_input',           'UTF-8');
ini_set('mbstring.http_output',          'UTF-8');
ini_set('mbstring.func_overload',        '7');
ini_set('mbstring.encoding_translation', 'on');
ini_set('asp_tags',                      'off');
ini_set('allow_url_fopen',               'off');
ini_set('allow_url_finclude',            'off');
ini_set('file_uploads',                  'off');
ini_set('register_argc_argv',            'off');
ini_set('register_long_arrays',          'off');
ini_set('safe_mode',                     'off');
ini_set('short_open_tag',                'off');
ini_set('magic_quotes_gpc',              'off');

// Step 2. A little of constant creation
defined('SEP')       or define('SEP',       \DIRECTORY_SEPARATOR,         true);
defined('ROOT_PATH') or define('ROOT_PATH',  dirname(__FILE__) . SEP,    true);
defined('CORE_PATH') or define('CORE_PATH', ROOT_PATH . 'core' . SEP,    true);
defined('SHOT_PATH') or define('SHOT_PATH', ROOT_PATH . 'photos' . SEP,  true);
defined('PLUG_PATH') or define('PLUG_PATH', ROOT_PATH . 'plugins' . SEP, true);

// Step 3. A little of error handling
// set_error_handler(function ($errno, $errstr, $errfile, $errline)
// {
//     throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
// });
// 
// set_exception_handler(function ($exception)
// {
//     if (class_exists('\pixelpost\Event'))
//     {
//         \pixelpost\Event::signal('error.new', array($exception));
//     }
//     elseif (DEBUG)
//     {
//         echo $exception;
//     }
// });

// Step 4. We need to load the minimum to work
require_once CORE_PATH . 'Error.php';
require_once CORE_PATH . 'Config.php';
require_once CORE_PATH . 'Event.php';
require_once CORE_PATH . 'Request.php';
require_once CORE_PATH . 'Plugin.php';
require_once CORE_PATH . 'Db.php';
require_once CORE_PATH . 'Filter.php';
require_once CORE_PATH . 'Photo.php';
require_once CORE_PATH . 'PluginInterface.php';
require_once PLUG_PATH . 'router/plugin.php';

// Step 5. We need to parse the config file and set properly the environnement
$conf = Config::load(ROOT_PATH . 'config');

defined('DEBUG')   or define('DEBUG',   $conf->debug, true);
defined('WEB_URL') or define('WEB_URL', $conf->url,   true);
defined('API_URL') or define('API_URL', $conf->api,   true);
defined('ADM_URL') or define('ADM_URL', $conf->admin, true);

DEBUG or error_reporting(0);

date_default_timezone_set($conf->timezone);

// Step 6. Registers activated plugins
Plugin::make_registration();

// Step 7. We need to parse the incoming request
$request = Request::create()->set_userdir($conf->userdir)->auto();

// Step 8. We just said we have a new request ! Enjoy :)
$event = Event::signal('request.new', array('request' => $request)); 



