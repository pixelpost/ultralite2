<?php

namespace pixelpost;

/**
 * Provide template transformation to html methods.
 *
 * All is public in this class because this class work with lot of closure and
 * actually in php we can't bind a closure to an object so in a closure we can't
 * call protected or private data even if the closure is executed in this
 * object.
 *
 * @copyright  2011 Alban LEROUX <seza@paradoxal.org>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons
 * @version    0.0.1
 * @since      File available since Release 1.0.0
 */
class TemplateCompiler
{
	const INLINE_ECHO  = 1; // {{ .. }}
	const INLINE_PHP   = 2; // {: .. :}
	const INLINE_NOTAG = 3; // {[ .. ]}

	/**
	 * @var string The templates path
	 */
	public $path   = '';

	/**
	 * @var string The template data
	 */
	public $tpl    = '';

	/**
	 * @var array All template raw blocks
	 */
	public $raw    = array();

	/**
	 * @var array All template blocks
	 */
	public $block  = array();

	/**
	 * @var array Escaped quoted string are store here
	 */
	public $quote  = array();

	/**
	 * @var array Escaped squared [...] data are store here
	 */
	public $square = array();

	/**
	 * @var array Escaped parened (...) data are store here
	 */
	public $paren  = array();

	/**
	 * Replace all type of php open tag by <?php and fix the « eat newline
	 * after a php closing tag bug » in the template.
	 */
	public function replace_php_short_open_tag()
	{
		$this->tpl = str_replace(
			array("\r\n", '<?php', '<?xml', '<?',    '<?php=',     '<#', "?>\n"),
			array("\n",   '<#php', '<#xml', '<?php', '<?php echo', '<?', "?>\n\n"),
			$this->tpl);
	}

	/**
	 * Remove all template comment
	 */
	public function remove_comment()
	{
		$this->replace_tag($this->tpl, '{#', '#}', function() { return ''; });
	}

	/**
	 * Protect all escaped string in the template.
	 */
	public function escape_escape()
	{
		$this->tpl = str_replace(
			array('{{ \'{{\' }}', '{{ \'}}\' }}', '{{ \'{%\' }}', '{{ \'%}\' }}', '{{ \'{:\' }}', '{{ \':}\' }}'),
			array('-|<@>|-',      '-|</@>|-',     '-|<*>|-',      '-|</*>|-',     '-|<§>|-',      '-|</§>|-'),
			$this->tpl);
	}

	/**
	 * Restore all protected escaped string in the template.
	 */
	public function unescape_escape()
	{
		$this->tpl = str_replace(
			array('-|<@>|-', '-|</@>|-', '-|<*>|-', '-|</*>|-', '-|<§>|-', '-|</§>|-'),
			array('{{',      '}}',       '{%',      '%}',       '{:',      ':}'),
			$this->tpl);
	}

	/**
	 * Protect all quote in $data by replacing them by a --#-- form.
	 *
	 * The $quote internal array is fill like:
	 * '--0--' : '\'quote 1\'',
	 * '--1--' : '"quote 2"',
	 * '--3--' : '"quote 3"', ...
	 *
	 * Each new call to this method erase all previously protected quote.
	 *
	 * @param string $data
	 */
	public function escape_quote(&$data)
	{
		$this->quote = array();

		$me = $this;

		$callback = function($inQuote, $open) use ($me)
		{
			$index = '--' . count($me->quote) . '--';

			$me->quote[$index] = $open . $inQuote . $open;

			return $index;
		};

		$double = strpos($data , '"');
		$simple = strpos($data , '\'');

		if ($double === false)
		{
			$this->replace_tag($data, '\'', '\'', $callback);
		}
		elseif ($simple === false)
		{
			$this->replace_tag($data, '"',  '"',  $callback);
		}
		elseif ($double < $simple)
		{
			$this->replace_tag($data, '"',  '"',  $callback);
			$this->replace_tag($data, '\'', '\'', $callback);
		}
		else
		{
			$this->replace_tag($data, '\'', '\'', $callback);
			$this->replace_tag($data, '"',  '"',  $callback);
		}
	}

	/**
	 * Restore all quote in $data previously protected.
	 *
	 * @param string $data
	 */
	public function unescape_quote(&$data)
	{
		$data = str_replace(array_keys($this->quote), array_values($this->quote), $data);
	}

