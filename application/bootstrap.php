<?php

// Initalize Pixelpost

// Set the default time.
date_default_timezone_set('America/Chicago');

define('DEBUG',true);

if (defined('DEBUG'))
	error_reporting(E_ALL|E_STRICT); // Development
else
	error_reporting(0); // Production
	
define('APPPATH', realpath(dirname(__FILE__)).'/');
define('CACHEPATH', realpath(dirname(__FILE__).'/../cache').'/');
define('CONTENTPATH', realpath(dirname(__FILE__).'/../content').'/');

// var_dump(APPPATH,CACHEPATH,CONTENTPATH);

require_once 'classes/class_uri.php';

require_once 'classes/class_loader.php';

Uri::getInstance();

Loader::getInstance();
Loader::scan();

// Initialize Autoloader
spl_autoload_register(array('Loader','autoload'));


$controller = Loader::find('controller');
Loader::load($controller);

var_dump($controller);

$template = Loader::find('template');
Loader::load($template);

var_dump($template);

