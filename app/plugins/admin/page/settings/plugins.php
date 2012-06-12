<?php

namespace pixelpost\plugins\admin;

use pixelpost\core\Template;

$manager = new classes\PluginManager();

Template::create()
	->assign(array(
		'all'    => $manager->all(),
		'new'    => $manager->news(),
		'is_new' => $manager->is_new(),
	))
	->assign('is_tab_plugins', true)
	->publish('admin/tpl/settings/plugins.php');