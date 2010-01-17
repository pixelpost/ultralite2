<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->site->language; ?>">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	
	<title><?php echo $this->page->title ?> / <?php echo $this->site->title ?></title>
	
	<base href="<?php echo $this->site->url; ?>" />
	<!-- <link rel="canonical" href="post/2" /> -->
	
	<link rel="alternate" type="application/rss+xml" title="<?php echo $this->site->title; ?> RSS Feed" href="feed" />
	<link rel="stylesheet" href="content/templates/greyspace/styles/dark.css" type="text/css" charset="utf-8" title="Dark" />
	<link rel="alternate stylesheet" href="content/templates/greyspace/styles/light.css" type="text/css" charset="utf-8" title="Light" />
</head>

<body>
	<div id="wrapper">

		<div class="top section">
			<span class="published"><?php echo $this->page->date; ?></span>
			<h1 class="title"><?php echo $this->page->title; ?></h1>
			<div class="nav"><a href="./" class="active">Home</a> <a href="archive">Archive</a> <a href="about">About</a></div>
			<br class="clear"/>
		</div>

		<div id="description">
			<?php echo $this->page->content; ?>
		</div>

	</div>
</body>
</html>