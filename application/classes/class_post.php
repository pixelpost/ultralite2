<?php
/**
 * Post
 * 
 * Loads the specified post, and can navigate forwards and backwards by posts.
 * It can also load any sub-classes on demand. e.g. $post->comments == new Post_Comments
 *
 * @package Pixelpost
 * @author Jay Williams
 */

class Post
{
	/**
	 * Stores the next() object, if called
	 *
	 * @var object
	 * @access private
	 */
	private $next;

	/**
	 * Stores the prev() object, if called
	 *
	 * @var object
	 * @access private
	 */
	private $prev;
	
	private $config;
	
	private $post;
	
	public $success = false;

	public function __construct($post=null)
	{
		$this->config = Config::current();
		
		if (is_object($post))
			$this->post = $post;
		else
			$this->post = $this->query($post);

		if (empty($this->post))
			 return false;
		
		foreach ($this->post as $key => $value)
		{
			if (is_numeric($value))
				$this->$key = (int) $value;
			else
				$this->$key = $value;
		}
		
		// Format Dates
		$this->date_raw       = $this->date;
		$this->date_timestamp = strtotime($this->date_raw);
		$this->date           = date($this->config->date_format, $this->date_timestamp);
		
		$this->author_name = 'Jay Williams'; // Pull from db, on request?
		
		// Format Permalink
		if ($this->config->permalink == 'slug')
			$this->url = $this->config->url.'post/'.$this->slug;
		else
			$this->url = $this->config->url.'post/'.$this->id;
		
		// Add the full url to the image & thumbnail, if it doesn't exist
		if (substr($this->photo,0,7) != 'http://')
			$this->photo = $this->config->url . 'content/images/' . $this->photo;
		
		if (substr($this->photo_t,0,7) != 'http://')
			$this->photo_t = $this->config->url . 'content/images/' . $this->photo_t;
		
		// Everything worked!
		$this->success = true;
	}

	/**
	 * Checks if a sub-class exists when an empty() or isset() 
	 * function is called on a non-existent property.
	 * 
	 * Input:
	 *    "test" ($post->test)
	 * Output:
	 *    true (if the class "Post_Test" exists)
	 */
	public function __isset($property)
	{
		$class_name = __CLASS__ . '_' . ucfirst($property);
		
		if (class_exists($class_name))
			return true;
		else
			return false;
	}

	/**
	 * Loads the sub class, when requested
	 */
	public function __get($property)
	{
		$class_name = __CLASS__ . '_' . ucfirst($property);
		
		if (class_exists($class_name))
			return new $class_name($this->id);
		
		// Return an empty placeholder, if no class exists
		return new Void;
	}

	/**
	 * Fetch a new Post
	 * A shorthand way to create a new Post class
	 */
	public function get($id)
	{
		return new self($id);
	}

	/**
	 * Fetch the Next (Newer) Post
	 */
	public function next()
	{
		if(is_object($this->next))
			return $this->next;
		
		$post = $this->query('__next');
		
		if (empty($post))
			 return new Void;
		
		$this->next = new self($post);
		
		return $this->next;
	}
	
	/**
	 * Fetch the Previous (Older) Post
	 */
	public function prev()
	{
		if(is_object($this->prev))
			return $this->prev;
		
		$post = $this->query('__prev');
		
		if (empty($post))
			 return new Void;
		
		$this->prev = new self($post);
		
		return $this->prev;
	}
		
	/**
	 * Alias for prev()
	 */
	public function previous()
	{
		return $this->prev();
	}

	/**
	 * Perform a Database Query
	 */
	public function query($post=null)
	{
		switch ($post) {
			
			case '__next': // Load the the Next (Newer) Post
				$sql = "SELECT * FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `id` != '{$this->id}'  AND `date` => '{$this->date_raw}'  AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` ASC LIMIT 1";
				break;
			
			case '__prev': // Load the the Previous (Older) Post
				$sql = "SELECT * FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `id` != '{$this->id}' AND `date` <= '{$this->date_raw}'  AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` DESC LIMIT 1";
				break;
			
			case '': // Load the default (latest) post
				$sql = "SELECT * FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` DESC LIMIT 1";
				break;
			
			case (is_numeric($post)): // Load the specified post_id
				$sql = "SELECT * FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `id` = '$post' AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` DESC LIMIT 1";
				break;
			
			case (is_string($post)): // Load the specific post_slug
				$sql = "SELECT * FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `slug` = '$post' AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` DESC LIMIT 1";
				break;
		}
		
		return DB::get_row($sql);
	}


} //endclass
