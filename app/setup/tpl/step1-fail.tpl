{% extends _main.tpl %}

{% block Content %}
<div class="row-fluid">
	<h1 class="page-header">Welcome! <small>Pixelpost 2 installation</small></h1>
	<p>
		Pixelpost will be installed in two minutes...
	</p>
	<div class="alert alert-block">
		<h2 class="alert-heading">Please note:</h2>
		<ul>
			{% for message in warnings %}
			<li>{{ message }}</li>
			{% endfor %}
		</ul>
	</div>
	<p>
		<a class="btn btn-primary btn-large" href="install.php?step=1">VERIFY AGAIN</a>
	</p>
</div>
{% endblock %}