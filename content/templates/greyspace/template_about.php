<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->site->language; ?>">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	
	<title>About / <?php echo $this->site->title ?></title>
	
	<base href="<?php echo $this->site->url; ?>" />
	<!-- <link rel="canonical" href="post/2" /> -->
	
	<link rel="alternate" type="application/rss+xml" title="<?php echo $this->site->title; ?> RSS Feed" href="feed" />
	<link rel="stylesheet" href="content/templates/greyspace/styles/dark.css" type="text/css" charset="utf-8" title="Dark" />
	<link rel="alternate stylesheet" href="content/templates/greyspace/styles/light.css" type="text/css" charset="utf-8" title="Light" />
</head>

<body>
	<div id="wrapper">

		<div class="top section">
			<span class="published">About</span>
			<h1 class="title">Me</h1>
			<div class="nav"><a href="./">Home</a> <a href="archive">Archive</a> <a href="about" class="active">About</a></div>
			<br class="clear"/>
		</div>


		<div class="middle section">


			<div class="about section">
				<h2>So, what the heck is all this? </h2>
				<p class="summary">This is my &quot;photoblog&quot; or &quot;photographic web log&quot;. This template is a  default of the Ultralite install, and I have yet to edit the  information posted here.</p>


				<div class="subbox">
				<div class="col1">
				<h2>Info</h2>
				<p>I will eventually put some information about myself or my photography here. For now, please just enjoy the photos. </p>
				</div>

				<div class="col2">
				<h2>Links</h2>
				<p>Below is a list of links that you may find are useful in getting your blog up and running:</p>
				<ul>
				<li><a href="http://www.pixelpost.org">Pixelpost.org</a> - This is the application that runs this photoblog.</li>
				<li><a href="http://www.photoblogs.org">Photoblogs.org</a> - Pretty much the oracle of photoblog community.</li>
				</ul>
				</div>

				<div class="col3">
				<h2>Additional Links </h2>
				<ul>
				<li><a href="http://www.photoblogs.org/">Photoblogs.org</a></li>
				<li><a href="http://www.photoblogsmagazine.org">PhotoblogsMagazine.org</a></li>
				<li><a href="http://www.photofriday.com">PhotoFriday.com</a></li>
				<li><a href="http://photos.vfxy.com/">VFXY</a></li>
				<li><a href="http://www.jpgmag.com">JPGmag.com</a></li>
				<li><a href="http://www.coolphotoblogs.com/">CoolPhotoblogs.com</a></li>
				</ul>
				</div>

				<div class="clear"></div>
				</div>
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