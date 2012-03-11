<?php

namespace pixelpost\core;

use RecursiveIteratorIterator  as RII,
	RecursiveDirectoryIterator as RDI;

/**
 * Plugin support
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class Plugin
{
	const NS                = 'pixelpost\plugins';
	const PLUG_CLASS        = 'Plugin';
	const PLUG_FILE         = 'Plugin.php';
	const PLUG_IFACE        = 'pixelpost\core\PluginInterface';

	const STATE_UNINSTALLED = 'uninstalled';
	const STATE_INACTIVE    = 'inactive';
	const STATE_ACTIVE      = 'active';

	protected static $_error = '';

	/**
	 * Return the last error message
	 *
	 * @return string
	 */
	public static function get_last_error()
	{
		$error = static::$_error;

		static::$_error = '';

		return $error;
	}

	/**
	 * Return if a plugins is detected
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin
	 * @return bool
	 */
	public static function is_exists($plugin)
	{
		Filter::is_string($plugin);

		return isset(Config::create()->plugins->$plugin);
	}

	/**
	 * Return the actual state of a plugins, the state is directly read from the
	 * configuration file.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return string
	 */
	public static function get_state($plugin)
	{
		Filter::is_string($plugin);

		$conf = Config::create();

		if (!isset($conf->plugins->$plugin))
		{
			static::set_state($plugin, static::STATE_UNINSTALLED);
		}

		return $conf->plugins->$plugin;
	}

	/**
	 * Change the state of a plugin, the configuration file is directly written,
	 * return TRUE if writing the config file is a success else FALSE.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @param  string $state  The new state of the plugin
	 * @return bool
	 */
	public static function set_state($plugin, $state)
	{
		Filter::is_string($plugin);
		Filter::is_string($state);

		switch ($state)
		{
			case static::STATE_UNINSTALLED : break;
			case static::STATE_INACTIVE    : break;
			case static::STATE_ACTIVE      : break;
			default                        : throw new Error(6, array($state));
		}

		$conf = Config::create();
		$conf->plugins->$plugin = $state;

		return $conf->save();
	}

	/**
	 * Return the file uri which contains the main plugin class.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return string
	 */
	public static function get_file($plugin)
	{
		Filter::is_string($plugin);

		return PLUG_PATH . SEP . $plugin . SEP . static::PLUG_FILE;
	}

	/**
	 * Return the full class name (namespace include) of the main plugin class.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return string
	 */
	public static function get_class($plugin)
	{
		Filter::is_string($plugin);

		return static::NS . '\\' . $plugin . '\\' . static::PLUG_CLASS;
	}

	/**
	 * Return the full method name (namespacedClassName::methodName) of a method
	 * of the main plugin class. (according the interface, methods should be
	 * defined).
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return string
	 */
	public static function get_method($plugin, $method)
	{
		Filter::is_string($method);

		return static::get_class($plugin) . '::' . $method;
	}

	/**
	 * Check if a proposed plugins is valid. Check if the main file exists, main
	 * class exists, implements the plugins interface.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return bool
	 */
	public static function validate($plugin)
	{
		require_once static::get_file($plugin);

		$class = static::get_class($plugin);

		if (!class_exists($class))
		{
			throw new Error(7, array($plugin, static::PLUG_CLASS, $class));
		}

		if (!array_key_exists(static::PLUG_IFACE, class_implements($class)))
		{
			throw new Error(8, array($plugin, static::PLUG_CLASS, static::PLUG_IFACE));
		}

		return true;
	}

	/**
	 * Check if new plugins are present in the plugin folder. If yes, the
	 * plugins is added to list in state 'uninstalled'.
	 *
	 * @throws pixelpost\core\Error
	 * @return bool
	 */
	public static function detect()
	{
		$isNewPlugin = false;

		$conf = Config::create();

		if (false === $rd = opendir(PLUG_PATH))
		{
			throw new Error(9, array(PLUG_PATH));
		}

		while (false !== $file = readdir($rd))
		{
			$f = PLUG_PATH . SEP . $file;

			if (!is_dir($f) || $file == '.' || $file == '..') continue;

			if (!isset($conf->plugins->$file) && static::validate($file))
			{
				$conf->plugins->$file = static::STATE_UNINSTALLED;

				$isNewPlugin = true;
			}
		}

		closedir($rd);

		$conf->save();

		return $isNewPlugin;
	}

	/**
	 * Totaly remove a plugin (this method can be dangerous). Uninstall the
	 * plugins before delete it if it is installed.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return bool
	 */
	public static function clean($plugin)
	{
		if (!static::uninstall($plugin)) return false;

		$conf = Config::create();

		unset($conf->plugins->$plugin);

		$conf->save();

		$path = PLUG_PATH . SEP . $plugin;

		foreach(new RII(new RDI($path), RII::CHILD_FIRST) as $file)
		{
			$method = $file->isDir() ? "rmdir" : 'unlink';
			$method($file->getPathName());
		}

		rmdir($path);

		return true;
	}

	/**
	 * Call the method 'register' of the plugin. Return FALSE in case of
	 * problem with the registration.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return bool
	 */
	public static function register($plugin)
	{
		require_once static::get_file($plugin);

		return call_user_func(static::get_method($plugin, 'register'));
	}

	/**
	 * Call the method 'update' of the plugin. Return FALSE is case of problem
	 * with the update.
	 * The plugins is inactived (if it is active) before the update and
	 * re-actived if needed.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return bool
	 */
	public static function update($plugin)
	{
		$isUpgraded = true;

		$state = static::get_state($plugin);

		if ($state == static::STATE_ACTIVE)
		{
			static::inactive($plugin);
		}

		if ($state == static::UNINSTALLED)
		{
			$isUpgraded = static::install($plugin);
		}
		else
		{
			$isUpgraded = call_user_func(static::get_method($plugin, 'update'));
		}

		if ($isUpgraded && $state == static::STATE_ACTIVE)
		{
			static::active($plugin);
		}

		return $isUpgraded;
	}

	/**
	 * Call the method 'version' of the plugin and return it's version number.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return string
	 */
	public static function version($plugin)
	{
		return call_user_func(static::get_method($plugin, 'version'));
	}

	/**
	 * Call the method 'install' of the plugin. The state of the plugin go from
	 * 'uninstalled' to 'inactive'. Return FALSE in case of problem with the
	 * installation.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return bool
	 */
	public static function install($plugin)
	{
		$state = static::get_state($plugin);

		if ($state != static::STATE_UNINSTALLED) return true;

		if (!static::check_dependencies($plugin)) return false;

		if (!call_user_func(static::get_method($plugin, 'install'))) return false;

		static::set_state($plugin, static::STATE_INACTIVE);

		return true;
	}

	/**
	 * Call the method 'uninstall' of the plugin. The state of the plugin goes
	 * from 'ACTIVE/INACTIVE' to 'UNINSTALLED'. The plugin is inactived if
	 * necesseray. Return FALSE in case of problem with the uninstallation.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return bool
	 */
	public static function uninstall($plugin)
	{
		$state = static::get_state($plugin);

		if ($state == static::STATE_UNINSTALLED) return true;

		if (!static::inactive($plugin)) return false;

		if (!call_user_func(static::get_method($plugin, 'uninstall'))) return false;

		static::set_state($plugin, static::STATE_UNINSTALLED);

		return true;
	}

	/**
	 * Activate a plugin, if the plugin is not installed yet, the install will
	 * being make. (oh my god is that english ?)
	 * Return TRUE if plugin is activated or FALSE in other case.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return bool
	 */
	public static function active($plugin)
	{
		$state = static::get_state($plugin);

		if ($state == static::STATE_ACTIVE) return true;

		if (!static::install($plugin)) return false;

		if (!static::check_dependencies($plugin)) return false;

		static::set_state($plugin, static::STATE_ACTIVE);

		return true;
	}

	/**
	 * Inactivate a plugin. If the plugin is active, it is just inactived, else
	 * if the plugin is unistalled, process to its installation. Return TRUE if
	 * the plugins finish in state actived, else FALSE.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin The plugin name
	 * @return bool
	 */
	public static function inactive($plugin)
	{
		$state = static::get_state($plugin);

		if ($state == static::STATE_INACTIVE) return true;

		if (!static::install($plugin))        return false;

		if (!static::check_required($plugin)) return false;

		static::set_state($plugin, static::STATE_INACTIVE);

		return true;
	}

	/**
	 * make the registration of all registred plugin before the website
	 * coming in action.
	 *
	 * @throws pixelpost\core\Error
	 */
	public static function make_registration()
	{
		foreach (Config::create()->plugins as $plugin => $status)
		{
			if ($status != static::STATE_ACTIVE) continue;

			static::register($plugin);
		}
	}

	/**
	 * Retrieve dependencies of a plugin
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin
	 * @return array
	 */
	public static function get_dependencies($plugin)
	{
		return call_user_func(static::get_method($plugin, 'depends'));
	}

	/**
	 * Verify if a plugin need another plugin to be installed/actived
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin
	 * @return bool
	 */
	public static function check_dependencies($plugin)
	{
		if (static::get_state($plugin) != static::STATE_UNINSTALLED) return true;

		foreach(static::get_dependencies($plugin) as $dep => $version)
		{
			if (!static::is_exists($dep))
			{
				static::$_error = "'$plugin' require plugin '$dep' >= '$version'.";
				return false;
			}

			if (static::get_state($dep) != static::STATE_ACTIVE)
			{
				static::$_error = "'$plugin' require plugin '$dep' to be active.";
				return false;
			}

			$v = call_user_func(static::get_method($dep, 'version'));

			if (Filter::compare_version($v, $version))
			{
				static::$_error = "'$plugin' require plugin '$dep' >= '$version'.";
				return false;
			}
		}

		return true;
	}

	/**
	 * Verify if a plugin is still required by another one to be
	 * inactived/uninstalled.
	 *
	 * @throws pixelpost\core\Error
	 * @param  string $plugin
	 * @return bool
	 */
	public static function check_required($plugin)
	{
		foreach(Config::create()->plugins as $plug => $state)
		{
			if ($state != static::STATE_ACTIVE) continue;

			$deps = static::get_dependencies($plug);

			if (isset($deps[$plugin]))
			{
				static::$_error = "'$plugin' is required by '$plug'.";
				return false;
			}
		}

		return true;
	}
}
