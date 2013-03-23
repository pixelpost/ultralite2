<?php

if (ini_get('phar.readonly'))
{
	exit('Create PHAR archive need to run php command with: php -d phar.readonly=0');
}

// load environnement
require dirname(__DIR__) . '/app/bootstrap.php';

// set some useful data about phar application
$app_name  = 'pixelpost.phar.php';
$app_alias = 'pixelpost.phar';
$stub      = <<<EOF
<?php

Phar::interceptFileFuncs();

define('PHAR',      true,                true);
define('ROOT_PATH', __DIR__,             true);
define('APP_PATH',  'phar://$app_alias', true);

require_once APP_PATH . '/bootstrap.php';

if (file_exists(PRIV_PATH . '/config.json'))
{
	require APP_PATH . '/app.php';
}
else
{
	if (CLI)
	{
		require APP_PATH . '/setup/cli.php';
	}
	else
	{
		Phar::webPhar('$app_alias', '/setup/index.php');
	}
}

__HALT_COMPILER();
EOF;

// delete old compiled version if exists
if (file_exists($app_name)) unlink($app_name);

// create the new phar archive
$p = new Phar($app_name, 0, $app_alias);
$p->startBuffering();
$p->buildFromDirectory(APP_PATH);
$p->setStub($stub);
$p->stopBuffering();


echo <<<EOL
Thank you for usgin pixelpost.

Don't forget to use suhosin.executor.include.whitelist="phar" configuration if
you use the suhosin patch;

PHAR archive is created: $app_name
EOL;
