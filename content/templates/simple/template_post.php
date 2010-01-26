<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title><?php if(!empty($this->post->title)) {  ?><?php echo $this->post->title  ?> | <?php } ?><?php echo $this->site->title ?></title>
	
</head>

<body>
<?php 

// var_dump($this->post);

echo "Current:";
var_dump($this->post->title);

echo "Next:";
var_dump($this->post->next()->title);

echo "Previous:";
var_dump($this->post->prev()->title);

echo "First:";
var_dump($this->post->first()->title);

echo "Last:";
var_dump($this->post->last()->title);

// var_dump($this->post);
?>
</body>
</html>