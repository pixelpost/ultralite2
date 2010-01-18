<?php

// Initalize Pixelpost

// Temporarily set the time zone, to prevent possible errors
date_default_timezone_set('GMT');

define('DEBUG',true);

if (defined('DEBUG'))
	error_reporting(E_ALL|E_STRICT); // Development
else
	error_reporting(0); // Production

define('APPPATH', realpath(dirname(__FILE__)).'/');
define('CACHEPATH', realpath(dirname(__FILE__).'/../cache').'/');
define('CONTENTPATH', realpath(dirname(__FILE__).'/../content').'/');

// Load Helper & Config
require_once APPPATH.'classes/class_helper.php';
require_once APPPATH.'classes/class_error.php';
require_once APPPATH.'classes/class_config.php';

// By setting the include path, templates can simply call include('header.php');
// and it will include the template header file.
set_include_path(CONTENTPATH.'templates/'.Config::current()->template.'/' . PATH_SEPARATOR . APPPATH);

// Initialize Autoloader
require_once 'classes/class_loader.php';
spl_autoload_register(array('Loader','autoload'));

// Search directories:
Loader::scan();

// Load default page, if no page is specified
if (count(Uri::get()) < 1) 
	Uri::set(strtolower(Config::current()->default));

// Set the default time zone
date_default_timezone_set(Config::current()->timezone);

DB::init(Config::current()->db_type);

// Find and initialize the controller
$controller = Loader::find('controller');
$controller = new $controller;

// Display Page
echo $controller->indexAction(Uri::get());

