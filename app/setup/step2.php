<?php
try 
{	
	$error = '';
	$rollbackTo = 0;
	
	// create the private directory
	if (mkdir(PRIV_PATH, 0775) == false)
	{
		throw new Exception('Cannot create `' . PRIV_PATH . '`.');
	}
	
	$rollbackTo = 1;

	// copy the config file
	$src = APP_PATH . SET . 'setup' . SEP . 'config_sample.json';
	$dst = PRIV_PATH . SEP . 'config.json';

	if (copy($src, $dst) == false)
	{
		throw new Exception('Cannot copy `'. $src . '` to `' . $dst . '`.');
	}

	$rollbackTo = 2;
	
	// copy the .htaccess file
	$src = APP_PATH . SET . 'setup' . SEP . 'htaccess_sample';
	$dst = ROOT_PATH . SEP . '.htaccess';

	if (copy($src, $dst) == false)
	{
		throw new Exception('Cannot copy `'. $src . '` to `' . $dst . '`.');
	}

	// set the folders in .htaccess file
	$content = file_get_contents($dst);
	$content = str_replace('{{PRIV}}', PRIV_PATH, $content);
	file_put_contents($dst, $content);
	
	$rollbackTo = 3;
	
	// copy the index.php file
	$src = APP_PATH . SET . 'setup' . SEP . 'index_sample.php';
	$dst = ROOT_PATH . SEP . 'index.php';

	if (copy($src, $dst) == false)
	{
		throw new Exception('Cannot copy `'. $src . '` to `' . $dst . '`.');
	}

	$rollbackTo = 4;
	
	// Load the request
	$request = pixelpost\Request::create()->auto();
	
	// retrieve the posted data
	$post = $request->get_post();
	
	// Load the config file
	$conf = pixelpost\Config::load(PRIV_PATH . SEP . 'config.json');

	// retreive the userdir
	$path = explode('/', $request->get_path()); 

	array_pop($path); // remove install.php
	array_pop($path); // remove app

	$conf->userdir = implode('/', $path);

	// retreive the website url
	$conf->url = $request->get_base_url();
	
	// retrieve the timezone
	$conf->timezone = $post['timezone'];
	
	// retrieve the title
	$conf->title = $post['title'];

	// create an uniq id for this installation
	$conf->uid = md5(uniqid() . microtime() . $request->get_request_url());

	// save the configuration file
	$conf->save();

	// create the database
	$db = pixelpost\Db::create();
	
	$rollbackTo = 5;

	// detect all plugins already in the package (and store the list in conf)
	pixelpost\Plugin::detect();

	foreach($conf->plugins as $plugin => $state)
	{
		if (pixelpost\Plugin::install($plugin) == false)
		{
			throw new Exception('Error during plugin ' . $plugin . ' setup.');
		}

		if (pixelpost\Plugin::active($plugin) == false)
		{
			throw new Exception('Error during plugin ' . $plugin . ' activation.');
		}
	}
} 
catch(Exception $e)
{	
	$error = $e->getMessage();
	
	if ($rollbackTo >= 5) unlink(PRIV_PATH . SEP . 'sqlite3.db');
	if ($rollbackTo >= 4) unlink(ROOT_PATH . SEP . 'index.php');
	if ($rollbackTo >= 3) unlink(ROOT_PATH . SEP . '.htaccess');
	if ($rollbackTo >= 2) unlink(PRIV_PATH . SEP . 'config.json');
	if ($rollbackTo >= 1) rmdir(PRIV_PATH);
}

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Pixelpost Setup</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	</head>
	<body>
		
		<?php if ($error != '') : #------------------------------------------ ?>

		<h1>Oops !</h1>
		<p>
			<?php echo $error ?>
		</p>

		<p>
			<a href="#">TRY AGAIN</a>			
		</p>
		
		<?php else :  #------------------------------------------------------ ?>
		
		<h1>Congratulation !</h1>
		<p>
			Pixelpost is correctly installed.
		</p>

		<p>
			<a href="<?php $request->get_base_url() ?>admin">FINISH</a>			
		</p>
		
		<?php endif; #------------------------------------------------------- ?>
		
	</body>
</html>
