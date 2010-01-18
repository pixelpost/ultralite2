<!--
	THIS PAGE IS CURRENTLY BROKEN 
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->site->language; ?>">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	
	<title><?php echo $this->archive->title ?> / <?php echo $this->site->title ?></title>
	
	<base href="<?php echo $this->site->url; ?>" />
	<!-- <link rel="canonical" href="post/2" /> -->
	
	<link rel="alternate" type="application/rss+xml" title="<?php echo $this->site->title; ?> RSS Feed" href="feed" />
	<link rel="stylesheet" href="content/templates/greyspace/styles/dark.css" type="text/css" charset="utf-8" title="Dark" />
	<link rel="alternate stylesheet" href="content/templates/greyspace/styles/light.css" type="text/css" charset="utf-8" title="Light" />
</head>

<body>
	<div id="wrapper">

		<div class="top section">
			<span class="published">Archive</span>
			<h1 class="title"><?php echo $this->archive->title; ?></h1>
			<div class="nav"><a href="./">Home</a> <a href="archive" class="active">Archive</a> <a href="about">About</a></div>
			<br class="clear"/>
		</div>


		<div class="middle section">


			<div class="thumbnails section">
				<?php echo $this->archive->thumbnails(); ?>
			</div>

			<div class="site section">
				<h2 class="name"><a href="./" title="View Latest Photo"><?php echo $this->site->title; ?></a></h2>
				<em class="tagline"><?php echo $this->site->description; ?></em>
			</div>
		</div>

		<div class="bottom section">

			<?php if ($this->site->posts_per_page): ?>

				<div class="pagination">

					<?php if ((Uri::$page) == 2): ?>
						<a href="<?php echo Uri::$uri; ?>" class="previous">&#x2190; Previous Page</a>

					<?php elseif ((Uri::$page) > 1): ?>
						<a href="<?php echo Uri::$uri . '/page/' . (Uri::$page-1); ?>" class="previous">&#x2190; Previous Page</a>

					<?php else: ?>
						<a class="previous disabled">&#x2190; Previous Page</a>

					<?php endif ?>


					<?php if (Uri::$page < Uri::$total_pages): ?>
						<a href="<?php echo Uri::$uri . '/page/' . (Uri::$page+1); ?>" class="next">Next Page &#x2192;</a>

					<?php else: ?>
						<a class="next disabled">Next Page &#x2192;</a>

					<?php endif ?>


					<span class="page"><?php echo "Page " . Uri::$page . " of " . Uri::$total_pages; ?></span>

					<br class="clear"/>
				</div>

			<?php endif ?>

			<?php if (!empty($categories)): ?>
				<h3>Categories</h3>
				<div class="categories">
					<?php foreach ($categories as $category): ?>
						<?php if ($category->depth == 0 || $category->depth > 1) continue; ?>
						<li><a href="<?php echo "archive/category/".$category->permalink; ?>"><?php echo htmlentities($category->name); ?></a></li>
					<?php endforeach ?>
				</div>
			<?php endif ?>

			<?php if (!empty($tags)): ?>
				<h3>Tags</h3>
				<div class="tags">
					<?php foreach ($tags as $tag): ?>
						<li><a href="<?php echo "archive/tag/".$tag->permalink; ?>"><?php echo htmlentities($tag->name); ?></a></li>
					<?php endforeach ?>
				</div>
			<?php endif ?>


		</div>

	</div>
</body>
</html>