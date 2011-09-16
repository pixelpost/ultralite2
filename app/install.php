<?php

/**
 * Default Configuration Installation
 * 
 * A quick and dirty way to get things running.
 */

// Use Sample Config
if(!file_exists(PRIV_PATH . SEP . 'config.json'))
		copy(PRIV_PATH . SEP . 'config_sample.json', PRIV_PATH . SEP . 'config.json');


// Load Config
$conf = pixelpost\Config::load(PRIV_PATH . SEP . 'config.json');

// Detect Site URL
$url = '';
// scheme
$url .= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')? 'https://' : 'http://';
// host
$url .= $_SERVER['SERVER_NAME'];
// port
$url .= ($_SERVER['SERVER_PORT'] != 80)? ':' . $_SERVER['SERVER_PORT'] : '';
// path
$url .= substr($_SERVER['REQUEST_URI'] , 0, strrpos($_SERVER['REQUEST_URI'], '/') + 1);

// Set Site URL
$conf->url = $url;

// Set User Directory
$conf->userdir = 'ultraite2';

$conf->save();

if(!file_exists('.htaccess'))
	copy('app/htaccess_sample', '.htaccess');

if(!file_exists(PRIV_PATH . SEP . 'sqlite3.db'))
{
	// Install Photo Plugin & Databse
	require_once PLUG_PATH . SEP . 'photo' . SEP . 'Plugin.php';
	pixelpost\plugins\photo\Plugin::install();
}