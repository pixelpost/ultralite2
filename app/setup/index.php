<?php
// check the php version if we have 5.3.0 at least
// PHP_VERSION_ID exists since verison 5.2.7
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
{
	include './tpl/web/php-min-version.tpl';
	exit();
}

// require a file with a php5.3 syntax
require './web/install.php';
