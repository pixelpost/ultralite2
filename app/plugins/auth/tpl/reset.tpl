{% extends auth/tpl/_main.tpl %}

{% block Title %}Password reset{% endblock %}

{% block Content %}
<noscript>
	<div class="row-fluid alert alert-warning">
		You need to have javascript actived !
	</div>
</noscript>
<form class="row-fluid form-horizontal">
	<fieldset>
		<legend>Enter a new password</legend>
		<div class="control-group">
			<label class="control-label" for="pass">Password:</label>
			<div class="controls">
				<input type="password" id="pass" required>
			</div>
		</div>
		<div class="form-actions">
			<button class="btn btn-primary btn-large" type="submit">Continue</button>
			<span id="message" class="label label-important"></span>
		</div>
	</fieldset>
</form>
{% endblock %}

{% block Css %}
<style>form, #message { display:none; }</style>
{% endblock %}

{% block Js %}
<script>
	$(document).ready(function() {
		$('form').show().submit(function(e) {
			e.preventDefault();
			e.stopPropagation();
			var pass   = md5($('#pass').val());
			var url    = window.location.href;
			var data   = { 'pass': pass };
			var todo   = function(data) {
				if (data.status == 'valid') window.location.href = '{{ @ADMIN_URL }}';
				else $('#message').text(data.message).hide().fadeIn();
			};
			jQuery.post(url, data, todo, 'json');
		});
	});
</script>
{% endblock %}
