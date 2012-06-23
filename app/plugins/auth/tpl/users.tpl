{% extends admin/tpl/settings/_main.tpl %}

{% block Css %}
<style>
	/* fix margin bug in list of user with bootstrap */
	#users .user:nth-of-type(3n + 1) { margin-left:0; }
</style>
{% endblock %}

{% block Js %}
<script src="{{ 'auth::users.js'|asset }}"></script>
{% endblock %}

{% block Content %}
<h2 class="lead">Add a user</h2>

<p class="well form-inline">
	<input id="user_name" placeholder="Username" class="input-small">
	<input id="user_pass" placeholder="Password" class="input-small">
	<input id="user_email" placeholder="user@example.com">
	<button class="btn btn-primary" id="user_add">Create</button>
</p>

<h2 class="lead">Manage users</h2>

<div id="users" class="row-fluid">
	{% for user in users %}
		{% display user %}
	{% endfor %}
</div>

<div class="modal fade" id="modal_del_user">
	<div class="modal-header">
		<a class="close" data-dismiss="modal">×</a>
		<h3>Are you sure?</h3>
	</div>
	<div class="modal-body">
		<p>
			User <strong></strong> will be deleted. His no longer can access to
			the admin and all software he used will no longer be able to access
			to the API.
		</p>
	</div>
	<div class="modal-footer">
		<a href="#" class="btn" data-dismiss="modal">Cancel</a>
		<a href="#" class="btn btn-danger btn-primary">Delete</a>
	</div>
</div>

{% endblock Content %}

{% block Footer %}
<aside id="user" class="hide">
	{:
		assign('user', @array(
			'user'     => '',
			'name'     => '',
			'email'    => '',
			'gravatar' => '',
			'is_admin' => false,
		))
	:}
	{% display user %}
</aside>
{% endblock %}


{% block user %}
{# this block require a var named `user` #}
<div class="span4 user" data-id="{{ user.user }}">
	<div class="well">
		<p class="pull-left thumbnail">
			<img src="http://gravatar.com/avatar/{{ user.gravatar }}?s=70">
		</p>
		<p class="offset1">
			{% if user.is_admin %}
			<span class="label label-inverse pull-right">admin</span>
			{% endif %}
			<i class="icon-user"></i> <span class="user-name">{{ user.name }}</span><br>
			<i class="icon-envelope"></i> <span class="user-email">{{ user.email }}</span><br>
			<br>
			<a class="btn btn-mini btn-edit" href="settings/user/{{ user.user }}">
				<i class="icon-pencil"></i> edit
			</a>
			<a class="btn btn-mini btn-danger" href="#">
				<i class="icon-white icon-trash"></i> delete
			</a>
		</p>
	</div>
</div>
{% endblock %}


