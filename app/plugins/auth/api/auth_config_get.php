<?php

namespace pixelpost\plugins\auth;

use pixelpost\core\Config,
	pixelpost\plugins\api\Exception\Ungranted;

if (!Plugin::is_granted('read')) throw new Ungranted('auth.config.get');

$event->response = Config::create()->plugin_auth;
