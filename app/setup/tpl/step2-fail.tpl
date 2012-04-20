{% extends _main.tpl %}

{% block Content %}
<h1 class="page-header">Oops! <small>Pixelpost 2 installation</small></h1>

<p class="alert alert-block alert-error">
	{{ error }}
</p>

<form method="POST">
<p>
	<input type="hidden" name="title" value="{{ data.title }}">
	<input type="hidden" name="timezone" value="{{ data.timezone }}">
	<input type="hidden" name="username" value="{{ data.username }}">
	<input type="hidden" name="password" value="{{ data.password }}">
	<input type="hidden" name="email" value="{{ data.email }}">
	<button class="btn btn-primary btn-large" type="submit">Try Again</button>
</p>
</form>
{% endblock %}