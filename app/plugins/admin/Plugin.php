<?php

namespace pixelpost\plugins\admin;

use pixelpost\plugins\router\Plugin as Router,
	pixelpost\core\PluginInterface,
	pixelpost\core\Event;

/**
 * ADMIN routers for pixelpost admin urls.
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
		return array('router' => '0.0.1');
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

		Event::register('request.admin',         $self . '::admin_router');

    	Event::register('api.admin.version',     $api  . '::api_version');

		Event::register('admin.index',           $page . '::page_index');
		Event::register('admin.404',             $page . '::page_404');
		Event::register('admin.api-test',        $page . '::page_api_test');
		Event::register('admin.phpinfo',         $self . '::phpinfo');
		Event::register('admin.template.widget', $page . '::template_widget', 200);

		DEBUG and Event::register('admin.template.nav', $page . '::template_nav_phpinfo', 200);
	}

	public static function phpinfo(Event $event)
	{
		if (DEBUG) phpinfo();
		else       $event->set_processed(false);
	}

	/**
	 * Treat a new request comming from event 'request.admin' and check the second
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

		Router::route($event);

		if (!$event->is_processed())
		{
			$event->redirect('admin.404');
			return false;
		}

		$event->set_name('request.admin');
	}
}