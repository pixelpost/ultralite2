<?php defined('APPPATH') or die('No direct script access.');

/**
 * Welcome to the Ultralite configuration file.
 * Here you can customize your photoblog with ease!
 * 
 * Just scroll down to see what you can change, 
 * and save the changes once you're done.
 * 
 * One thing to keep in mind, this file will be 
 * overwritten by Ultralite if you change your 
 * settings via the web admin.
 **/

return array (
  'database' => 
  array (
    'host' => '',
    'username' => '',
    'password' => '',
    'database' => 'pixelpost.sqlite3',
    'prefix' => '',
    'adapter' => 'sqlite',
  ),
  'site_name' => 'My Ultralite Photoblog',
  'site_description' => 'Guess what, it\'s open source, and it\'s ultralite!',
  'copyright' => '(c) 2009 Pixelpost',
  'url' => 'http://localhost/ultralite2/',
  'email' => 'user@domain.com',
  'language' => 'en',
  'theme' => 'simple',
  'filter' => 'markdown',
  'per_page' => 5,
  'feed_items' => 5,
  'feed_pagination' => true,
  'timezone' => 'America/Chicago',
  'enabled_plugins' => 
  array (
    // 0 => 'example',
  ),
  'default_controller' => 'Post',
  'default_action' => 'indexAction',
)

?>