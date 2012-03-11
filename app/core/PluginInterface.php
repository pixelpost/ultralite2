<?php

namespace pixelpost\core;

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
	 * This method provide the actual plugin version number in a string.
	 * The string will be formated like A.B.C:
	 *
	 * A is the major version (BC break possible)
	 * B is the minor version (Non BC beak)
	 * C is the bug fixes release version
	 *
	 * @return string
	 */
	public static function version();


	/**
	 * This method provide a list of required plugins. The list returned is
	 * an array with required plugin in key and version's plugin required
	 * in value.
	 *
	 * @return array
	 */
	public static function depends();

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
