{% extends pixelpost/tpl/_main.php %}

{% block Title %}{% endblock %}

{% block Css %}
	<link rel="stylesheet" href="{{ 'admin::style.css'|asset }}">
	{{ 'admin.template.css'|event|join }}
	{% child %}
{% endblock %}

{% block Js %}
	<script src="{{ 'admin::lib.js'|asset }}"></script>
	{{ 'admin.template.js'|event|join }}
	{% child %}
{% endblock %}

{% block Head %}
	<base href="{{ @ADMIN_URL }}">
	{% child %}
{% endblock %}

{% block Body %}
	<header class="navbar navbar-fixed-top">
		<nav class="navbar-inner">
			<div class="container-fluid">
				<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</a>
				<a class="brand" href="http://pixelpost.org/">PixelPost</a>
				<div class="nav-collapse">
					<ul class="nav">
						<li><a href="index">home</a></li>
						{{ 'admin.template.nav'|event|join }}
					</ul>
					{{ 'admin.template.navbar'|event|join }}
				</div>
			</div>
		</nav>
	</header>
	<div id="content" class="container-fluid">
		<h1 class="row-fluid page-header">{{ config().title }} <small>{% display Title %}</small></h1>
		{% display Content %}
	</div>
	<footer>
		<aside id="notify"></aside>
		{% display Footer %}
		{{ 'admin.template.footer'|event|join }}
	</footer>
{% endblock %}

