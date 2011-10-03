<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!Plugin::is_granted('read')) throw new Exception\Ungranted('auth.config.get');

$event->response = pixelpost\Config::create()->plugin_auth;   
