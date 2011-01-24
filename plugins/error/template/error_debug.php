<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Oups !</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	</head>
	<body>
		<h1>Oups !</h1>
		<p>
			There is an internal error ! Active the debug mode in your
			configuration, if you want more information about it !
		</p>
		<p>
			We are soory about this behaviour and we'll try to recover it soonly.
		</p>
		<p>
			<em>The Pixelpost team.</em>
		</p>
		<h1>Error Message:</h1>
		<p>
			<?php echo $error ?>
		</p>
		<h1>BackTrace:</h1>
		<p>
			<?php echo nl2br($error->getTraceAsString()); ?>
		</p>
	</body>
</html>
