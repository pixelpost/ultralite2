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

Uri::getInstance();

// Uri::$clean_url=false;
// var_dump(Uri::create());

// var_dump(Uri::$parameters);

/**
 * File Scanner
 */
$scan = array();

// Controller Directories to Scan:
$scan['controller'][] = APPPATH.'controllers/';

// Class Directories to Scan:
$scan['class'][] = APPPATH.'classes/';

// Language Directories to Scan:
$scan['language'][] = APPPATH.'languages/';

// Page Directories to Scan:
$scan['page'][] = CONTENTPATH.'templates/simple/';

$files = array();
foreach ($scan as $section => $dirs) {
	foreach ($dirs as $dir) {
 		$matches = glob($dir.$section.'*.php');
		foreach ($matches as $file) {
			$files[$section][basename($file,'.php')] = $file;
		}
	}
}


var_dump($files);


/**
 * 
 * @todo also scan plugin folders
 */
function find($type=null)
{
	global $files;
	
	if (!array_key_exists($type,$files)) 
		return false;
	
	$keys = array_keys(Uri::get());
	array_unshift($keys,$type);
	$total = count($keys);

	for ($i=0; $i < $total; $i++) {
		$name = implode($keys,'_');
		if (array_key_exists($name,$files[$type])) {
			return $files[$type][$name];
		}
		array_pop($keys);
	}
}

$controller = find('controller');

include $controller;

var_dump($controller);

$page = find('page');

include $page;

var_dump($page);