	/**
	 * Protect all square in $data by replacing them by a §§#§§ form.
	 *
	 * The $square internal array is fill like:
	 * '§§0§§' : '[square 1]',
	 * '§§1§§' : '[square 2]',
	 * '§§3§§' : '[square 3]', ...
	 *
	 * Each new call to this method erase all previously protected square.
	 *
	 * @param string $data
	 */
	public function escape_square(&$data)
	{
		$this->square = array();

		$me = $this;

		$callback = function($inSquare) use ($me)
		{
			$index = '§§' . count($me->square) . '§§';

			$me->square[$index] = '[' . $inSquare . ']';

			return $index;
		};

		$this->replace_tag($data, '[',  ']',  $callback, true);
	}

	/**
	 * Restore all square previously protected
	 *
	 * @param string $data
	 */
	public function unescape_square(&$data)
	{
		$this->square = array_reverse($this->square, true);

		$data = str_replace(array_keys($this->square), array_values($this->square), $data);
	}

	/**
	 * Protect all paren in $data by replacing them by a ::#:: form.
	 *
	 * The $paren internal array is fill like:
	 * '::0::' : '(paren 1)',
	 * '::1::' : '(paren 2)',
	 * '::3::' : '(paren 3)', ...
	 *
	 * Each new call to this method erase all previously protected paren.
	 *
	 * @param string $data
	 */
	public function escape_paren(&$data)
	{
		$this->paren = array();

		$me = $this;

		$callback = function($inSquare) use ($me)
		{
			$index = '::' . count($me->paren) . '::';

			$me->paren[$index] = '(' . $inSquare . ')';

			return $index;
		};

		$this->replace_tag($data, '(',  ')',  $callback, true);
	}

	/**
	 * Restore all paren previously protected
	 *
	 * @param string $data
	 */
	public function unescape_paren(&$data)
	{
		$this->paren = array_reverse($this->paren, true);

		$data = str_replace(array_keys($this->paren), array_values($this->paren), $data);
	}

	/**
	 * Escape all raw block in the template.
	 * Replace {% raw %} ... {% endraw %} by {% RAW # %}
	 *
	 * The internal $raw array is fill like :
	 * '{% RAW 0 %}' : 'raw data 0',
	 * '{% RAW 1 %}' : 'raw data 1',
	 * '{% RAW 2 %}' : 'raw data 2', ...
	 *
	 */
	public function escape_raw_block()
	{
		$me = $this;

		$todo = function($data) use ($me)
		{
			$index           = '{% RAW ' . count($me->raw) . ' %}';
			$me->raw[$index] = $data;

			return $index;
		};

		$this->replace_tag($this->tpl, '{% raw %}', '{% endraw %}', $todo);
	}

	/**
	 * Unescape all raw block by replace all {% RAW # %} by it's content.
	 */
	public function unescape_raw_block()
	{
		$this->tpl = str_replace(array_keys($this->raw), array_values($this->raw), $this->tpl);
	}

	/**
	 * Extract all block content in the template.
	 * All {% block name %}...{% endblock %} are replaced by {% BLOCK name %}
	 * All {% display name %} are replaced by {% BLOCK name %}
	 *
	 * The internal $block array is fill like:
	 * '{% BLOCK name %}' : 'content of the block',
	 * '{% BLOCK name %}' : 'content of the block',
	 * '{% BLOCK name %}' : 'content of the block', ...
	 *
	 * If a block named Z is allready in $block and a new block Z is discovered,
	 * the {% child %} tag in the discovered block is replaced by the content
	 * in the $block[Z]. (Don't forget that child are declared before parent).
	 *
	 * Finally, the $block[Z] value is replaced by the new content.
	 *
	 * @return type
	 */
	public function extract_block()
	{
		$me = $this;

		$callback = function($data, $open) use ($me)
		{
			if ($is_display = ($open == '{% display '))
			{
				$name  = $data;
				$block = '{% child %}';
			}
			else
			{
				list($name, $block) = explode(' %}', $data, 2);
				$block = trim($block);
			}

			$name = '{% BLOCK ' . trim($name) . ' %}';

			$replace = isset($me->block[$name]) ? $me->block[$name] : '';

			$me->block[$name] = str_replace('{% child %}', $replace, $block);

			return $name . ($is_display ? '' : '{% endblock');
		};

		$this->replace_tag($this->tpl, '{% display ' , ' %}'        , $callback);
		$this->replace_tag($this->tpl, '{% block '   , '{% endblock', $callback);
		$this->replace_tag($this->tpl, '{% endblock ', '%}'         , function() { return ''; });
	}

