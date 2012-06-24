<?php

$step             = 1;
$minStep          = 1;
$maxStep          = 2;
$phpTZ            = ini_get('date.timezone');

// check the php version if we have 5.3.0 at least
// PHP_VERSION_ID exists since verison 5.2.7
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50300)
{
	include 'tpl/step0-fail.tpl';
	exit();
}

// create the app env
require_once '../bootstrap.php';

// check installation by configuration existance
$isConfFileExists = file_exists(PRIV_PATH . '/config.json');

// load the actual step
if (isset($_GET['step'])) $step = abs(intval($_GET['step']));

if ($step < $minStep) $step = $minStep;
if ($step > $maxStep) $step = $maxStep;

switch($step)
{
	case 1: require APP_PATH . '/setup/step1.php'; break;
	case 2: require APP_PATH . '/setup/step2.php'; break;
}