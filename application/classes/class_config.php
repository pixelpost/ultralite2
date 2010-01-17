<?php

/**
 * Holds all of the configuration variables for the entire site, as well as addon settings.
 * 
 * This class is based off of the work of Alex Suraci's Chyrp application.
 *
 * @package Pixelpost
 * @subpackage Config
 * @author Alex Suraci and individual contributors
 * @author Jay Williams
 */
class Config
{
	
	/**
	 * Config file path
	 *
	 * @var string
	 */
	private $file = "";
	
	private $changed = false;
	
	private $config = array();

	/**
	 * Initializes and loads the configuration file.
	 */
	private function __construct($config=null)
	{
		
		if ($config) {
			$this->config = $config;
			return true;
		}
		
		$this->file = APPPATH.'config.php';
		$this->load();
		
		if(empty($this->config))
			Error::quit(500, 'Configuration Missing?', 'Yup, it looks like the config file is empty or doesn\'t exist. So we\'re stuck until it gets created.');
		
		// Merge the defaults with the the config.
		$defaults                 = $this->defaults();
		$this->config             = array_merge($defaults, $this->config);

		$this->validate();
	}
	

	public function __get($property)
	{
		$self = self::current();
		
		if (!array_key_exists($property,$self->config))
			return null;
		
		return $self->config[$property];
	}

	public function __set($property, $value)
	{
		$self = self::current();
		
		if (array_key_exists($property,$self->config) && $self->config[$property] == $value)
			return true;
		
		$self->config[$property] = $value;
		
		// Make a note, so when we __destruct(), we can save the changes
		$self->changed = true;
		
		return true;
	}

	public function __isset($property)
	{
		$self = self::current();
		
		if (array_key_exists($property,$self->config))
			return true;
		
		return false;
	}

	public function __unset($property)
	{
		$self = self::current();
		
		if (array_key_exists($property,$self->config))
		{
			unset($self->config[$property]);
			
			// Make a note, so when we __destruct(), we can save the changes
			$self->changed = true;
		}
	}
	
	public function __destruct()
	{
		$self = self::current();
		
		if ($self->changed && !$self->store())
		{
			Error::quit(500, 'Unable to Save Config', 'Any changes that were made, weren\'t saved.');
		}
	}

	/**
	 * Returns a singleton reference to the current configuration.
	 *
	 * @return $instance
	 */
	public static function & current()
	{
		static $instance = null;
		
		return $instance = (empty($instance)) ? new self() : $instance;
	}
	
	/**
	 * Loads the configuration file.
	 */
	private function load()
	{
		
		if(file_exists($this->file)){
			$this->config = include $this->file;
			return true;
		}else
			return false;
	}
	
	/**
	 * Default Pixelpost Configuration
	 * 
	 * Pixelpost will use these values if the config file is missing an option.
	 *
	 * @return array
	 */
	protected function defaults()
	{
		return array (
		  'db_host' => 'localhost',
		  'db_username' => '',
		  'db_password' => '',
		  'db_name' => '',
		  'db_prefix' => 'pixelpost_',
		  'db_type' => '',
		  'title' => 'Untitled',
		  'description' => '',
		  'copyright' => '(c) All Rights Reserved',
		  'url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/',
		  'language' => 'en',
		  'email' => '',
		  'template' => 'simple',
		  'timezone' => 'GMT',
		  'default' => 'post',
		  'plugins' => 
		  array (
		  ),
		);
	}
	
	/**
	 * Verify that the config file is setup properly, and fix any potential problems.
	 */
	protected function validate()
	{
		
		if (!is_writable($this->file))
		{
			Error::quit(500, 'Ugh', 'The config file isn\'t writeable. Please check the file permissions.');
		}
		
		if (substr($this->config['url'], -1) != '/')
		{
			$this->config['url'] = $this->config['url'].'/';
		}
		
		if ($this->config['language'] != 'en') 
		{
			$this->config['language'] = strtolower($this->config['language']);
		}
		
		if (!is_dir(CONTENTPATH.'templates/'.$this->config['template'].'/'))
		{
			Error::quit(500, 'Oops!', 'The template either wasn\'t specified or it doesn\'t exist.');
		}
		
		if (!file_exists(APPPATH.'languages/language_'.$this->config['language'].'.php'))
		{
			Error::quit(500, 'No Comprendo?', 'The language file doesn\'t exist, which means we\'re stuck.');
		}

		if (empty($this->config['db_type']) || empty($this->config['db_name']))
		{
			Error::quit(500, 'No Database?', 'The config must contain the proper database information, or we\'re dead in the water.');
		}
		
		if (!empty($this->config['plugins'])) 
		{
			foreach ($this->config['plugins'] as $key => $plugin) 
			{
				if(!is_dir(APPPATH.'plugins/'.$plugin.'/'))
					unset($this->config['plugins'][$key]);
			}
		}

		return true;
	}
	
	/**
	 * Stores the configuration file.
	 *
	 * @return bool true if stored successfully
	 */
	private function store()
	{
		
		// Convert the settings to a PHP parsable array
		$contents = var_export($this->config, true);

		$contents = <<<CONFIG
<?php defined('APPPATH') or die('No direct script access.');

/**
 * Welcome to the Ultralite configuration file.
 * Here you can customize your photoblog with ease!
 * 
 * Just scroll down to see what you can change, 
 * and save the changes once you're done.
 * 
 * One thing to keep in mind, this file will be 
 * overwritten by Ultralite if you change your 
 * settings via the web admin.
 **/

return $contents;

CONFIG;
		
		if(!@file_put_contents($this->file, $contents))
			return false;
		else
			return true;
	}
	
}
