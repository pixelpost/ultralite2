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
class Plugin
{
	const NS_SEP            = '\\';
	const NS                = 'plugins';
	const PLUG_CLASS        = 'Plugin';
	const PLUG_FILE         = 'plugin.php';
	const PLUG_IFACE        = '\pixelpost\PluginInterface';

	const STATE_UNINSTALLED = 'uninstalled';
	const STATE_INACTIVE    = 'inactive';
	const STATE_ACTIVE      = 'active';

	/**
	 * Return the actual state of a plugins, the state is directly read from the
	 * configuration file.
	 *
	 * @param string $plugin The plugin name
	 * @return string
	 */
	public static function get_state($plugin)
	{
		Filter::is_string($plugin);

		$conf = Config::create();

		if (!isset($conf->plugins[$plugin]))
		{
			self::set_state($plugin, self::STATE_UNINSTALLED);
		}

		return $conf->plugins[$plugin];
	}

	/**
	 * Change the state of a plugin, the configuration file is directly written,
	 * return TRUE if writing the config file is a success else FALSE.
	 *
	 * @throws Error
	 * @param string $plugin The plugin name
	 * @param string $state  The new state of the plugin
	 * @return bool
	 */
	public static function set_state($plugin, $state)
	{
		Filter::is_string($plugin);
		Filter::is_string($state);

		switch ($state)
		{
			case self::STATE_UNINSTALLED : break;
			case self::STATE_INACTIVE    : break;
			case self::STATE_ACTIVE      : break;
			default : throw new Error(6, array($state));
		}

		$conf = Config::create();
		$conf->plugins[$plugin] = $state;

		return $conf->save();
	}

	/**
	 * Return the file uri which contains the main plugin class.
	 *
	 * @param string $plugin The plugin name
	 * @return string
	 */
	public static function get_file($plugin)
	{
		Filter::is_string($plugin);

		return PLUG_PATH . SEP . $plugin . SEP . self::PLUG_FILE;
	}

	/**
	 * Return the full class name (namespace include) of the main plugin class.
	 *
	 * @param string $plugin The plugin name
	 * @return string
	 */
	public static function get_class($plugin)
	{
		Filter::is_string($plugin);

		return __NAMESPACE__ . self::NS_SEP . self::NS 
		                     . self::NS_SEP . $plugin
		                     . self::NS_SEP . self::PLUG_CLASS;
	}

	/**
	 * Return the full method name (namespacedClassName::methodName) of a method
	 * of the main plugin class. (according the interface, methods should be
	 * defined).
	 *
	 * @param string $plugin The plugin name
	 * @return string
	 */
	public static function get_method($plugin, $method)
	{
		Filter::is_string($method);

		return self::get_class($plugin) . '::' . $method;
	}

	/**
	 * Check if a proposed plugins is valid. Check if the main file exists, main
	 * class exists, implements the plugins interface.
	 *
	 * @throws Error
	 * @param string $plugin The plugin name
	 */
	public static function validate($plugin)
	{
		require_once self::get_file($plugin);

		$class = self::get_class($plugin);

		if (!class_exists($class))
		{
			throw new Error(7, array($plugin, self::PLUG_CLASS, $class));
		}

		if (!array_key_exists(self::PLUG_IFACE, class_implements($class)))
		{
			throw new Error(8, array($plugin, self::PLUG_CLASS, self::PLUG_IFACE));
		}
	}

	/**
	 * Check if new plugins are present in the plugin folder. If yes, the
	 * plugins is added to list in state 'uninstalled'.
	 *
	 * @throws Error
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
			if (!is_dir($file) || $file == '.' || $file == '..')
				continue;

			if (!isset($conf->plugins[$file]) && self::validate($plugin))
			{
				$conf->plugins[$file] = self::STATE_UNINSTALLED;

				$isNewPlugin = true;
			}
		}

		closedir($rd);

		return $isNewPlugin;
	}

	/**
	 * Totaly remove a plugin (this method can be dangerous). Uninstall the
	 * plugins before delete it if it is installed.
	 *
	 * @throws Error
	 * @param string $plugin The plugin name
	 * @return bool
	 */
	public static function clean($plugin)
	{
		if (!self::uninstall($plugin))
			return false;

		$conf = Config::create();

		unset($conf->plugins[$plugin]);

		$conf->save();

		$rmrf = function($f) use (&$rmrf)
		{
			if (! file_exists($f))       return;
			if ($f == '.' || $f == '..') return;
			if (! is_dir($f))
			{
				unlink($f);
			}
			else
			{
				if (false === $rd = opendir($f)) throw new Error(9, array($f));

				while (false !== $file = readdir($rd)) $rmrf($file);

				closedir($rd);

				rmdir($f);
			}
		};

		$rmrf(PLUG_PATH . SEP . $plugin);

		return true;
	}

