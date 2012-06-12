<?php

namespace pixelpost\plugins\admin\classes;

use pixelpost\core\Filter,
	pixelpost\core\Config,
	pixelpost\core\Event,
	pixelpost\core\Plugin as Plug;

/**
 * Plugin class provide all needed to operate with a pixelpost plugin.
 * This class is mainly a facade to the pixelpost core class `pixelpost\core\Plugin`.
 *
 * @see pixelpost\core\Plugin
 * @see pixelpost\plugins\admin\classes\Plugin
 * @see pixelpost\plugins\admin\classes\PluginManager
 */
class Plugin
{
	/**
	 * @var string The plugin name
	 */
	protected $plugin;

	/**
	 * Create a new instance of plugin $plugin
	 *
	 * @param string $plugin a plugin name.
	 */
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * Return the plugin name when an instance is used as a function.
	 *
	 * @return string
	 */
	public function __invoke()
	{
		return $this->plugin;
	}

	/**
	 * Return the formatted plugin name when an instance is casted to a string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->name();
	}

	/**
	 * Return a fornatted name for the plugin.
	 * This is actually replace underscores by spaces.
	 *
	 * @return string
	 */
	public function name()
	{
		return str_replace('_', ' ', $this->plugin);
	}

	/**
	 * Return the plugin version number.
	 *
	 * @return string
	 */
	public function version()
	{
		return Plug::version($this->plugin);
	}

	/**
	 * Return the required plugin as an array like: `plugin => version`
	 *
	 * @return array
	 */
	public function dependencies()
	{
		return Plug::get_dependencies($this->plugin);
	}

	/**
	 * Return the plugin state. The state is a constant string, see the
	 * pixelpost core Plugin class constant:
	 *
	 * `pixelpost\core\Plugin::STATE_UNINSTALLED`
	 * `pixelpost\core\Plugin::STATE_INACTIVE`
	 * `pixelpost\core\Plugin::STATE_ACTIVE`
	 *
	 * @return string
	 */
	public function state()
	{
		return Plug::get_state($this->plugin);
	}

	/**
	 * Call and return content corresponding settings.plugin event.
	 * This method provide a way for a pixelpost plugin to return customized
	 * data about itself.
	 *
	 * Example for a plugin called: `bar` an event: `admin.settings.plugin.bar` is
	 * thrown. The destination of the result is the plugin datasheet admin page.
	 *
	 * @return string
	 */
	public function data()
	{
		$event_name = 'admin.settings.plugin.' . $this->plugin;
		$event_data = array('data' => '');

		return Event::signal($event_name, $event_data)->data;
	}

	/**
	 * Return if the plugin is protected
	 *
	 * @return bool
	 */
	public function is_protected()
	{
		return in_array($this->plugin, Config::create()->protected);
	}

	/**
	 * Return if the plugin is packaged
	 *
	 * @return bool
	 */
	public function is_packaged()
	{
		return in_array($this->plugin, Config::create()->packaged);
	}

	/**
	 * Return if the plugin is active
	 *
	 * @return bool
	 */
	public function is_active()
	{
		return Plug::get_state($this->plugin) == Plug::STATE_ACTIVE;
	}

	/**
	 * Return if the plugin is inactive
	 *
	 * @return bool
	 */
	public function is_inactive()
	{
		return Plug::get_state($this->plugin) == Plug::STATE_INACTIVE;
	}

	/**
	 * Return if the plugin is uninstalled
	 *
	 * @return bool
	 */
	public function is_uninstalled()
	{
		return Plug::get_state($this->plugin) == Plug::STATE_UNINSTALLED;
	}

	/**
	 * Do the active management operation on the plugin, return true if success.
	 *
	 * On error this method return false, you can call `error()`
	 * for more information about it.
	 *
	 * @return bool
	 */
	public function active()
	{
		return Plug::active($this->plugin);
	}

	/**
	 * Do the install management operation on the plugin, return true if success.
	 *
	 * On error this method return false, you can call `error()`
	 * for more information about it.
	 *
	 * @return bool
	 */
	public function install()
	{
		return Plug::install($this->plugin);
	}

	/**
	 * Do the inactive management operation on the plugin, return true if success.
	 *
	 * On error this method return false, you can call `error()`
	 * for more information about it.
	 *
	 * @return bool
	 */
	public function inactive()
	{
		return Plug::inactive($this->plugin);
	}

	/**
	 * Do the uninstall management operation on the plugin, return true if success.
	 *
	 * On error this method return false, you can call `error()`
	 * for more information about it.
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		return Plug::uninstall($this->plugin);
	}

	/**
	 * Do the clean management operation on the plugin, return true if success.
	 *
	 * On error this method return false, you can call `error()`
	 * for more information about it.
	 *
	 * @return bool
	 */
	public function clean()
	{
		return Plug::clean($this->plugin);
	}

	/**
	 * Return the last registred error during managment operation.
	 *
	 * @return string
	 */
	public function error()
	{
		return Plug::get_last_error();
	}
}