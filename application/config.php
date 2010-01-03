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
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'ultralite',
    'prefix' => '',
    'sqlite' => './application/pixelpost.sqlite3',
    'adapter' => 'sqlite',
  ),
  'site_name' => 'My Ultralite Photoblog',
  'site_description' => 'Guess what, it\'s open source, and it\'s ultralite!',
  'copyright' => '(c) 2009 Pixelpost',
  'url' => 'http://localhost/ultralite/',
  'email' => 'user@domain.com',
  'locale' => 'EN',
  'theme' => 'greyspace_neue',
  'posts_per_page' => 5,
  'feed_items' => 5,
  'feed_pagination' => true,
  'timezone' => 'America/Chicago',
  'enabled_plugins' => 
  array (
    // 0 => 'Example',
    // 1 => 'comment',
    // 2 => 'media_rss',
    // 3 => 'markdown',
    // 4 => 'smartypants',
    // 5 => 'metadata',
    // 6 => 'category',
    // 7 => 'tag',
  ),
  'default_comment_status' => 'denied',
  'allowed_comment_html' => 
  array (
    0 => 'strong',
    1 => 'em',
    2 => 'blockquote',
    3 => 'code',
    4 => 'pre',
    5 => 'a',
  ),
  'logging' => 
  array (
    'log_handler' => 'file',
    'log_file' => 'application/pixelpost.log',
    'log_level' => 999,
  ),
  'default_controller' => 'Post',
  'default_action' => 'indexAction',
  'error_controller' => 'Error',
  'static_controller' => 'Static',
)

?>