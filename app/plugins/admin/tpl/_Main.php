<!DOCTYPE html>
<html>
	<head>
		<title>{% block Title %}{{ config().title }}{% endblock %}</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<base href="{{ @ADMIN_URL }}" />
		<link rel="stylesheet" media="screen" type="text/css" href="{{ @CONTENT_URL }}admin/content/style.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="http://fonts.googleapis.com/css?family=Lato:300" />
		{% block Css %}{% endblock %}
	</head>
	<body>
		<header>
			<nav>
				<ul>
					<li><a href="<?php echo ADMIN_URL ?>index/">home</a></li>
					<?php
						$event = \pixelpost\Event::signal('admin.template.nav', array('response' => array()));
						foreach($event->response as $item)
						{
							echo $item;
						}			
					?>
					<li><a href="<?php echo ADMIN_URL ?>api-test/">api test</a></li>
				</ul>
			</nav>
			<h1>{% display Title %}</h1>
		</header>
		<div id="content">
			{% block Content %}{% endblock %}
		</div>
		<footer>
			{% block Footer %}{% endblock %}
		</footer>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
		{% block Js %}{% endblock %}
	</body>
</html>