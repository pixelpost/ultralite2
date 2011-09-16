<?php

namespace pixelpost;

/**
 * Plugin support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
interface PluginInterface
{

	/**
	 * This method provide the actual plugin version number in a string
	 *
	 * @return string
	 */
	public static function version();

	/**
	 * This action is executed each time when a request is executed on the
	 * entire application and the plugin is in active state, so keep simple.
	 *
	 * @return NULL
	 */
	public static function register();

	/**
	 * This action is executed once when the plugins is actived.
	 *
	 * @return bool
	 */
	public static function install();

	/**
	 * This action is executed once when the plugins is inactived.
	 *
	 * @return bool
	 */
	public static function uninstall();

	/**
	 * This action is executed once when the plugins is updated.
	 *
	 * @return bool
	 */
	public static function update();

}
