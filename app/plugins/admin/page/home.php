<?php

namespace pixelpost\plugins\admin;

use pixelpost\Template,
	pixelpost\Event;

$tpl = Template::create();

$tpl->widgets = Event::signal('admin.template.widget', array('response' => array()))->response;

$tpl->publish('admin/tpl/home.php');