	/**
	 * Replace all {% BLOCK name %} by it's content in the template.
	 */
	public function compile_block()
	{
		$this->block = array_reverse($this->block, true);
		$names       = array_keys($this->block);
		$values      = array_values($this->block);
		$this->tpl   = str_replace($names, $values, $this->tpl);
	}

	/**
	 * Replace all IF, ELSEIF, ELSE statement by its php equivalent in the
	 * template.
	 *
	 * All if, elseif condition are push between {[ and ]} to be parsed inline
	 * in future.
	 *
	 * {[ .. ]} content should interpreted as the same as {{ .. }} or {: .. :}
	 * but didn't need php open and close tag <?php ... ?>
	 */
	public function make_if()
	{
		$callback = function($data, $open)
		{
			return sprintf('<?php %s ({[ %s ]}) : ?>', substr($open, 3, -1), $data);
		};

		$this->replace_tag($this->tpl, '{% if ',     ' %}', $callback);
		$this->replace_tag($this->tpl, '{% elseif ', ' %}', $callback);

		$this->tpl = str_replace('{% else %}',  '<?php else : ?>', $this->tpl);
		$this->tpl = str_replace('{% endif %}', '<?php endif ?>', $this->tpl);
	}

	/**
	 * Replace all FOR statement by its php equivalent in the template.
	 *
	 * A local loop object is instanciate to help the designer of the template
	 * to work with the loop.
	 *
	 * In the for loop all use of global key / value / loop var are replaced by
	 * its local #key / #value / #loop equivalent.
	 * The local #loop var are follow by a number to remove conflict in
	 * imbriqued loop.
	 *
	 * All for « array » are push between {[ and ]} to be parsed inline
	 * in future.
	 *
	 * {[ .. ]} content should interpreted as the same as {{ .. }} or {: .. :}
	 * but didn't need php open and close tag <?php ... ?>
	 *
	 * Exemple:
	 * {% v in array|sort %} {{ v }} {% endfor %}
	 *
	 * Become (simplified):
	 * <?php for ({[ array|sort ]} as $v): ?>{{ #v }}<?php endfor ?>;
	 *
	 */
	public function make_for()
	{
		$me = $this;

		$callback = function($data) use ($me)
		{
			static $loopIndex = 0;

			$loopIndex++;

			list($var,   $data) = explode(' in ', $data, 2);
			list($array, $data) = explode(' %}',  $data, 2);

			$varkey   = array_reverse(explode(',', $var));
			$var      = trim(array_shift($varkey));
			$key      = trim(array_shift($varkey));
			$endBlock = sprintf('<?php endforeach; unset($loop%s); ', $loopIndex);

			$loop = sprintf('<?php $loop%s = new \pixelpost\TemplateLoop({[ %s ]}); ', $loopIndex, $array);

			if ($key == '') $block = sprintf('foreach ({[ %s ]} as $%s) : ', $array, $var);
			else            $block = sprintf('foreach ({[ %s ]} as $%s => $%s) : ', $array, $key, $var);

			$block .= sprintf('$loop%s->iterate(); ?>', $loopIndex);

			$callback = function($data, $type) use ($me, $loopIndex, $key, $var)
			{
				$data = trim($data);

				$me->extract_var($data, function($data) use ($me, $loopIndex, $key, $var)
				{
					$v = explode('|', $data);
					$v = array_shift($v);
					$v = explode('.', $v);
					$v = array_shift($v);

					if ($v == 'loop') return '#loop' . $loopIndex . substr($data, 4);
					if ($v == $var)   return '#' . $var . substr($data, strlen($var));
					if ($v == $key)   return '#' . $key . substr($data, strlen($key));

					return $data;
				});

				$class = __CLASS__;

				switch($type)
				{
					case $class::INLINE_ECHO : $format = '{{ %s }}'; break;
					case $class::INLINE_PHP  : $format = '{: %s :}'; break;
					default :                  $format = '{[ %s ]}'; break;
				}

				return sprintf($format, $data);
			};

			$me->replace_inline($data, $callback);

			if (strpos($data, '{% elsefor %}') !== false)
			{
				list($data, $else) = explode('{% elsefor %}', $data, 2);

				$loop     .= sprintf('if ($loop%s->length > 0) : ', $loopIndex);
				$endBlock .= sprintf('else : ?>%s<?php endif ', $else);
			}

			return $loop . $block . $data . $endBlock . '?>';
		};

		$this->replace_tag($this->tpl, '{% for ', '{% endfor %}', $callback, true);
	}

