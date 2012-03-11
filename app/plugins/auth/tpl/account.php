{% extends admin/tpl/_main.php %}

{% block Content %}
<form method="post" accept-charset="utf-8">
	<fieldset>
	<legend>Update your account settings</legend>
	<ol>
		<li>
			<label for="name">Username:</label>
			<input type="text" id="name" name="name" value="{{ user.name }}" placeholder="username" required>
		</li>
		<li>
			<label for="email">Email:</label>
			<input type="text" id="email" name="email" value="{{ user.email }}" placeholder="email@address.com" required>
		</li>
		<li>
			<label for="password">Password:</label>
			<input type="password" id="password" name="password" placeholder="password, keep empty will not change it">
		</li>
	</ol>
	<p>
		<input type="hidden" id="data" value="">
		<button class="btn" type="submit">Update</button>
		<span class="msg">{{ message }}</span>
	</p>
	</fieldset>
</form>

<h2>Manage your public keys</h2>
<div id="entities">
	{% for entity in entities %}
		<p class="entity">
			<strong>{{ entity.name }}</strong> — <a href="#">delete</a><br>
			<code>
				public key: <span class="pub_key">{{ entity.public_key }}</span><br>
				private key: <span>{{ entity.private_key }}</span>
			</code>
		</p>
	{% elsefor %}
		A public key offers access to your pixelpost API. Create your first one:
	{% endfor %}
</div>
<p>
	<fieldset>
		<input type="text" id="key_name" placeholder="what is the key usage ?">
		<button class="btn" id="key_add">Add a public key</button>
	</fieldset>
</p>

<div id="grants" class="hidden">
{% for grant in grants %}
	{{ grant.grant }}
	{{ grant.name }}
{% endfor %}
</div>

<p id="entity" class="hidden">
	<strong></strong> — <a href="#">delete</a><br>
	<code>
		public key: <span class="pub_key"></span><br>
		private key: <span></span>
	</code>
</p>
{% endblock Content %}


{% block Js %}
<script>
	$(document).ready(function() {

		// user form management
		$('form').submit(function(e) {
			var pass = $('#password');
			var val  = pass.val();
			if (val != '') pass.val(md5(val));
		});

		// key management
		$('#key_add').click(function() {
			// the new key name
			var name = $('#key_name').val();
			if (name == '') name = 'default #' + ($('.entity').length + 1);
			// add a new public key
			var data = {
				'name': name,
				'user': $('#name').val()
			};
			api_call('auth.entity.add', data, function (add_rep)
			{
				if (add_rep.status == 'valid')
				{
					data = {'entity': add_rep.response.entity};

					api_call('auth.entity.get', data, function (get_rep)
					{
						if (get_rep.status == 'valid')
						{
							var e = $('#entity').clone().addClass('entity').attr('id', '').appendTo('#entities');
							e.find('strong').text(get_rep.response.name);
							e.find('span').text(get_rep.response.private_key);
							e.find('span.pub_key').text(get_rep.response.public_key);
							e.fadeIn('slow');
						}
					});
				}
			});
		});
	});
</script>
{% endblock %}