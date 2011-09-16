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

/**
 * @todo Detect root URL, even if mod_rewrite is already installed
 * e.g. Installer runs while browsing /admin/
 */

// Set Site URL
$conf->url = $url;

// Set User Directory
$conf->userdir = 'ultraite2';

/**
 * FIXME: For some reason, saving the config breaks Ultralite, is there an encoding issue?
 */
$conf->save();

if(!file_exists(ROOT_PATH . SEP . '.htaccess'))
	copy(APP_PATH . SEP . 'htaccess_sample', ROOT_PATH . SEP .'.htaccess');

if(!file_exists(PRIV_PATH . SEP . '.htaccess'))
	copy(PRIV_PATH . SEP . 'htaccess_sample', PRIV_PATH . SEP .'.htaccess');

if(!file_exists(PRIV_PATH . SEP . 'sqlite3.db'))
{
	// Install Photo Plugin & Databse
	require_once PLUG_PATH . SEP . 'photo' . SEP . 'Plugin.php';
	pixelpost\plugins\photo\Plugin::install();
}