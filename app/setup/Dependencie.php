<?php

/**
 * Create the plugin order
 */
class DependencieManager
{
	private $plugins = array();
	private $temp    = array();

	/**
	 * Create the Dependencie Manager, The argument is an array contains the 
	 * list of plugin
	 * 
	 * @param array $plugins 
	 */
	public function __construct(array $plugins)
	{
		foreach($plugins as $plugin) $this->_add($plugin);		
	}

	/**
	 * Generate the ordered list of plugins, the first one will be installed in
	 * first.
	 * 
	 * @return array
	 */
	public function process()
	{
		// start to set a priority to each plugin
		// each plugin increase in cascade the priority of required plugin
		foreach($this->plugins as $priority) $priority->set_priority();

		// we retrieve the final priority of each plugin (highter is more 
		// prioritary)
		$this->temp = array();

		foreach($this->plugins as $plugin => $priority) 
		{
			$this->temp[$plugin] = $priority->priority;
		}
		
		// we order that
		arsort($this->temp);		
		
		// and return the final ordered list
		return array_keys($this->temp);
	}

	/**
	 * Register a plugin, add him a default priority and retrieve the plugins
	 * it require.
	 * 
	 * @param string $pluginName 
	 */
	private function _add($pluginName)
	{
		// if we haven't already register this plugin
		if (!isset($this->plugins[$pluginName]))
		{
			// retrieve the plugin required for it.
			$requires = pixelpost\Plugin::get_dependencies($pluginName);
			
			// create the object hold that
			$this->plugins[$pluginName] = new DependenciePriority();
			$this->plugins[$pluginName]->name = $pluginName;
			
			// for each plugin required we add it to the object ...
			foreach(array_keys($requires) as $p)
			{
				// ... add it only if the required plugin have himself 
				// a DependenciePriority object ...
				if (isset($this->plugins[$p]))
				{
					$this->plugins[$pluginName]->requires[] = $this->plugins[$p];
				}
				// ... if not, we waiting for this object
				else
				{
					if (!isset($this->temp[$p])) $this->temp[$p] = array();
					
					$this->temp[$p][] = $pluginName;
				}
			}
		}
		
		// if other plugins are waiting for the this plugin
		// DependenciePriority object 
		if (isset($this->temp[$pluginName]))
		{
			// we add it now to them
			foreach($this->temp[$pluginName] as $p)
			{
				$this->plugins[$p]->requires[] = $this->plugins[$pluginName];
			}
		}		
	}
}


/**
 * This class is use to hold a plugin priority
 * And tell other linked plugins about it.
 */
class DependenciePriority
{
	public $name     = '';       // the plugin name
	public $priority = 0;        // it's priority highter is prior
	public $requires = array();  // it's dependencies (array of Priority class)
	
	/**
	 * Change the priority of a plugin
	 * 
	 * The arguments are only for internal use, don't use them.
	 */
	public function set_priority($priority = 1, $from = '')
	{
		// if we have this TRUE we return to start point so it's a infinite loo
		if ($from == $this->name) throw new Exception('Cyclic dependencies with ' . $from);
		
		// first call we register the sender
		if ($from == '') $from = $this->name;
		
		// if we actually have a lower priority we take a highter one...
		if ($this->priority < $priority) 
		{
			$this->priority = $priority;			
			
			// ... and we offer a more highter one to our dependencies
			foreach($this->requires as $d) 
			{
				$d->set_priority($this->priority + 1, $from);
			}
		}
	}
}
