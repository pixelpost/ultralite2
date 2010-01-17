<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->site->language; ?>">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	
	<title><?php echo $this->post->title ?> / <?php echo $this->site->title ?></title>
	
	<base href="<?php echo $this->site->url; ?>" />
	<!-- <link rel="canonical" href="post/2" /> -->
	
	<link rel="alternate" type="application/rss+xml" title="<?php echo $this->site->title; ?> RSS Feed" href="feed" />
	<link rel="stylesheet" href="content/templates/greyspace/styles/dark.css" type="text/css" charset="utf-8" title="Dark" />
	<link rel="alternate stylesheet" href="content/templates/greyspace/styles/light.css" type="text/css" charset="utf-8" title="Light" />
	<style type="text/css">
	
		<?php if (isset($this->post->width)): ?>
			.section{
				width: <?php echo $this->post->width; ?> px;
			}
		<?php endif ?>
	</style>
</head>

<body>
	<div id="wrapper">

		<div class="top section">
			<span class="published"><?php echo $this->post->date; ?></span>
			<h1 class="title"><?php echo $this->post->title; ?></h1>
			<div class="nav"><a href="./" class="active">Home</a> <a href="archive">Archive</a> <a href="about">About</a></div>
			<br class="clear"/>
		</div>

		<div class="middle section">
			<a href="<?php echo $this->post->prev()->url; ?>"><img src="<?php echo $this->post->photo; ?>" alt="<?php echo $this->post->title; ?>" width="<?php echo $this->post->width; ?>" height="<?php echo $this->post->height; ?>" id="photo" /></a>
			<div class="site section">
				<h2 class="name"><a href="./" title="View Latest Photo"><?php echo $this->site->title; ?></a></h2>
				<em class="tagline"><?php echo $this->site->description; ?></em>
			</div>
		</div>


		<div class="bottom section">
			<div id="description">
				<?php echo $this->post->description; ?>
			</div>
		</div>

	</div>
</body>
</html>