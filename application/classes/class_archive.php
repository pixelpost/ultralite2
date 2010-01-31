<?php
/**
 * Archive
 * 
 * Loads multiple posts, for an archives page.
 *
 * @package Pixelpost
 * @author Jay Williams
 */

class Archive
{
	private $config;
	private $archive;
	public $per_page;
	public $page = 1;
	public $total_pages = 1;
	
	public $success = false;

	public function __construct($archive=null, $option=array())
	{
		$this->config = & Config::current();
		
		
		if(isset($option['per_page']))
			$this->page = (int) $option['per_page'];
		else
			$this->per_page = (int) $this->config->per_page;
		
		if(isset($option['page']))
			$this->page = (int) $option['page'];
		elseif(Uri::get('page'))
			$this->page = (int) Uri::get('page');
		
		
		if (is_object($archive))
			$this->_posts = & $archive;
		else
			$this->posts($archive);
		
		if (empty($this->_posts))
			 return false;
		
		// Everything worked!
		$this->success = true;
	}

	/**
	 * Checks if a sub-class exists when an empty() or isset() 
	 * function is called on an inaccessible property.
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
	 * Loads the sub class, when an inaccessible property is requested
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
	 * Executes a requested call
	 * 
	 * Example:
	 *    $post->total();
	 *    $post->posts();
	 */
	public function __call($name, $arguments)
	{
		$name = '_'.$name;
		
		if(isset($this->$name))
			return $this->$name;
		
		$result = $this->query($name, $arguments);
		
		if (empty($result))
			 return new Void;
		
		if (is_array($result))
		{
			foreach ($result as $key => & $value)
			{
				$result[$key] = new Post($value);
			}
		}
		
		$this->$name = $result;
		
		return $this->$name;
	}

	/**
	 * Fetch a new Post
	 * A shorthand way to create a new Post class
	 */
	public function get($id)
	{
		return new self($id);
	}

	public function thumbnails()
	{
		$posts = $this->posts();
		
		// create thumbnails list
		$thumbnails = '';
		foreach ($posts as & $post)
		{
			$thumbnails .= '<a href="' . $post->url . '" title="' . $post->title . '">'
			 			. '<img src="' . $post->photo_t . '" alt="' . $post->title . '" width="' . $post->width_t .'" height="' . $post->height_t . '" class="thumbnail" />'
			 			. "</a>";
		}
		return $thumbnails;
		
	}

	/**
	 * Perform a Database Query
	 */
	public function query($archive=null, $arguments=array())
	{
		switch ($archive) {
			
			// case '_next': // Load the the Next (Newer) Post
			// 	$sql = "SELECT * FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `id` != '{$this->id}'  AND `date` >= '{$this->date_raw}'  AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` ASC LIMIT 1";
			// 	break;
			
			// case '_prev': // Load the the Previous (Older) Post
			// case '_previous': // Alias for _prev
			// 	$sql = "SELECT * FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `id` != '{$this->id}' AND `date` <= '{$this->date_raw}'  AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` DESC LIMIT 1";
			// 	break;
			
			case '_total': // Total posts
				$sql = "SELECT count(`id`) FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` DESC";
				return (int) DB::get_var($sql);
				break;
			
			case '_posts': // Load all of the posts
			case '':
			default:
				$sql = "SELECT * FROM `{$this->config->db_prefix}posts` WHERE `published` = '1' AND `date` <= CURRENT_TIMESTAMP ORDER BY `date` DESC";
				break;
		}
		
		return DB::get_results($sql);
	}


} //endclass
