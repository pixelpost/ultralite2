<?php

$warnings = array();

if ($isConfFileExists)
{
	$warnings[] = 'Found a config file, you should run update.php script';
}
else
{
	// check if root path is writable
	if (is_writable(ROOT_PATH) == false)
	{
		$warnings[] = '`' . ROOT_PATH . '` is not writable.';
	}

	// check if private folder already exists
	if (file_exists(PRIV_PATH))
	{
		$warnings[] = '`' . PRIV_PATH . '` already exists (config file and database, will not be created).';
	}

	// check if a .htaccess file allready exists
	if (file_exists(ROOT_PATH . SEP . '.htaccess'))
	{
		$messageVeryLong  = '.htaccess` already exists: '
						  . 'we cannot install the mod_rewrite rule, '
						  . 'and secure your private data... '
						  . ' Take a look at the `app/setup/htaccess_sample` '
						  . ' file for manual install.';

		$warnings[] = '`' . ROOT_PATH . SEP . $messageVeryLong;
	}

	// check if GD is installed in version 2
	if (!defined('GD_MAJOR_VERSION'))
	{
		$warnings[] = 'GD 2 library is not installed.';
	}

	if (GD_MAJOR_VERSION < 2)
	{
		$warnings[] = 'GD library is too old. You need version 2.0.0 or later. '
		            . 'Your current GD version is ' . GD_VERSION . '.';
	}

	// check if sqlite3 is present (some distribution like debian
	// provide sqlite3 support in a separated paquet.
	if (!class_exists('SQLite3'))
	{
		$warnings[] = 'SQLite3 library is not installed.';
	}
}

$template = (count($warnings) > 0) ? 'step1-fail.tpl' : 'step1-form.tpl';

$tpl = pixelpost\core\Template::create();

$tpl->set_cache_raw_template(false)->set_template_path(__DIR__ . SEP . 'tpl');

$tpl->warnings  = $warnings;
$tpl->phpTZ     = $phpTZ;
$tpl->timezones = DateTimeZone::listIdentifiers();

$tpl->publish($template);

