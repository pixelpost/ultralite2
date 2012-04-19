<!DOCTYPE html>
<html>
	<head>
		<title>{% display Title %}</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		{% display Head %}
		<link rel="stylesheet" href="{{ 'pixelpost::asset/bootstrap.min.css'|asset }}">
		<link rel="stylesheet" href="{{ 'pixelpost::asset/bootstrap-responsive.min.css'|asset }}">
		{% display Css %}
	</head>
	<body lang="en">
		{% display Body %}
		<script src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
		<script src="{{ 'pixelpost::asset/bootstrap.min.js'|asset }}"></script>
		{% display Js %}
	</body>
</html>