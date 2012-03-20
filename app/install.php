<?php

/***
 * INSTALL HEADER
 */
$step             = 1;
$minStep          = 1;
$maxStep          = 2;
$isConfFileExists = true;
$phpTZ            = ini_get('date.timezone');

// check the php version if we have 5.3.0 at least
// PHP_VERSION_ID exists since verison 5.2.7
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
{
	// use dirname(__FILE__) here because __DIR__ exists since PHP 5.3.0
	include dirname(__FILE__) . 'setup/tpl/step0-fail.tpl';
	exit();
}

try
{
	require_once __DIR__ . '/bootstrap.php';
}
catch(Exception $e)
{
	// we can't use pixelpost\core\Error here because we consider we can be in PHP < 5.3
	// so namespace can't be used.
	// use get_class() instead of is_a() or instanceof because is_a() throw
	// a E_STRICT between PHP5.0.0 and PHP 5.3.0 and instanceof exists since
	// PHP 5.0.0 (and instanceof can call __autoload() before PHP 5.1.0)
	if (get_class($e) == 'pixelpost\core\Error' && $e->getCode() == 3) $isConfFileExists = false;
	else throw $e;
}

if (isset($_GET['step'])) $step = abs(intval($_GET['step']));

if ($step < $minStep) $step = $minStep;
if ($step > $maxStep) $step = $maxStep;

switch($step)
{
	case 1: require __DIR__ . '/setup/step1.php'; break;
	case 2: require __DIR__ . '/setup/step2.php'; break;
}
