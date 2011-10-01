<?php

namespace pixelpost;

/**
 * Provide utility to works with for loop in template language.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class TemplateLoop
{
    public $index     = 0;
    public $index0    = -1;
    public $revindex  = 0;
    public $revindex0 = 0;
    public $length    = 0;
    public $first     = false;
    public $last      = false;

	/**
	 * Generate a new TemplateLoop Object. The argument could be an array or an 
	 * instance of a Traversable and Countable object.
	 * 
	 * @param array $array 
	 */
    public function __construct($array)
    {
        if (!is_array($array))
        {
            if (!is_object($array))                  throw Error::create(17);
			elseif (!$array instanceof \Traversable) throw Error::create(18);
			elseif (!$array instanceof \Countable)   throw Error::create(19);
        }

        $this->length    = count($array);
        $this->revindex  = $this->length + 1;
        $this->revindex0 = $this->length;
    }

	/**
	 * Step all counter.
	 * 
	 */
    public function iterate()
    {
        $this->index++;
        $this->index0++;
        $this->revindex--;
        $this->revindex0--;

        $this->first = ($this->revindex == $this->length);
        $this->last  = ($this->index    == $this->length);
    }
}
