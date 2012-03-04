{% extends _main.tpl %}

{% block Content %}
<h1>Welcome!</h1>
<p>
	Pixelpost will be installed in two minutes...
</p>
<form method="POST" action="install.php?step=2">
	<fieldset>
		<legend>Configuration</legend>
		<ol>
			<li>
				<label for="title">Title:</label>
				<input id="title" name="title" placeholder="My Photoblog" required />
			</li>
			<li>
				<label for="username">Username:</label>
				<input id="username" name="username" placeholder="Username" required />
			</li>
			<li>
				<label for="password">Password:</label>
				<input id="password" name="password" placeholder="Password" required />
			</li>
			<li>
				<label for="email">Email:</label>
				<input id="email" name="email" placeholder="your-email@address.com" required />
			</li>
			<li>
				<label for="timezone">Timezone:</label>
				<select id="timezone" name="timezone" required>
					{% for tz in timezones %}
					<option {{ (tz == phpTZ)|if('selected', '') }}>{{ tz }}</option>
					{% endfor %}
				<select>
			</li>
		</ol>
	</fieldset>
	<fieldset>
		<button class="btn" type="submit">CONTINUE</button>
	</fieldset>
</form>
{% endblock %}

{% block Js %}
<script type="text/javascript">
	// for security we non transmit the password in clear text.
	$(document).ready(function() {
		$('form').submit(function(event) {
			var p = $('#password');
			p.val(md5(p.val()));
		});
	});
</script>
{% endblock %}