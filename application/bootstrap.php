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


// Initialize Autoloader
require_once 'classes/class_loader.php';
spl_autoload_register(array('Loader','autoload'));


// Search directories:
Loader::scan();


$controller = Loader::find('controller');

$controller = new $controller;

var_dump($controller);

$template = Loader::find('template',true);
Loader::load($template);

var_dump($template);

