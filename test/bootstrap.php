<?php

namespace pixelpost;

error_reporting(-1);

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

defined('VERSION')   or define('VERSION',   "0.0.1",                     true);
defined('SEP')       or define('SEP',       DIRECTORY_SEPARATOR,         true);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__),            true);
defined('APP_PATH')  or define('APP_PATH',  ROOT_PATH . SEP . 'app',     true);
defined('CORE_PATH') or define('CORE_PATH', APP_PATH  . SEP . 'core',    true);
defined('PLUG_PATH') or define('PLUG_PATH', APP_PATH  . SEP . 'plugins', true);
defined('PRIV_PATH') or define('PRIV_PATH', ROOT_PATH . SEP . 'private', true);

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
	$file = APP_PATH . str_replace('\\', SEP, substr($class, $len)) . '.php';

	is_file($file) and require_once $file;

	return class_exists($name);
});
