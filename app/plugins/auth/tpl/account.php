{% extends admin/tpl/_main.php %}

{% block Title %}Account settings{% endblock %}

{% block Js %}
<script src="{{ 'auth::account.js'|asset }}"></script>
{% endblock %}

{% block Content %}
<div class="row-fluid">
	<form id="form-account" method="post" accept-charset="utf-8" class="span6 form-horizontal">
		<fieldset>
			<legend>Update your account settings</legend>
			{% if flag_success %}
			<p class="alert alert-success fade in">
				<a class="close" data-dismiss="alert">&times;</a>
				Updated !
			</p>
			{% endif %}
			{% if flag_reconnect %}
			<p class="alert alert-warning fade in">
				<a class="close" data-dismiss="alert">&times;</a>
				You need to reconnect on next page.
			</p>
			{% endif %}
			<div class="control-group">
				<label class="control-label" for="name">Username:</label>
				<div class="controls">
					<input id="name" name="name" value="{{ user.name }}" placeholder="username" required>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="email">Email:</label>
				<div class="controls">
					<input type="email" id="email" name="email" value="{{ user.email }}" placeholder="user@example.com" required>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="password">Password:</label>
				<div class="controls">
					<input type="password" id="password" name="password" placeholder="keep it empty will not change it">
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary" type="submit">Update</button>
				<button class="btn" type="reset">Reset</button>
			</div>
		</fieldset>
	</form>

	<div class="span6">
		<h2 class>Manage your public keys</h2>

		<p class="well form-inline">
			<input id="key_name" placeholder="Where will this key be used?">
			<button class="btn" id="key_add">Create</button>
		</p>

		<div id="entities">
			{% for entity in entities %}
				{% display entity %}
			{% elsefor %}
			<div class="alert alert-info alert-block">
				<h3 class="alert-heading">Tip!</h3>
				<p>A public key allows you to use external apps and services
					to access and manage your website remotely, using the
					<a href="https://github.com/pixelpost/ultralite2/wiki/api-home">Pixelpost API</a>,
					without requiring you to disclose your username and password.
				<p><strong>Create your first one!</strong>
			</div>
			{% endfor %}

			<div class="modal fade" id="modal_del_entity">
				<div class="modal-header">
					<a class="close" data-dismiss="modal">×</a>
					<h3>Are you sure?</h3>
				</div>
				<div class="modal-body">
					<p>
						Public key <strong></strong> will be deleted. Software using this
						key will no longer be able to access to your pixelpost information.
					</p>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn" data-dismiss="modal">Cancel</a>
					<a href="#" class="btn btn-danger btn-primary">Delete</a>
				</div>
			</div>

		</div>
	</div>
</div>
{% endblock Content %}

{% block entity %}
<div class="entity hide well well-small">
	<p>
		<strong>{{ entity.name }}</strong>
		<span class="hide">&mdash;</span>
		<span class="pull-right">
			<a class="btn btn-info btn-mini" data-toggle="collapse" data-parent="#entities" href="#{{ entity.entity }}">
				<i class="icon-list icon-white"></i> info
			</a>
			<span class="hide">&mdash;</span>
			<a class="btn btn-danger btn-mini">
				<i class="icon-remove icon-white"></i> delete
			</a>
		</span>
	</p>
	<form id="{{ entity.entity }}" class="collapse form-inline">
		<table class="table table-condensed">
			<tr>
				<th>Public key</th>
				<td>{{ entity.public_key }}</td>
			</tr>
			<tr>
				<th>Private key</th>
				<td>{{ entity.private_key }}</td>
			</tr>
			<tr>
				<th>Grants</th>
				<td>
					{% for g in grants %}
					<label class="checkbox inline">
						<input type="checkbox" value="{{ g.grant }}"> {{ g.name }}
					</label>
					{% endfor %}
				</td>
			</tr>
		</table>
		<p class="input-append">
			<input type="text" placeholder="rename that public key"><button
				class="btn"><i class="icon-pencil"></i>update</button>
		</p>
	</form>
</div>
{% endblock %}

{% block Footer %}
<aside id="entity">
	{:
		assign('entity',
			@array(
				'entity'      => '',
				'name'        => '',
				'public_key'  => '',
				'private_key' => '',
			)
		)
	:}
	{% display entity %}
</aside>
{% endblock %}