	/**
	 * Replace all {% extends file %} tag by nothing in the template.
	 * If there is a first tag (next tag are ignored), replace the template
	 * content by a new template content from extends 'file'.
	 *
	 * 'file' is always a relative path to PLUG_PATH (eg. the plugins directory).
	 */
	public function make_extends()
	{
		$extends = array();

		$callback = function($data) use (&$extends)
		{
			$extends[] = $data;
			return '';
		};

		$this->replace_tag($this->tpl, '{% extends ', ' %}', $callback);

		$filename = array_shift($extends);

		if (is_null($filename)) return;

		$filename = str_replace('/', SEP, trim($filename));

		if ($filename == '') throw Error::create(20);

		$file = $this->path . $filename;

		if (!file_exists($file)) throw Error::create(21, array($file));

		$this->tpl = file_get_contents($file);

		$this->escape_raw_block();
		$this->remove_comment();
		$this->escape_escape();
		$this->extract_block();
		$this->make_extends();
	}

	/**
	 * Replace all {% include file %} tag by the content of 'file' in the
	 * template.
	 *
	 * 'file' is always a relative path to PLUG_PATH (eg. the plugins directory).
	 */
	public function make_include()
	{
		$includes = array();

		$callback = function($data) use (&$includes)
		{
			$index            = '{% INCLUDE#' . count($includes) . ' %}';
			$includes[$index] = $data;
			return $index;
		};

		$this->replace_tag($this->tpl, '{% include "', '" %}', $callback);

		foreach($includes as $id => $include)
		{
			$filename = str_replace('/', SEP, trim($include));

			if ($filename == '') throw Error::create(22);

			$file = $this->path . $filename;

			if (!file_exists($file)) throw Error::create(23, array($file));

			$replace = file_get_contents($file);

			$this->tpl = str_replace($id, $replace, $this->tpl);
		}
	}

	/**
	 * Replace all inline content {{ .. }}, {: .. :}, {[ .. ]} by its PHP
	 * equivalent form in the template.
	 */
	public function make_inline()
	{
		$me = $this;

		$callback = function($data, $type) use ($me)
		{
			$me->extract_var($data, function($data) use ($me)
			{
				return $me->make_var($data);
			});

			$class = __CLASS__;

			switch($type)
			{
				case $class::INLINE_ECHO : $format = '<?php echo %s ?>'; break;
				case $class::INLINE_PHP  : $format = '<?php %s ?>';      break;
				default:                   $format = '%s';               break;
			}

			return sprintf($format, $data);
		};

		$this->replace_inline($this->tpl, $callback, false);
	}

	/**
	 * Extract all template var from $data and apply $todo to them before to
	 * replace them in $data.
	 *
	 * $todo need to be a callback function that accept one string argument and
	 * return a string value.
	 *
	 * @param string $data
	 * @param \Closure $todo
	 */
	public function extract_var(&$data, \Closure $todo)
	{
		$me = $this;

		$callback = function(&$data) use ($todo)
		{
			$var    = '(@|#)?[a-z-][a-z0-9:§._-]*';
			$filter = '\|[a-z][a-z0-9_-]*';
			$arg    = '::[0-9]+::';
			$regex  = "/($var|$arg)($filter($arg)?)*/i";

			$callback = function($match) use ($todo) { return $todo($match[0]); };

			$data = preg_replace_callback($regex, $callback, $data);
		};

		$this->escape_paren($data);

		array_walk($this->paren, function (&$item) use ($me, $callback)
		{
			$me->escape_square($item);
			array_walk($me->square, $callback);
			$callback($item);
			$me->unescape_square($item);
		});

		$this->escape_square($data);

		array_walk($this->square, $callback);

		$callback($data);

		$this->unescape_square($data);
		$this->unescape_paren($data);
	}

