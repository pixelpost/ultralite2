<?php

namespace pixelpost\plugins\admin;

use pixelpost\core\Template,
	pixelpost\core\Event;

$tpl = Template::create()->assign('is_home_page', true);

$tpl->widgets = Event::signal('admin.template.widget', array('response' => array()))->response;

$tpl->publish('admin/tpl/home.php');