	/**
	 * Call the method 'register' of the plugin. Return FALSE in case of
	 * problem with the registration.
	 *
	 * @param string $plugin The plugin name
	 * @return bool
	 */
	public static function register($plugin)
	{
		require_once self::get_file($plugin);

		return call_user_func(self::get_method($plugin, 'register'));
	}

	/**
	 * Call the method 'update' of the plugin. Return FALSE is case of problem
	 * with the update.
	 * The plugins is inactived (if it is active) before the update and
	 * re-actived if needed.
	 *
	 * @param string $plugin The plugin name
	 * @return bool
	 */
	public static function update($plugin)
	{
		$isUpgraded = true;

		$state = self::get_state($plugin);

		if ($state == self::STATE_ACTIVE)
		{
			self::inactive($plugin);
		}

		if ($state == self::UNINSTALLED)
		{
			$isUpgraded = self::install($plugin);
		}
		else
		{
			$isUpgraded = call_user_func(self::get_method($plugin, 'update'));
		}

		if ($isUpgraded && $state == self::STATE_ACTIVE)
		{
			self::active($plugin);
		}

		return $isUpgraded;
	}

	/**
	 * Call the method 'version' of the plugin and return it's version number.
	 *
	 * @param string $plugin The plugin name
	 * @return string
	 */
	public static function version($plugin)
	{
		return call_user_func(self::get_method($plugin, 'version'));
	}

	/**
	 * Call the method 'install' of the plugin. The state of the plugin go from
	 * 'uninstalled' to 'inactive'. Return FALSE in case of problem with the
	 * installation.
	 *
	 * @param string $plugin The plugin name
	 * @return bool
	 */
	public static function install($plugin)
	{
		if (self::get_state($plugin) != self::STATE_UNINSTALLED)
			return true;

		if (call_user_func(self::get_method($plugin, 'install')))
		{
			self::set_state($plugin, self::STATE_INACTIVE);
			return true;
		}
		else
		{
			self::set_state($plugin, self::STATE_UNINSTALLED);
			return false;
		}
	}

	/**
	 * Call the method 'uninstall' of the plugin. The state of the plugin goes
	 * from 'ACTIVE/INACTIVE' to 'UNINSTALLED'. The plugin is inactived if
	 * necesseray. Return FALSE in case of problem with the uninstallation.
	 *
	 * @param string $plugin The plugin name
	 * @return bool
	 */
	public static function uninstall($plugin)
	{
		if (self::get_state($plugin) == self::STATE_UNINSTALLED)
			return true;

		self::inactive($plugin);

		if (call_user_func(self::get_method($plugin, 'uninstall')))
		{
			self::set_state($plugin, self::STATE_UNINSTALLED);
			return true;
		}
		else
		{
			self::set_state($plugin, self::STATE_INACTIVE);
			return false;
		}
	}

	/**
	 * Activate a plugin, if the plugin is not installed yet, the install will
	 * being make. (oh my god is that english ?)
	 * Return TRUE if plugin is activated or FALSE in other case.
	 *
	 * @param string $plugin The plugin name
	 * @return bool
	 */
	public static function active($plugin)
	{
		$state = self::get_state($plugin);

		if ($state == self::STATE_UNINSTALLED)
		{
			if (!self::install($plugin))
				return false;
		}

		if ($state == self::STATE_INACTIVE)
		{
			self::set_state($plugin, self::STATE_ACTIVE);
		}

		return true;
	}

	/**
	 * Inactivate a plugin. If the plugin is active, it is just inactived, else
	 * if the plugin is unistalled, process to its installation. Return TRUE if
	 * the plugins finish in state actived, else FALSE.
	 *
	 * @param string $plugin The plugin name
	 * @return bool
	 */
	public static function inactive($plugin)
	{
		$state = self::get_state($plugin);

		if ($state == self::STATE_ACTIVE)
		{
			self::set_state($plugin, self::STATE_INACTIVE);
			return true;
		}

		if ($state == self::STATE_UNINSTALLED)
		{
			return self::install($plugin);
		}
	}

	/**
	 * make the registration of all registred plugin before the website
	 * coming in action.
	 *
	 */
	public static function make_registration()
	{
		foreach (Config::create()->plugins as $plugin => $status)
		{
			if ($status != self::STATE_ACTIVE)
				continue;

			self::register($plugin);
		}
	}

}
