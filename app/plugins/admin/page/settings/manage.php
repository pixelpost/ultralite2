<?php

namespace pixelpost\plugins\admin;

use Exception as E,
	pixelpost\core\Plugin as Plug;

$error = '';

try
{
	if (!$event->request->is_query()) throw new E('no request', 0);

	$params = $event->request->get_query() + array('plugin' => '', 'action' => '');
	$plugin = $params['plugin'];
	$action = $params['action'];

	if (!Plug::is_exists($plugin)) throw new E('bad request');

	$p = new classes\Plugin($plugin);

	switch ($action)
	{
		default:
			throw new E('bad request');
			break;

		case 'active':
			if (!$p->active()) throw new E($p->error());
			break;

		case 'inactive':
			if ($p->is_protected()) throw new E('bad action');
			if (!$p->inactive()) throw new E($p->error());
			break;

		case 'uninstall':
			if ($p->is_protected() || $p->is_packaged()) throw new E('bad action');
			if (!$p->uninstall()) throw new E($p->error());
			break;

		case 'clean':
			if ($p->is_protected() || $p->is_packaged()) throw new E('bad action');
			if (!$p->clean()) throw new E($p->error());
			break;
	}
}
catch(E $e)
{
	$error = $e->getMessage();
}

echo json_encode(array('error' => ($error != ''), 'message' => $error));
