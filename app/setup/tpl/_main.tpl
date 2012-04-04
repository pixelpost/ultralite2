<!DOCTYPE html>
<html>
	<head>
		<title>Pixelpost Setup</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<link rel="stylesheet" media="screen" type="text/css" href="plugins/admin/public/style.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="http://fonts.googleapis.com/css?family=Lato:300" />
	</head>
	<body>
		{% block Content %}{% endblock %}
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
		<script	type="text/javascript" src="plugins/admin/public/lib.js"></script>
		{% block Js %}{% endblock %}
	</body>
</html>