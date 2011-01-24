<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Oops!</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	</head>
	<body>
		<h1>Oops!</h1>
		<p>
			It looks like we're experiencing a few problems with our website.
		</p>
		<p>
			Please hang in there, we'll be up and running as soon as possible.
		</p>
		<p>
			<em>The Pixelpost team.</em>
		</p>
		<h1>Error Message:</h1>
		<p>
			<?php echo $error ?>
		</p>
		<h1>Backtrace:</h1>
		<p>
			<?php echo nl2br($error->getTraceAsString()); ?>
		</p>
	</body>
</html>
