{% extends pixelpost/tpl/_main.tpl %}

{% block Css %}
	{# <link rel="stylesheet" href="{{ 'admin::style.css'|asset }}"> #}
	{% child %}
{% endblock %}

{% block Js %}
	<script src="{{ 'admin::lib.js'|asset }}"></script>
	{% child %}
{% endblock %}

{% block Head %}
	<base href="{{ @ADMIN_URL }}">
	{% child %}
{% endblock %}

{% block Body %}
	<div class="container">
		<h1 class="row-fluid page-header">{{ config().title }} <small>{% display Title %}</small></h1>
		{% display Content %}
	</div>
{% endblock %}