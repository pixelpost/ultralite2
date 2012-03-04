<!DOCTYPE html>
<html>
	<head>
		<title>{% block Title %}{{ config().title }}{% endblock %}</title>
		<meta charset="utf-8">
		<base href="{{ @WEB_URL }}">
		<link rel="stylesheet" media="screen" href="{{ @CONTENT_URL }}web/content/style.css">
		<link rel="stylesheet" media="screen" href="http://fonts.googleapis.com/css?family=Lato:300">
		{% display Css %}
		{{ 'web.template.css'|event|join }}
	</head>
	<body lang="en">
		<header>
			<nav>
				<ul>
					<li><a href="index">home</a></li>
					{{ 'web.template.nav'|event|join }}
				</ul>
			</nav>
			<h1>{% display Title %}</h1>
		</header>
		<div id="content">
			{% display Content %}
		</div>
		<footer>
			{% display Footer %}
			{{ 'web.template.footer'|event|join }}
		</footer>
		{{ 'web.template.js'|event|join }}
		{% display Js %}
	</body>
</html>