<?php

$warnings = array();

if ($isConfFileExists)
{
	$warnings[] = 'Found a config file, you should run update.php script';
}
else
{		
	// check if root path is writable
	if (is_writable(ROOT_PATH) == false)
	{
		$warnings[] = '`' . ROOT_PATH . '` is not writable.';
	}

	// check if private folder already exists
	if (file_exists(PRIV_PATH))
	{
		$warnings[] = '`' . PRIV_PATH . '` already exists (config file and database, will not be created).';		
	}

	// check if a .htaccess file allready exists
	if (file_exists(ROOT_PATH . SEP . '.htaccess'))
	{
		$messageVeryLong  = '.htaccess` already exists: '
						  . 'we cannot install the mod_rewrite rule, '
						  . 'and securise your private data... '
						  . ' Take a look on `app/setup/htaccess_sample` '
						  . ' file for manual install.';

		$warnings[] = '`' . ROOT_PATH . SEP . $messageVeryLong;					
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Pixelpost Setup</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	</head>
	<body>
		<h1>Welcome !</h1>
		<p>
			Pixelpost will be installed in two minutes...
		</p>
		
		<?php if (count($warnings) > 0) : #---------------------------------- ?>

		<h2>Please take care about :</h2>
		
		<ul>
			<?php
			foreach($warnings as $message)
			{
				printf('<li></li>', $message);
			}
			?>
		</ul>

		<p>
			<a href="#">VERIFY AGAIN</a>			
		</p>
		
		<?php else :  #------------------------------------------------------ ?>
		
		<form method="POST" action="./install.php?step=2">
			<fieldset>
				<legend>Configuration</legend>
				<ol>
					<li>
						<label for="title">Title:</label>
						<input id="title" name="title" placeholder="My Photoblog" required />
					</li>
					<li>
						<label for="timezone">Timezone:</label>
						<select id="timezone" name="timezone" required>
							<?php
							foreach(DateTimeZone::listIdentifiers() as $tz)
								printf('<option>%s</option>', $tz);
							?>
						<select>
					</li>
				</ol>
			</fieldset>
			<fieldset>
				<button type="submit">CONTINUE</button>
			</fieldset>
		</form>
		
		<?php endif; #------------------------------------------------------- ?>
		
	</body>
</html>
	