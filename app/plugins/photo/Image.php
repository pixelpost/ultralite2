<?php

namespace pixelpost\plugins\photo;

use pixelpost;

class Image
{
	/**
	 * The image source file
	 * 
	 * @var string 
	 */
	protected $_src;
	
	/**
	 * The image source witdh
	 * 
	 * @var int 
	 */
	protected $_w;
	
	/**
	 * The image source height
	 * 
	 * @var int 
	 */
	protected $_h;
	
	/**
	 * The image souce type
	 * 
	 * @var int
	 */
	protected $_t;
	
	/**
	 * The image souce ratio
	 * 
	 * @var float
	 */
	protected $_r;

	/**
	 * The final image quality compression 
	 * 
	 * @var int 
	 */
	protected $_q;
	
	/**
	 * Create a new image for resize.
	 * 
	 * @param type $filename The location of the image
	 * @param type $quality  The jpeg quality compression of the resized image
	 */
	public function __construct($filename, $quality = 90)
	{
		if (!defined(GD_MAJOR_VERSION))
		{
			throw new \Exception(0, 'GD library is not installed.');
		}
		
		if (GD_MAJOR_VERSION < 2)
		{
			throw new \Exception(0, 'GD library is too old, need at least version 2.0.');			
		}
		
		pixelpost\Filter::assume_string($filename);
		pixelpost\Filter::assume_int($quality);
		
		if (!file_exists($filename))
		{
			throw new \Exception(0, "Image '$filename' does not exists.");
		}
		
		$this->_src = $filename;
		
		list($this->_w, $this->_h, $this->_t) = getimagesize($this->_src);
		
		switch($this->_t)
		{
			case IMAGETYPE_BMP : break;
			case IMAGETYPE_GIF : break;
			case IMAGETYPE_JPEG: break;
			case IMAGETYPE_PNG : break;
			default : 
				throw new \Exception(0, "Image format is not supported.");
		}
		
		$this->_r = floatval(round($this->_w / $this->_h, 2));
		$this->_q = $quality;
	}

	/**
	 * Resizing the image and store the new image in $path;
	 * 
	 * @param  string $path the future image location
	 * @param  int    $w    the future image width
	 * @param  int    $h    the future image height
	 * @param  int    $x    the future image start (crop)
	 * @param  int    $y    the future image start (crop)
	 * @return bool 
	 */
	protected function _resize($path, $w, $h, $x = 0, $y = 0)
	{
		switch($this->_t)
		{
			case IMAGETYPE_BMP : $src = imagecreatefromwbmp($this->_src);
			case IMAGETYPE_GIF : $src = imagecreatefromgif($this->_src);
			case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($this->_src);
			case IMAGETYPE_PNG : $src = imagecreatefrompng($this->_src);
			default            : return false;
		}
		$dst = imagecreatetruecolor($w, $h);
		imagecopyresampled($dst, $src, 0, 0, $x, $y, $w, $h, $this->_w, $this->_h);
		imagejpeg($dst, $path, $this->_q);
		imagedestroy($dst);
		imagedestroy($src);
		return true;
	}

	/**
	 * Convert an image in jpg
	 *
	 * @param  string $path
	 * @return bool 
	 */
	public function convert_to_jpeg($path)
	{
		return $this->_resize($path, $this->_w, $this->_h);
	}
	
	/**
	 * Resize the image, keep ratio, to width X px.
	 * 
	 * @param  string $path  the future image location
	 * @param  int    $width the future image width
	 * @return bool
	 */
	public function resize_fixed_width($path, $width)
	{		
		if ($this->_w < $width) return false;				

		$height  = intval(round($width * ($this->_h / $this->_w), 0));

		return $this->_resize($path, $width, $height);
	}
		
	/**
	 * Resize the image, keep ratio, to height X px.
	 * 
	 * @param  string $path   the future image location
	 * @param  int    $height the future image height
	 * @return bool
	 */
	public function resize_fixed_height($path, $height)
	{
		if ($this->_h < $height) return false;

		$width  = intval(round($height * ($this->_w / $this->_h), 0));				
				
		return $this->_resize($path, $width, $height);		
	}
		
	/**
	 * Resize the image, keep ratio, to larger_border X px.
	 * 
	 * @param  string $path the future image location
	 * @param  int    $size the future image larger border size
	 * @return bool
	 */
	public function resize_larger_border($path, $size)
	{
		if ($this->_r >= 1)
		{
			if ($this->_w < $size) return false;
			$width  = $size;
			$height = intval(round($width * $this->_r, 0));
		}
		else
		{
			if ($this->_h < $size) return false;
			$height = $size;
			$width  = intval(round($height / $this->_r, 0));
		}
		
		return $this->_resize($path, $width, $height);
	}
		
	/**
	 * Resize the image, not keep ratio, to X px side square shape.
	 * 
	 * @param  string $path the future image location
	 * @param  int    $size the future image side size
	 * @return bool
	 */
	public function resize_square($path, $size)
	{
		if ($this->_w < $size || $this->_h < $size) return false;
		
		$x  = 0;
		$y  = 0;
		
		if ($this->_r != 1)
		{
			if ($this->_r > 1) $x = intval(round(($this->_w - $this->_h) / 2, 0));			
			else               $y = intval(round(($this->_h - $this->_w) / 2, 0));										
		}
		
		return $this->_resize($path, $size, $size, $x, $y);
	}

		
	/**
	 * Resize the image, not keep ratio.
	 * 
	 * @param  string $path   the future image location
	 * @param  int    $width  the future image width
	 * @param  int    $height the future image height
	 * @return bool
	 */
	public function resize_fixed($path, $width, $height)
	{
		if ($this->_w < $width || $this->_h < $height) return false;

		$x = 0;
		$y = 0;
		
		$ratio = round(floatval($width / $height), 2);
		
		if ($this->_r >= 1 && $ratio < 1 || $this->_r < 1 && $ratio >= 1)
		{
			$width  ^= $height;
			$height ^= $width;
			$width  ^= $height;
		}
		
		if ($this->_r != $ratio)
		{
			if ($this->_r < $ratio)
			{
				$z  = ($this->_r >= 1) ? 'y' : 'x';
				$$z = intval(round(($this->_h - ($this->_w / $ratio)) / 2, 0));							
			}
			else
			{
				$z  = ($this->_r >= 1) ? 'x' : 'y';
				$$z = intval(round(($this->_w - ($this->_h * $ratio)) / 2, 0));							
			}			
		}

		return $this->_resize($path, $width, $height, $x, $y);
	}
	
}