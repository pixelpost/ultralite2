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
class Pixelpost_Config
{
	
	/**
	 * Holds all of the settings as a $key => $val
	 *
	 * @var $config array
	 */
	private $config = array();
	
	/**
	 * Config file path
	 *
	 * @var string
	 */
	private $file = "";

	/**
	 * Initializes and loads the configuration file.
	 */
	private function __construct()
	{
		
		$this->file = APPPATH.'config.php';
		
		if (!$this->load())
		{
			throw new Exception("Unable to load configuration file.", E_ERROR);
			return false;
		}
			
		
		foreach ($this->config as $setting => $value)
				$this->$setting = $value;
			
		/**
		 * @todo Possibly add some error checking, to make sure the required settings exist
		 */
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
	 *
	 * @return bool true if loaded successfully
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
