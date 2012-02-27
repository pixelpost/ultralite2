<!DOCTYPE html>
<html>
	<head>
		<title>{% block Title %}{{ config().title }}{% endblock %}</title>
		<meta charset="utf-8">
		<base href="{{ @ADMIN_URL }}">
		<link rel="stylesheet" media="screen" href="{{ @CONTENT_URL }}admin/content/style.css">
		<link rel="stylesheet" media="screen" href="http://fonts.googleapis.com/css?family=Lato:300">
		{% block Css %}{% endblock %}
		<?php
			$event = \pixelpost\Event::signal('admin.template.css', array('response' => array()));
			foreach($event->response as $item)
			{
				echo $item;
			}
		?>
	</head>
	<body lang="en">
		<header>
			<nav>
				<ul>
					<li><a href="index">home</a></li>
					<?php
						$event = \pixelpost\Event::signal('admin.template.nav', array('response' => array()));
						foreach($event->response as $item)
						{
							echo $item;
						}
					?>
					<li><a href="api-test">api test</a></li>
				</ul>
			</nav>
			<h1>{% display Title %}</h1>
		</header>
		<div id="content">
			{% block Content %}{% endblock %}
		</div>
		<footer>
			{% block Footer %}{% endblock %}
			<?php
				$event = \pixelpost\Event::signal('admin.template.footer', array('response' => array()));
				foreach($event->response as $item)
				{
					echo $item;
				}
			?>
		</footer>
		<script src="http://code.jquery.com/jquery-1.6.4.min.js"></script>
		<script src="{{ @CONTENT_URL }}admin/content/lib.js"></script>
		<?php
			$event = \pixelpost\Event::signal('admin.template.js', array('response' => array()));
			foreach($event->response as $item)
			{
				echo $item;
			}
		?>
		{% block Js %}{% endblock %}
	</body>
</html>