<?php

/**
* Helper Class
*/
class Helper
{
	
	function __construct()
	{
		# code...
	}
	
	static function array2obj($data) 
	{
	    return is_array($data) ? (object) array_map(array(__CLASS__,__METHOD__),$data) : $data;
	}
	
	static function entities($value='')
	{
		return htmlentities($value,ENT_QUOTES,'UTF-8');
	}
	
	static function isactive($value='')
	{
		global $page;
		
		if ($page == $value) {
			echo ' class="active"';
			return true;
		}
		
		return false;
	}
	
	// static function encode($value='')
	// {
	// 	return urlencode($value);
	// }
}


