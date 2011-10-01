<?php

error_reporting(E_ALL | E_STRICT);

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

defined('VERSION')   or define('VERSION',   "0.0.1",                     true);
defined('SEP')       or define('SEP',       DIRECTORY_SEPARATOR,         true);
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__),            true);
defined('APP_PATH')  or define('APP_PATH',  ROOT_PATH . SEP . 'app',     true);
defined('CORE_PATH') or define('CORE_PATH', APP_PATH  . SEP . 'core',    true);
defined('PLUG_PATH') or define('PLUG_PATH', APP_PATH  . SEP . 'plugins', true);
defined('PRIV_PATH') or define('PRIV_PATH', ROOT_PATH . SEP . 'private', true);

spl_autoload_register(function($className)
{
    // the main namespace, all other is ignored
    $nsPrefix = 'pixelpost\\';
	
	// we need to keep $className, so we work on $class
	$class    = $className;

    // some security checking
    if (strpos($class, '/') !== false) return false;
    if (strpos($class, '.') !== false) return false;
    
    // remove the beginning backslash
    if (substr($class, 0, 1) == '\\') $class = substr($class, 1);
    
    // check if the class start with the main namespace
    if (substr($class, 0, strlen($nsPrefix)) != $nsPrefix) return false;

    // remove the main namespace of the class name
    $class = substr($class, strlen($nsPrefix));

    // get all parts of the class name
    $items = explode('\\', $class);

    // extract the file name
    $class = array_pop($items);

    // create the related path
    $path = (count($items) == 0) ? 'Core' : implode('\\', $items);  
    
    // create the absolute path with the complete file name
    $file = APP_PATH . SEP . str_replace('\\', SEP, $path) . SEP . $class . '.php';

    // check if file exists...
    if ( ! is_file($file)) return false;

    // include him...
    require_once $file;
    
    // return if the class is loaded...
    return class_exists($className);
});
