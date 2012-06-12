<?php

namespace pixelpost\plugins\admin\classes;

use ArrayAccess, Iterator, Countable;

/**
 * PluginList emulates a collection of Plugin object. Plugin object are created
 * on the fly for better ressources managment.
 *
 * @see pixelpost\plugins\admin\classes\Plugin
 * @see pixelpost\plugins\admin\classes\PluginManager
 */
class PluginList implements ArrayAccess, Iterator, Countable
{
	/**
	 * @var array A list of plugin's name.
	 */
	protected $list;

	/**
	 * @var int Internal usage for iterator implementation.
	 */
	protected $position = 0;

	/**
	 * Create a new collection of plugin
	 *
	 * @param array $list A list of plugin's name
	 */
	public function __construct(array $list)
	{
		$this->list = $list;
	}

	/**
	 * Export the collection to an array, this create an array of Plugin class.
	 *
	 * @return array
	 */
	public function to_array()
	{
		$l = array();
		foreach ($this as $value) $l[] = $value;
		return $l;
	}

	/**
	 * Countable interface
	 */
	public function count()
	{
		return count($this->list);
	}

	/**
	 * Iterator interface
	 */
	public function rewind()
	{
		$this->position = 0;
	}

	public function current()
	{
		return $this->offsetGet($this->position);
	}

	public function key()
	{
		return $this->position;
	}

	public function next()
	{
		++$this->position;
	}

	public function valid()
	{
		return $this->offsetExists($this->position);
	}

	/**
	 * ArrayAccess interface
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
		{
			$this->list[] = $value;
		}
		else
		{
			$this->list[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->list[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->list[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->list[$offset]) ? new Plugin($this->list[$offset]) : null;
	}
}