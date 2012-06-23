<?php

namespace pixelpost\plugins\auth\classes;

use pixelpost\core\Request,
	pixelpost\core\Template,
	pixelpost\plugins\api\Plugin as Api;

class UserForm
{
	public $flag_reconnect = false;
	public $flag_success   = false;
	public $is_online      = false;
	public $user_id        = null;

	public function __construct($user_id, $is_online = false)
	{
		$this->user_id   = $user_id;
		$this->is_online = $is_online;
	}

	/**
	 * Check if the form is posted and process posted data
	 *
	 * @param pixelpost\core\Request $request
	 * @return bool TRUE if a form is posted else false
	 */
	public function check(Request $request)
	{
		if (!$request->is_post()) return false;

		// retrieve posted data in $p
		$p = filter_var_array($request->get_post(), array(
			'name'     => array('filter' => FILTER_SANITIZE_STRING),
			'email'    => array('filter' => FILTER_VALIDATE_EMAIL),
			'password' => array('filter' => FILTER_SANITIZE_STRING),
		));

		// delete not provided and bad value
		if (!$p['name'])     unset($p['name']);
		if (!$p['email'])    unset($p['email']);
		if (!$p['password']) unset($p['password']);

		// remove name if not changed (for error in api next call: same name)
		if (isset($p['name']) && $p['name'] === $this->user_id)
		{
			unset($p['name']);
		}

		// make the update
		Api::call('auth.user.set', $p + array('user' => $this->user_id));

		// update some flag
		$this->flag_success   = true;
		$this->flag_reconnect = ($this->is_online and isset($p['name']) || isset($p['password']));

		// update the user id if needed
		if (isset($p['name']))
		{
			$this->user_id = $p['name'];
		}

		return true;
	}

	/**
	 * Create a Html5 form
	 *
	 * @param array|object $user the user data (user, name, email)
	 * @return string The HTML5 form
	 */
	public function render($user)
	{
		return Template::create()
			->assign(array('user' => $user, 'form' => $this))
			->render('auth/tpl/_user-form.tpl');
	}
}