	/**
	 * Return a PHP equivalent of $data (a template variable eg. my_var|upper ).
	 *
	 * @param string $data
	 * @return string
	 */
	public function make_var($data)
	{
		$filters = explode('|', $data);

		$var = array_shift($filters);

		switch($var)
		{
			case 'null'  : break;
			case 'false' : break;
			case 'true'  : break;
			case 'array' : break;
			default:
				switch(substr($var, 0, 1))
				{
					case ':' : break;                               // paren
					case '-' : break;                               // quote
					case '@' : $var = substr($var, 1); break;       // constant
					case '#' : $var = '$' . substr($var, 1); break; // local
					default  : $var = '$this.' . $var; break;      // template
				}

				$addBraceToHyphensName = function($item)
				{
					return (strpos($item, '-') === false) ? $item : "{'$item'}";
				};

				if (strpos($var, '.') !== false)
				{
					$var = str_replace('->', '<>', $var);
					$var = implode('->', array_map($addBraceToHyphensName, explode('.', $var)));
					$var = str_replace('<>', '->', $var);
				}

				break;
		}

		foreach($filters as $filter) $var = sprintf($this->parse_filter($filter), $var);

		return $var;
	}

	/**
	 * Return a string format to apply a filter to a variable.
	 *
	 * @param string $data
	 * @return string
	 */
	public function parse_filter($data)
	{
		$params = explode('::', $data, 3);

		$filter = array_shift($params);

		$param = (count($params) > 0) ? '::' . $params[0] . '::' : false;

		if ($param) $this->paren[$param] = substr($this->paren[$param], 1, -1);

		switch($filter)
		{
			// mixed
			case 'exists'  : return 'isset(%s)';
			case 'empty'   : return 'empty(%s)';
			case 'default' : return '$this->_filter_default(%s, ' . $param . ')';
			case 'if'      : return '$this->_filter_if(%s, ' . $param . ')';
			// string
			case 'reverse' : return 'strrev(%s)';
			case 'br'      : return 'nl2br(%s)';
			case 'strip'   : return 'strip_tags(%s)';
			case 'url'     : return 'urlencode(%s)';
			case 'base64'  : return 'base64_encode(%s)';
			case 'escape'  : return '$this->_filter_escape(%s)';
			case 'len'     : return 'mb_strlen(%s, \'UTF-8\')';
			case 'upper'   : return 'mb_strtoupper(%s, \'UTF-8\')';
			case 'lower'   : return 'mb_strtolower(%s, \'UTF-8\')';
			case 'capital' : return 'ucFirst(mb_strtolower(%s, \'UTF-8\'))';
			case 'title'   : return 'ucWords(mb_strtolower(%s, \'UTF-8\'))';
			case 'sub'     : return 'mb_substr(%s, ' . $param . ', \'UTF-8\')';
			case 'replace' : return 'str_replace(' . $param . ', %s )';
			case 'split'   : return 'explode(' . $param . ', %s)';
			// math
			case 'number'  : return '$this->_filter_number(%s)';
			case 'between' : return '$this->_filter_between(%s, ' . $param . ')';
			case 'even'    : return '((%s % 2) == 0)';
			case 'odd'     : return '((%s % 2) == 1)';
			case 'abs'     : return 'abs(%s)';
			case 'neg'     : return '(%s * -1)';
			// array
			case 'first'   : return '$this->_filter_array_first(%s)';
			case 'last'    : return '$this->_filter_array_last(%s)';
			case 'sort'    : return '$this->_filter_array_sort(%s)';
			case 'rsort'   : return '$this->_filter_array_rsort(%s)';
			case 'nsort'   : return '$this->_filter_array_nsort(%s)';
			case 'length'  : return 'count(%s)';
			case 'keys'    : return 'array_keys(%s)';
			case 'values'  : return 'array_values(%s)';
			case 'join'    :
				if ($param) return 'implode(' . $param . ', %s)';
				else        return 'implode(\' \', %s)';
			// datetime
			case 'date':
			case 'datetime':
			case 'time':
				if (!$param) $param = '\'default\'';
				return "\$this->_filter_date(%s, '$filter', $param)";
			// event
			case 'event'   : return '$this->_event_signal(%s)';
		}

		return '%s';
	}

