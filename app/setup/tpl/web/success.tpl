{% extends _main.tpl %}

{% block Content %}
<h1 class="page-header">Congratulations! <small>Pixelpost 2 installation</small></h1>

<p class="alert alert-block alert-success">
	Pixelpost has been successfully installed.
</p>

<p>
	<a class="btn btn-primary btn-large" href="{{ @ADMIN_URL }}">Finish</a>
</p>
{% endblock %}