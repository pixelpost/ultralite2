<!DOCTYPE html>
<html>
	<head>
		<title>Pixelpost Setup</title>
		<meta charset="utf-8">
		{% if use_public %}
		<link rel="stylesheet" href="{{ @ADMIN_URL }}../public/pixelpost/asset/bootstrap.min.css">
		<link rel="stylesheet" href="{{ @ADMIN_URL }}../public/pixelpost/asset/bootstrap-responsive.min.css">
		{% else %}
		<link rel="stylesheet" href="../plugins/pixelpost/public/asset/bootstrap.min.css">
		<link rel="stylesheet" href="../plugins/pixelpost/public/asset/bootstrap-responsive.min.css">
		{% endif %}
	</head>
	<body lang="en" class="container-fluid">
		{% display Content %}
		<script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
		{% if use_public %}
		<script	src="{{ @ADMIN_URL }}../public/pixelpost/asset/bootstrap.min.js"></script>
		<script	src="{{ @ADMIN_URL }}../public/admin/lib.js"></script>
		{% else %}
		<script	src="../plugins/pixelpost/public/asset/bootstrap.min.js"></script>
		<script	src="../plugins/admin/public/lib.js"></script>
		{% endif %}
		{% display Js %}
	</body>
</html>