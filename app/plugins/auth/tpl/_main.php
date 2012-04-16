<!DOCTYPE html>
<html>
	<head>
		<title>{% display Title %}</title>
		<meta charset="utf-8">
		<base href="{{ @ADMIN_URL }}">
		<link rel="stylesheet" media="screen" href="{{ 'admin::style.css'|asset }}">
		<link rel="stylesheet" media="screen" href="http://fonts.googleapis.com/css?family=Lato:300">
		{% display Css %}
	</head>
	<body lang="en">
		<h1>{% display Title %}</h1>
		<div id="content">
			{% display Content %}
		</div>
		<script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
		<script src="{{ 'admin::lib.js'|asset }}"></script>
		{% display Js %}
	</body>
</html>