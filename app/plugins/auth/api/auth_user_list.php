<?php

namespace pixelpost\plugins\auth;

use pixelpost;
use pixelpost\plugins\api\Exception;

if (!Plugin::is_granted('admin')) throw new Exception\Ungranted('auth.user.list');

$event->response = array('user' => Model::user_list());