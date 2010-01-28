<?php include('template.php'); ?>

<h2>Post Tests</h2>

<pre><code><?php

function test($input='',$output='')
{
	if ($input !== $output)
		echo "<strong>FAIL</strong>";
	else
		echo "OK";
}

/**
 * Test 1: Latest Post
 */
echo "\n<a href=\"{$this->site->url}post\">Test 1: Latest Post</a>\n";

test($this->post->id, 8);
echo ' | $this->post->id'."\n";

test(empty($this->post->next()->id), true);
echo ' | $this->post->next()->id'."\n";

test($this->post->prev()->id, 12);
echo ' | $this->post->prev()->id'."\n";

test($this->post->first()->id, 1);
echo ' | $this->post->first()->id'."\n";

test($this->post->last()->id, 8);
echo ' | $this->post->last()->id'."\n";

/**
 * Test 2: Specific Post
 */
echo "\n<a href=\"{$this->site->url}post/7\">Test 2: Specific Post</a>\n";

test($this->post->id, 7);
echo ' | $this->post->id'."\n";

test($this->post->next()->id, 9);
echo ' | $this->post->next()->id'."\n";

test($this->post->prev()->id, 6);
echo ' | $this->post->prev()->id'."\n";

test($this->post->first()->id, 1);
echo ' | $this->post->first()->id'."\n";

test($this->post->last()->id, 8);
echo ' | $this->post->last()->id'."\n";

// var_dump($this->post);
?></code></pre>