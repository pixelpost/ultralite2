<?php
/**
 * @return array of string all detected warnings.
 */

$warnings = array();

if (file_exists(CONF_FILE))
{
	$warnings[] = 'Found an existing config file, please run the app.php script';
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
		$warnings[] = '`' . PRIV_PATH . '` already exists: '
		            . 'config file and database, will not be created.';
	}

	// check if public folder already exists
	if (file_exists(PUB_PATH))
	{
		$warnings[] = '`' . PUB_PATH . '` already exists: '
					. 'files in it will be exposed on internet.';
	}

	// check if a .htaccess file allready exists
	if (file_exists(ROOT_PATH . '/.htaccess'))
	{
		$warnings[] = '`' . ROOT_PATH . '/.htaccess` already exists: '
					. 'we cannot install the mod_rewrite rules, '
					. 'and secure your private data... '
					. 'Take a look at the `app/setup/samples/htaccess_sample` '
					. 'file for manual install.';
	}

	// check if GD is installed in version 2
	if (!extension_loaded('gd'))
	{
		$warnings[] = 'GD 2 library is not installed.';
	}

	if (defined('GD_MAJOR_VERSION') && GD_MAJOR_VERSION < 2)
	{
		$warnings[] = 'GD library is too old. You need version 2.0.0 or later. '
		            . 'Your current GD version is ' . GD_VERSION . '.';
	}

	// check if sqlite3 is present (some distribution like debian
	// provide sqlite3 support in a separated paquet.
	if (!extension_loaded('sqlite3'))
	{
		$warnings[] = 'sqlite3 extension is not installed.';
	}

	if (!extension_loaded('mbstring'))
	{
		$warnings[] = 'mbstring extension is not installed.';
	}
}

return $warnings;