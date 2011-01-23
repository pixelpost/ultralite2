<?php

namespace pixelpost;

/**
 * Plugin support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/2.0/fr/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 2.0.0
 */
interface PluginInterface
{
	public static function register();

	public static function install();

	public static function uninstall();

	public static function version();

	public static function update();
}
