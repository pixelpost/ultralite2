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
	
	private $config = array();

	/**
	 * Initializes and loads the configuration file.
	 */
	private function __construct()
	{
		
		
		$this->file = APPPATH.'config.php';
		$this->load();
		
		if(empty($this->config))
			Error::quit(500, 'Configuration Missing?', 'Yup, it looks like the config file is empty or doesn\'t exist. So we\'re stuck until it gets created.');
		
		// Merge the defaults with the the config, 
		// just in case the user didn't fill out an important setting.
		$defaults                 = $this->defaults();
		$this->config             = array_merge($defaults, $this->config);
		$this->config['site']     = array_merge($defaults['site'], $this->config['site']);
		$this->config['database'] = array_merge($defaults['database'], $this->config['database']);
		
		// Is the config ready for action?
		$this->validate();
		
		$this->config = Helper::array2obj($this->config);
		
		foreach ($this->config as $setting => $value)
				$this->$setting = $value;
	}
	
	/**
	 * Adds or replaces a configuration setting with the given value.
	 *
	 * @param string $setting The setting name.
	 * @param mixed $value The value.
	 * @param bool $overwrite If the setting exists and is the same value, should it be overwritten?
	 * @return bool true if changed
	 */
	public static function set($setting, $value, $overwrite = false)
	{
		 $self = self::getInstance();
		
		if (!$overwrite and isset($self->$setting) and $self->$setting == $value)
			return false;
		
		# Add the setting
		$self->config[$setting] = $self->$setting = $value;
		
		// if (class_exists("Trigger"))
			// Trigger::getInstance()->call("change_setting", $setting, $value, $overwrite);
			
		if (!$self->store()) {
			/**
			 * @todo Display warning that the setting wasn't saved!
			 */
			return false;
		} else
			return true;
	}
	
	/**
	 * Removes a configuration setting.
	 *
	 * @param string $setting he name of the setting to remove.
	 * @return bool true if removed
	 */
	public static function remove($setting)
	{
		$self = self::getInstance();
		
		if (!isset($self->$setting))
			return false;
		
		// Remove the setting
		unset($self->config[$setting]);
		unset($self->$setting);
		
		return $self->store();
	}
	
	/**
	 * Returns a singleton reference to the current configuration.
	 *
	 * @return $instance
	 */
	public static function & getInstance($reset=false)
	{

		static $instance = null;

		if ($reset)
				settype(&$instance, 'null');
		
		return $instance = (empty($instance)) ? new self() : $instance ;
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
		  'database' => 
		  array (
		    'host' => 'localhost',
		    'username' => '',
		    'password' => '',
		    'database' => '',
		    'prefix' => 'pixelpost_',
		    'type' => '',
		  ),
		  'site' => 
		  array (
		    'title' => 'Untitled',
		    'description' => '',
		    'copyright' => '(c) All Rights Reserved',
		    'url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/',
		    'language' => 'en',
		  ),
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
		if (substr($this->config['site']['url'], -1) != '/')
		{
			$this->config['site']['url'] = $this->config['site']['url'].'/';
		}
		
		if ($this->config['site']['language'] != 'en') 
		{
			$this->config['site']['language'] = strtolower($this->config['site']['language']);
		}
		
		if (!is_dir(CONTENTPATH.'templates/'.$this->config['template'].'/'))
		{
			Error::quit(500, 'Oops!', 'The template either wasn\'t specified or it doesn\'t exist.');
		}
		
		if (!file_exists(APPPATH.'languages/language_'.$this->config['site']['language'].'.php'))
		{
			Error::quit(500, 'No Comprendo?', 'The template either wasn\'t specified or it doesn\'t exist.');
		}

		if (empty($this->config['database']['type']) || empty($this->config['database']['database']))
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

return $contents

?>
CONFIG;
		
		if(!@file_put_contents($this->file, $contents))
			return false;
		else
			return true;
	}
	
}
