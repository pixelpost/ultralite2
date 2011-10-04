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
						  . 'and securise your private data... '
						  . ' Take a look on `app/setup/htaccess_sample` '
						  . ' file for manual install.';

		$warnings[] = '`' . ROOT_PATH . SEP . $messageVeryLong;					
	}
}

$template = (count($warnings) > 0) ? 'step1-fail.tpl' : 'step1-form.tpl';

$tpl = pixelpost\Template::create();

$tpl->set_cache_raw_template(false)->set_template_path(__DIR__ . SEP . 'tpl');

$tpl->warnings  = $warnings;
$tpl->phpTZ     = $phpTZ; 		
$tpl->timezones = DateTimeZone::listIdentifiers();

$tpl->publish($template);

	