<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception as ApiException;

if (!Plugin::is_granted('read'))
{
	throw new ApiException('unauthorized', 'You have not the rights necessary to call this method.');
}

$event->response = pixelpost\Config::create()->plugin_auth;   
