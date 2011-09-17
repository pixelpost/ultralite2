<?php

/***
 * INSTALL HEADER
 */
$step = 1;
$minStep = 1;
$maxStep = 2;
$isConfFileExists = true;

try 
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';
}
catch(\pixelpost\Error $e)
{
	if ($e->getCode() == 3) $isConfFileExists = false;
	else throw $e;
}

if (isset($_GET['step'])) $step = abs(intval($_GET['step']));

if ($step < $minStep) $step = $minStep;
if ($step > $maxStep) $step = $maxStep;

switch($step)
{
	case 1:
		require __DIR__ . SEP . 'setup' . SEP . 'step1.php'; 
		break;
	case 2:
		require __DIR__ . SEP . 'setup' . SEP . 'step2.php'; 
		break;
}
