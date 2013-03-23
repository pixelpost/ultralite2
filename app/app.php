<?php

namespace pixelpost;

use pixelpost\core\Config,
	pixelpost\core\Filter,
	pixelpost\core\Plugin,
	pixelpost\core\Event;

require_once 'bootstrap.php';

// We need to parse the config file and set the configured environnement
$conf = Config::load(CONF_FILE);

// Debug can also be switched on by setting the Apache envrionment
// variable `APPLICATION_ENV` to `development` in .htaccess
$debug = ($conf->debug or 'development' == getenv('APPLICATION_ENV'));

defined('DEBUG')       or define('DEBUG',       $debug,                 true);
defined('PROCESS_ID')  or define('PROCESS_ID',  uniqid(),               true);
defined('WEB_URL')     or define('WEB_URL',     $conf->url,             true);
defined('CONTENT_URL') or define('CONTENT_URL', $conf->url . 'public/', true);

DEBUG or  error_reporting(0);
DEBUG and assert_options(ASSERT_ACTIVE, true);

date_default_timezone_set($conf->timezone);

assert('pixelpost\core\Log::info("(bootstrap) new process: %s",  PROCESS_ID)');
assert('pixelpost\core\Log::debug("(bootstrap) VERSION: %s",     VERSION)');
assert('pixelpost\core\Log::debug("(bootstrap) ROOT_PATH: %s",   ROOT_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) APP_PATH: %s",    APP_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) CORE_PATH: %s",   CORE_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) PLUG_PATH: %s",   PLUG_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) PRIV_PATH: %s",   PRIV_PATH)');
assert('pixelpost\core\Log::debug("(bootstrap) WEB_URL: %s",     WEB_URL)');
assert('pixelpost\core\Log::debug("(bootstrap) CONTENT_URL: %s", CONTENT_URL)');

// Step 6. Check auto update if needed
if (Filter::compare_version($conf->version, VERSION))
{
	assert('pixelpost\core\Log::info("(bootstrap) upgrade detected")');

	require_once APP_PATH . '/setup/update.php';
}

// Step 7. Registers activated plugins
assert('pixelpost\core\Log::info("(bootstrap) plugin registration")');

Plugin::make_registration();

// Step 8. Send the signal that all is ready to go!
assert('pixelpost\core\Log::info("(bootstrap) application starts")');

Event::signal('app.start');

// Step 9. Send the signal that all is finished!
assert('pixelpost\core\Log::info("(bootstrap) application ends")');

Event::signal('app.end');
