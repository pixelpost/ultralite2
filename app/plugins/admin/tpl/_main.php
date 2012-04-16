<!DOCTYPE html>
<html>
	<head>
		<title>{% block Title %}{{ config().title }}{% endblock %}</title>
		<meta charset="utf-8">
		<base href="{{ @ADMIN_URL }}">
		<link rel="stylesheet" media="screen" href="{{ @CONTENT_URL }}admin/style.css">
		<link rel="stylesheet" media="screen" href="http://fonts.googleapis.com/css?family=Lato:300">
		{% display Css %}
		{{ 'admin.template.css'|event|join }}
	</head>
	<body lang="en">
		<header>
			<nav>
				<ul>
					<li><a href="index">home</a></li>
					{{ 'admin.template.nav'|event|join }}
					<li><a href="api-test">api test</a></li>
				</ul>
			</nav>
			<h1>{% display Title %}</h1>
		</header>
		<div id="content">
			{% display Content %}
		</div>
		<footer>
			{% display Footer %}
			{{ 'admin.template.footer'|event|join }}
		</footer>
		<script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
		<script src="{{ @CONTENT_URL }}admin/lib.js"></script>
		{{ 'admin.template.js'|event|join }}
		{% display Js %}
	</body>
</html>