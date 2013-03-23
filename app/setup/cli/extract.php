<?php
/**
 * Don't forget than parent script provides variables:
 *
 * $argc int                     Command line number of arguments
 * $argv array                   Command line arguments
 * $tpl  pixelpost\core\Template Empty template file
 */
if (!PHAR)
{
	require APP_PATH . 'setup/cli/help.php';
	exit();
}

$path = getcwd() . '/pixelpost';

if (!is_dir($path)) mkdir($path, 0775);

$phar = new Phar('pixelpost.phar');
$phar->extractTo($path);

$tpl->publish('extract.tpl');