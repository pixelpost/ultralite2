<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title><?php if($this->post->title) {  ?><?php echo $this->post->title  ?> | <?php } ?><?php echo $this->site->title ?></title>
	
</head>

<body>
<h1><?php echo $this->post->title ?></h1>
<em><?php echo $this->post->date ?></em>

<p>
	<img src="<?php echo $this->post->url_m ?>" width="<?php echo $this->post->width_m ?>" height="<?php echo $this->post->height_m ?>"/>
</p>
<p>
	<?php echo $this->post->description  ?>
</p>

<?php echo "PHP still works too!"; ?>

<?php if($this->post->photos) {  ?>
<h3>Extra Photos, for your enjoyment</h3>
<ul>
	<?php foreach($this->post->photos as $this->photo) {  ?>
		<li><?php echo $this->photo;  ?></li>
	<?php } ?>
</ul>
<?php } ?>


<?php //echo translate($this->post->next->title); ?>

<?php echo $this->post->next()->title ?>

</body>
</html>