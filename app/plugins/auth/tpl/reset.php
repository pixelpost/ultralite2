{% extends auth/tpl/_main.php %}

{% block Title %}Reset your password{% endblock %}

{% block Css %}
<style type="text/css">
	form { display:none; }
</style>
{% endblock %}

{% block Content %}
<noscript>
	You need to have javascript actived !
</noscript>
<form>
	<fieldset>
		<legend>Enter a new password</legend>
		<ol>
			<li>
				<label for="pass">Password:</label>
				<input type="password" id="pass" required />
			</li>
		</ol>
	</fieldset>
	<fieldset>
		<button class="btn" type="submit">CONTINUE</button>
		<span id="message"></span>
	</fieldset>
</form>
{% endblock %}

{% block Js %}
<script type="text/javascript">
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
