<?php

/**
* Error Class
* 
* @package Pixelpost
* @author Jay Williams
*/
class Error
{
	/**
	 * Enables or disables the pretty html output.
	 *
	 * @var bool
	 */
	static $html = true;
	
	
	private function __construct()
	{
		# code...
	}
	
	/**
	 * Set HTTP Status Code
	 *
	 * @param int $code HTTP Status Code
	 * @return bool true
	 * @author Jay Williams
	 */
	static function status($code=404)
	{
		switch ((int) $code)
		{
			case 500:
				$status = '500 Internal Server Error';
				break;
			case 404:
			default:
				$status = '404 Not Found';
				break;
		}
		
		@header($_SERVER["SERVER_PROTOCOL"].' '.$status);
		@header('Status: '.$status);
		
		return true;
	}
	
	
	/**
	 * Generate error message and quit script execution
	 *
	 * Alias for message(), but with added die() function call.
	 * 
	 * @param string $code HTTP Status Code
	 * @param string $title Error Title
	 * @param string $message Error Message 
	 * @return string $output
	 * @author Jay Williams
	 */
	static function quit($code=404, $title='', $message='')
	{
		return die(Error::message($code,$title,$message));
	}
	
	
	/**
	 * Generate error message
	 * 
	 * Set the variable: Error::$html = false;
	 * if you don't want the pretty html output.
	 *
	 * @param string $code HTTP Status Code
	 * @param string $title Error Title
	 * @param string $message Error Message 
	 * @return string $output
	 * @author Jay Williams
	 */
	static function message($code=404, $title='', $message='') 
	{
		
		$title = (!empty($title))? $title : 'Oh No!';
		$message = (!empty($message))? $message : 'Something seems to have gone awry! You can try again, or go back to the <a href="/">home page</a>.';
		
		Error::status($code);
		
		if (self::$html)
		{
			// It might be a good idea to move this to its own template file.
			$output = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html><head><title>Error: '.$code.'</title><meta http-equiv="content-type" content="text/html; charset=utf-8"><style type="text/css">* {padding: 0px;margin: 0px;}body {background: #FAFAFA;color: #444;font: normal 0.9em "Lucida Sans Unicode",sans-serif;}a {color: #4283B9;text-decoration: none;}a:hover {color: #111;}h1 {color: #333;font: normal 1.4em/24px Verdana,sans-serif;padding-bottom: 5px;}p,code,em,ul {padding: 5px 0;}#content {width: 700px;text-align: center;margin: 50px auto 0;}#content .message {text-align: left;padding: 6px 12px;border: 1px solid #EEE;background: #FFF;}</style></head><body><div id="content"><div class="message"><h1>'.$title.'</h1><p>'.$message.'</p></div></div></body></html>';
		}
		else
		{
			$output = "Error: $code\n$title\n$message";
		}
		
		return $output;
	}
	
}
