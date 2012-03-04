{% extends _main.tpl %}

{% block Content %}
<h1>Welcome!</h1>
<p>
	Pixelpost will be installed in two minutes...
</p>
<h2>Please note:</h2>
<ul>
	{% for message in warnings %}
	<li>{{ message }}</li>
	{% endfor %}
</ul>
<p>
	<a class="btn" href="install.php?step=1">VERIFY AGAIN</a>
</p>
{% endblock %}