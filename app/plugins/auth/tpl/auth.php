{% extends auth/tpl/_main.php %}

{% block Title %}Authentification{% endblock %}

{% block Css %}
<style type="text/css">
	form { display:none; }
	#forget { display:none; }
</style>
{% endblock %}


{% block Content %}
<noscript>
	You need to have javascript actived !
</noscript>
<form>
	<fieldset>
		<legend>Who are you ?</legend>
		<ol>
			<li>
				<label for="user">Username:</label>
				<input type="text" id="user" value="{{ user }}" placeholder="username" required />
			</li>
			<li>
				<label for="pass">Password:</label>
				<input type="password" id="pass" placeholder="password" required />
			</li>
		</ol>
		<p>
			<input type="hidden" id="data" value="{{ priv }}" />
			<button class="btn" type="submit">CONTINUE</button>
			<span id="message"></span>
		</p>
		<p>
			<a id="forget" href="#">Forget your password ? Click here to request a reset</a>
		</p>
	</fieldset>
</form>
{% endblock %}

{% block Js %}
<script type="text/javascript">
	$(document).ready(function() {
		$('form').show().submit(function(e) {
			e.preventDefault();
			e.stopPropagation();
			var user   = $('#user').val();
			var pass   = md5($('#pass').val());
			var data   = $('#data').val();
			var nonce  = Math.ceil((new Date()).getTime() / 150000) * 150;
			var domain = window.location.hostname;
			var secret = md5(user + pass + domain);
			var key    = md5(data + secret + nonce);
			var url    = '{{ @ADMIN_URL }}auth-login';
			var data   = { 'user': user, 'priv': data, 'key': key };
			var todo   = function(json) {
				if (json.status == 'valid') {
					window.location.reload();
				} else {
					$('#message').text(json.message).hide().fadeIn();
					$('#forget').fadeIn();
				}
			};
			jQuery.post(url, data, todo, 'json');
		});
		$('#forget').click(function(e) {
			e.preventDefault();
			e.stopPropagation();
			var user   = $('#user').val();
			var data   = { 'user': user };
			var url    = '{{ @ADMIN_URL }}auth-forget';
			var todo   = function(json) {
				if (json.status == 'valid') {
					$('#message').text(json.message).hide().fadeIn();
					$('#forget').fadeOut();
				} else {
					$('#message').text(json.message);
				}
			};
			jQuery.post(url, data, todo, 'json');

		});
	});
</script>
{% endblock %}
