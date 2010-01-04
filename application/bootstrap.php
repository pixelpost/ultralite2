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


require_once 'classes/class_uri.php';

Uri::getInstance();

// Uri::$clean_url=false;
var_dump(Uri::create());

var_dump(Uri::$parameters);
// var_dump(Uri::$uri);