	/**
	 * Search all inline tag in $data and apply $todo to them.
	 *
	 * $todo need to be a callback function that accept one string argument and
	 * return a string value.
	 * A second argument is sent to $todo and represent the type of inlined
	 * content (see constant INLINE_*)
	 *
	 * @param string   $data
	 * @param \Closure $todo
	 */
	public function replace_inline(&$data, \Closure $todo)
	{
		$me = $this;

		$callback = function($data, $open) use ($todo, $me)
		{
			$data = trim($data);

			$me->escape_quote($data);

			$class = __CLASS__;

			switch ($open)
			{
				case '{{' : $type = $class::INLINE_ECHO;  break;
				case '{:' : $type = $class::INLINE_PHP;   break;
				default   : $type = $class::INLINE_NOTAG; break;
			}

			$data = $todo($data, $type);

			$me->unescape_quote($data);

			return $data;
		};

		$this->replace_tag($data, '{{', '}}', $callback); // <?php echo ...
		$this->replace_tag($data, '{:', ':}', $callback); // <?php ...
		$this->replace_tag($data, '{[', ']}', $callback); // ...
	}

	/**
	 * Search all string in $data, starting by $openTag and finished by
	 * $closeTag, and replace them by the result of $todo($data).
	 *
	 * $todo need to be a closure which accept 3 string parameter:
	 * - the matched data discovered (tag excluded).
	 * - the open tag
	 * - the close tag
	 * And must return a replacement string.
	 *
	 * You can use $includedFirst to true if you want the method take care of
	 * possible included tag.
	 *
	 * $startAt and $includeLvl argument and the return value are used internaly
	 * for the recursion.
	 *
	 * @param string  $data
	 * @param string  $openTag
	 * @param string  $closeTag
	 * @param Closure $todo
	 * @param bool    $includedFirst
	 * @param int     $startAt
	 * @param int     $includeLvl
	 * @return int
	 */
	public function replace_tag(&$data, $openTag, $closeTag, \Closure $todo, $includedFirst = false, $startAt = 0, $includeLvl = 0)
	{
		// the len of the openTag, closeTag
		$openLen  = mb_strlen($openTag, 'UTF-8');
		$closeLen = mb_strlen($closeTag, 'UTF-8');

		// this is like a tail reccursion
		while (true) :

			// the len of data
			$dataLen  = mb_strlen($data, 'UTF-8');

			// Let's go we find the first open tag after $startAt position.
			$start = mb_strpos($data, $openTag, $startAt, 'UTF-8');

			// No open tag we return were we are in the scan.
			if ($start === false) return $startAt;

			// the content start after the start tag (we exclude it).
			$startAt = $start + $openLen;

			// now we find the close tag or eat all the
			// rest of data if there no close tag.
			$stop = mb_strpos($data, $closeTag, $startAt, 'UTF-8') ?: $dataLen;

			// if we should take care of included tag
			if ($includedFirst)
			{
				// we look for the next open tag
				$nextStart = mb_strpos($data, $openTag, $startAt, 'UTF-8');

				// if an other open tag exists and its position is before our close tag
				while ($nextStart !== false && $nextStart < $stop)
				{
					// we recurse by starting after our open tag
					// and get were the recursion ended in the data.
					$startAt = $this->replace_tag($data, $openTag, $closeTag, $todo, $includedFirst, $startAt, ++$includeLvl);

					--$includeLvl;

					// calc the new data length
					$dataLen = mb_strlen($data, 'UTF-8');
					// calc the new start tag
					$nextStart = mb_strpos($data, $openTag, $startAt, 'UTF-8');
					// and we redo the search of a close tag
					$stop = mb_strpos($data, $closeTag, $startAt, 'UTF-8') ?: $dataLen;
				}
			}

			// where start the content to replace and it's len
			$contentStart = $start + $openLen;
			$contentLen   = $stop - $contentStart;

			// we get the data to replace
			$content = mb_substr($data, $contentStart, $contentLen, 'UTF-8');

			// retreive the replacement data
			$content = $todo($content, $openTag, $closeTag);

			// and replace it into the string (we don't use substr_replace()
			// because there is no mb_substr_replace() function).
			$data = mb_substr($data, 0, $start, 'UTF-8')
				  . $content
				  . mb_substr($data, $stop + $closeLen, $dataLen, 'UTF-8');

			// the new point to start the search
			$startAt = $start + mb_strlen($content, 'UTF-8');

			// if we are not in recursion, we follow our research
			if ($includeLvl != 0) return $startAt;

		// and we search again
		endwhile;
	}
}