<!DOCTYPE html>
<html>
	<head>
		<title>{% block Title %}{% endblock %}</title>
		<meta charset="utf-8">
		<base href="{{ @ADMIN_URL }}">
		<link rel="stylesheet" media="screen" href="{{ @CONTENT_URL }}admin/content/style.css">
		<link rel="stylesheet" media="screen" href="http://fonts.googleapis.com/css?family=Lato:300">
		{% block Css %}{% endblock %}
	</head>
	<body>
		<h1>{% display Title %}</h1>
		<div id="content">
			{% block Content %}{% endblock %}
		</div>
		<script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
		<script src="{{ @CONTENT_URL }}admin/content/lib.js"></script>
		{% block Js %}{% endblock %}
	</body>
</html>