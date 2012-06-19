<?php

namespace pixelpost\plugins\admin;

use pixelpost\core\Template,
	pixelpost\core\Plugin as Plug;

$plugin = current($event->params);

if (!$plugin || !Plug::is_exists($plugin))
{
	$event->redirect('admin.404');
	exit();
}

Template::create()
	->assign('plugin', new classes\Plugin($plugin))
	->assign('is_tab_plugins', true)
	->publish('admin/tpl/settings/plugin.tpl');