{% extends _main.tpl %}

{% block Content %}
<h1>Oops !</h1>
<p>
	{{ error }}
</p>

<form method="POST">
<p>
	<input type="hidden" name="title" value="{{ data.title }}" />
	<input type="hidden" name="timezone" value="{{ data.timezone }}" />
	<input type="hidden" name="username" value="{{ data.username }}" />
	<input type="hidden" name="password" value="{{ data.password }}" />
	<input type="hidden" name="email" value="{{ data.email }}" />
	<button class="btn" type="submit">TRY AGAIN</button>			
</p>
</form>
{% endblock %}