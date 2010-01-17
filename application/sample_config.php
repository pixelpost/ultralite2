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
  'db_host' => 'localhost',
  'db_username' => '',
  'db_password' => '',
  'db_name' => 'pixelpost.sqlite3',
  'db_prefix' => 'pixelpost_',
  'db_type' => 'sqlite',
  'title' => 'Example Photoblog',
  'description' => 'Example Description',
  'copyright' => '(c) Your Name, All Rights Reserved',
  'url' => 'http://example.com/pixelpost/',
  'language' => 'en',
  'email' => 'user@example.com',
  'template' => 'simple',
  'timezone' => 'America/New_York',
  'default' => 'post',
  'plugins' => 
  array (
    0 => 'example',
  ),
);
