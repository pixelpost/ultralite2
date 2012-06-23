<?php

namespace pixelpost\plugins\admin;

use pixelpost\plugins\pixelpost\Plugin as PP,
	pixelpost\core\PluginInterface,
	pixelpost\core\Event;

/**
 * ADMIN for pixelpost.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin implements PluginInterface
{
	public static function version()
	{
		return '0.0.1';
	}

	public static function depends()
	{
		return array('pixelpost' => '0.0.1');
	}

	public static function install()
	{
		return true;
	}

	public static function uninstall()
	{
		return true;
	}

	public static function update()
	{
		return true;
	}

	public static function register()
	{
		$self = __CLASS__;
		$api  = __NAMESPACE__ . '\Api';
		$page = __NAMESPACE__ . '\Page';

		Event::register_list(array(
			// api events
			array('api.admin.version',          $api  . '::api_version'),
			// admin web interface
			array('http.admin',                 $self . '::admin_router'),
			array('admin.phpinfo',              $self . '::phpinfo'),
			array('admin.index',                $page . '::home'),
			array('admin.404',                  $page . '::404'),
			array('admin.settings',             $page . '::settings'),
			array('admin.settings.index',       $page . '::settings_index'),
			array('admin.settings.plugins',     $page . '::settings_plugins'),
			array('admin.settings.plugin',      $page . '::settings_plugin'),
			array('admin.settings.manage',      $page . '::settings_manage'),
			array('admin.settings.cache-flush', $page . '::settings_cache-flush'),
			array('admin.settings.plugin.admin',$page . '::about'),
			array('admin.template.nav',         $page . '::template_nav', 200),
			array('admin.template.widget',      $page . '::template_widget', 200),
		));

		DEBUG and Event::register('admin.template.nav', $page . '::template_nav_phpinfo', 201);
	}

	public static function phpinfo(Event $event)
	{
		if (DEBUG) phpinfo();
		else       $event->set_processed(false);
	}

	/**
	 * Treat a new request comming from event 'http.admin' and check the second
	 * part of the requested URL to find what admin page is asked for.
	 *
	 * This produce an event admin.* where * is replaced by the requested page.
	 * (ex: admin.index)
	 *
	 * In case of non response to an event admin.* the event admin.404 is thrown.
	 *
	 * @param  pixelpost\core\Event $event
	 * @return bool
	 */
	public static function admin_router(Event $event)
	{
		$event->set_name('admin');

		PP::route($event);

		if (!$event->is_processed())
		{
			$event->redirect('admin.404');
			return false;
		}

		$event->set_name('http.admin');
	}
}