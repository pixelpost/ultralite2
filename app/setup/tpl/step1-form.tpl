{% extends _main.tpl %}

{% block Content %}
<h1 class="page-header">Welcome! <small>Pixelpost 2 installation</small></h1>
<p>
	Pixelpost will be installed in two minutes...
</p>
<form class="form-horizontal" method="POST" accept-charset="utf-8" action="?step=2">
	<fieldset>
		<legend>Configuration</legend>
		<div class="control-group">
			<label class="control-label" for="title">Title</label>
			<div class="controls">
				<input id="title" name="title" placeholder="My Photoblog" required />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="username">Username</label>
			<div class="controls">
				<input id="username" name="username" placeholder="Username" required />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="password">Password</label>
			<div class="controls">
				<input id="password" name="password" type="password" placeholder="Password" required />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="email">Email</label>
			<div class="controls">
				<input id="email" name="email" type="email" placeholder="user@example.com" required />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="timezone">Timezone</label>
			<div class="controls">
				<select id="timezone" name="timezone" required>
					{% for tz in timezones %}
					<option {{ (tz == phpTZ)|if('selected', '') }}>{{ tz }}</option>
					{% endfor %}
				<select>
			</div>
		</div>
		<div class="form-actions">
			<button class="btn btn-primary btn-large" type="submit">Continue</button>
		</div>
	</fieldset>
</form>
{% endblock %}

{% block Js %}
<script type="text/javascript">
	// for security we won't transmit the plain text password.
	$(document).ready(function() {
		$('form').submit(function(event) {
			var p = $('#password');
			p.val(md5(p.val()));
		});
	});
</script>
{% endblock %}