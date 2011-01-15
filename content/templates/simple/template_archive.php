<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title><?php if(!empty($this->archive->title)) {  ?><?php echo $this->archive->title  ?> | <?php } ?><?php echo $this->site->title ?></title>
	
</head>

<body>
<?php 

// var_dump($this->archive);

echo "Current:";

// var_dump( $this->archive->total() );

echo $this->archive->thumbnails();

// var_dump($this->archive->all());
// 
// foreach ($this->archive->all() as $key => $post) {
// 	echo $post->title . "<br/>";
// }

// var_dump($this->archive);
?>
</body>
</html>