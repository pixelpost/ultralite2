<?php

namespace pixelpost\plugins\admin\classes;

use pixelpost\core\Template,
	pixelpost\core\Config,
	pixelpost\core\Plugin as Plug;

/**
 * PluginManager provides the needed plugins collections for all plugin
 * management operations.
 *
 * @see pixelpost\plugins\admin\classes\Plugin
 * @see pixelpost\plugins\admin\classes\PluginList
 */
class PluginManager
{
	protected $all = array();
	protected $new = array();

	/**
	 * Create a new plugin manager.
	 */
	public function __construct()
	{
		$this->all = $detected = (array) Config::create()->plugins;

		if (Plug::detect())
		{
			$this->all = (array) $conf->plugins;
			$this->new = array_diff_key($this->all, $detected);
		}
	}

	/**
	 * Return if uninstalled plugins are detected.
	 *
	 * @return bool
	 */
	public function is_new()
	{
		return (bool) count($this->new);
	}

	/**
	 * Return a plugin collection of all plugins installed or not.
	 *
	 * @return pixelpost\plugins\admin\classes\PluginList
	 */
	public function all()
	{
		return new PluginList(array_keys($this->all));
	}

	/**
	 * Return a plugin collection of all new plugins.
	 *
	 * @return pixelpost\plugins\admin\classes\PluginList
	 */
	public function news()
	{
		return new PluginList(array_keys($this->new));
	}
}
