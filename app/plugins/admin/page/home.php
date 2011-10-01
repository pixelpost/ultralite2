<?php

namespace pixelpost\plugins\admin;

use pixelpost;

$tpl = pixelpost\Template::create();

$tpl->widgets = pixelpost\Event::signal('admin.template.widget', array('response' => array()))->response;

$tpl->publish('admin/tpl/home.php');
