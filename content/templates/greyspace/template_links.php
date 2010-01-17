<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->site->language; ?>">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	
	<title>Links / <?php echo $this->site->title ?></title>
	
	<base href="<?php echo $this->site->url; ?>" />
	<!-- <link rel="canonical" href="post/2" /> -->
	
	<link rel="alternate" type="application/rss+xml" title="<?php echo $this->site->title; ?> RSS Feed" href="feed" />
	<link rel="stylesheet" href="content/templates/greyspace/styles/dark.css" type="text/css" charset="utf-8" title="Dark" />
	<link rel="alternate stylesheet" href="content/templates/greyspace/styles/light.css" type="text/css" charset="utf-8" title="Light" />
</head>

<body>
	<div id="wrapper">

		<div class="top section">
			<span class="published">Links</span>
			<h1 class="title">I Like</h1>
			<div class="nav"><a href="./">Home</a> <a href="archive">Archive</a> <a href="about">About</a> <a href="links" class="active">Links</a></div>
			<br class="clear"/>
		</div>


		<div class="middle section">


			<div class="about section">
				<h2>My Favorite Links </h2>
				<p class="summary">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
			</div>

			<div class="site section">
				<h2 class="name"><a href="./" title="View Latest Photo"><?php echo $this->site->title; ?></a></h2>
				<em class="tagline"><?php echo $this->site->description; ?></em>
			</div>
		</div>

		<div class="bottom section">

			<div class="credits">
				<a href="#ultralite">Powered by: Ultralite</a> | <a href="#greyspace">Designed By: Jay Williams</a>
			</div>

		</div>

	</div>
</body>
</html>