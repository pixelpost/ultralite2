{% extends auth/tpl/_main.php %}

{% block Title %}authentication{% endblock %}

{% block Content %}
<form class="row-fluid form-horizontal">
	<fieldset>
		<legend>Who are you ?</legend>

		<div class="control-group">
			<label class="control-label" for="user">Username:</label>
			<div class="controls">
				<input id="user" value="{{ user }}" placeholder="username" required>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="pass">Password:</label>
			<div class="controls">
				<input type="password" id="pass" placeholder="password" required>
			</div>
		</div>
		<div class="form-actions">
			<input type="hidden" id="data" value="{{ priv }}">
			<button class="btn btn-primary btn-large" type="submit">
				<i class="icon-user icon-white"></i> Log In
			</button>
			<span id="message" class="label"></span>
			<a id="forget" href="#">Forget your password ? Click here to request a reset</a>
		</div>
	</fieldset>
</form>
<noscript>
	<div class="row-fluid alert alert-warning">
		You need to have javascript actived !
	</div>
</noscript>
{% endblock %}

{% block Css %}
<style>
	form, #message, #forget { display:none; }
</style>
{% endblock %}

{% block Js %}
<script>
$(document).ready(function() {
	$('form').show().submit(function(e) {
		e.preventDefault();
		e.stopPropagation();
		var url    = '{{ @ADMIN_URL }}auth-login';
		var user   = $('#user').val();
		var pass   = md5(md5($('#pass').val())+url);
		var data   = $('#data').val();
		var salt   = Math.ceil((new Date()).getTime() / 150000) * 150;
		var secret = md5(user + pass);
		var key    = md5(data + secret + salt);
		var data   = { 'user': user, 'priv': data, 'key': key };
		var todo   = function(json) {
			if (json.status == 'valid') {
				window.location.reload();
			} else {
				$('#message').text(json.message).hide().addClass('label-important').fadeIn();
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
				$('#forget').fadeOut();
				$('#message').text(json.message)
					.hide()
					.removeClass('label-important')
					.addClass('label-success')
					.fadeIn();
			} else {
				$('#message').text(json.message)
					.hide()
					.addClass('label-important')
					.fadeIn();
			}
		};
		jQuery.post(url, data, todo, 'json');
	});
});
</script>
{% endblock %}
