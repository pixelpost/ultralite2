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

// Load Helper & Config
require_once APPPATH.'classes/class_helper.php';
require_once APPPATH.'classes/class_config.php';
$config = Config::getInstance();

// Initialize Autoloader
require_once APPPATH.'classes/class_loader.php';
spl_autoload_register(array('Loader','autoload'));

// Search directories:
Loader::scan();

// Load default page, if no page is specified
if (isset($config->default) && count(Uri::get()) < 1) 
{
	Uri::set($config->default);
}

// Find and initialize controller
$controller = Loader::find('controller');
$controller = new $controller;

// Output Page
echo $controller->indexAction(Uri::get());

