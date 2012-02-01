<?php

namespace pixelpost;

/**
 * Test class for TemplateCompiler.
 */
class TemplateCompilerTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var TemplateCompiler
	 */
	protected $object;

	protected function setUp()
	{
		$this->object = new TemplateCompiler;
	}

	protected function tearDown()
	{		
	}

	/**
	 * @covers pixelpost\TemplateCompiler::replace_php_short_open_tag
	 */
	public function test_replace_php_short_open_tag()
	{
		$this->object->tpl = '<?xml <?php <? <?= test ?> ?> ?> ?>';

		$this->object->replace_php_short_open_tag();
		
		$this->assertSame('<?xml <?php <?php <?php echo test ?> ?> ?> ?>', $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::remove_comment
	 */
	public function test_remove_comment()
	{
		$test = <<<EOF
This is {# comment #} a {# multi line
comment #} commented line.
EOF;
		$result = <<<EOF
This is  a  commented line.
EOF;
		
		$this->object->tpl = $test;
		$this->object->remove_comment();
		
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::escape_escape
	 * @covers pixelpost\TemplateCompiler::unescape_escape
	 */
	public function test_escape_escape()
	{
		$test = "This is {{ '{{' }} a {{ '}}' }} line {{ '{%' }} containing "
			  . "{{ '%}' }} {{ '{:' }} reserved sequences {{ ':}' }}.";		
		
		$result = 'This is {{ a }} line {% containing %} {: reserved sequences :}.';
		
		$this->object->tpl = $test;
		$this->object->escape_escape();
		$this->object->unescape_escape();
		
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::escape_quote
	 * @covers pixelpost\TemplateCompiler::unescape_quote
	 */
	public function test_escape_quote()
	{
		$test = 'This is \'quoted word\', normal words and "words" in quotes.';				
		$inter = 'This is --0--, normal numbers and --1-- in marks.';
		$result = 'This is \'quoted word\', normal numbers and "words" in marks.';
		
		$this->object->escape_quote($test);
		
		$test = str_replace('quote', 'mark', $test);
		$test = str_replace('word', 'number', $test);
		
		$this->assertSame($inter, $test);		
		
		$this->assertArrayHasKey('--0--', $this->object->quote);
		$this->assertArrayHasKey('--1--', $this->object->quote);
		
		$this->assertSame("'quoted word'", $this->object->quote['--0--']);
		$this->assertSame('"words"',       $this->object->quote['--1--']);
		
		$this->object->unescape_quote($test);
		
		$this->assertSame($result, $test);		
	}

	/**
	 * @covers pixelpost\TemplateCompiler::escape_square
	 * @covers pixelpost\TemplateCompiler::unescape_square
	 */
	public function test_escape_square()
	{
		$test = 'try escape this foo.bar[baz["foo"|upper]]["foo"]|lower';
		
		$inter = 'TRY ESCAPE THIS FOO.BAR§§1§§§§2§§|LOWER';		
		
		$result = 'TRY ESCAPE THIS FOO.BAR[baz["foo"|upper]]["foo"]|LOWER';
		
		$this->object->escape_square($test);
		
		$test = strtoupper($test);
		
		$this->assertArrayHasKey('§§0§§', $this->object->square);
		$this->assertArrayHasKey('§§1§§', $this->object->square);
		$this->assertArrayHasKey('§§2§§', $this->object->square);
		
		$this->assertSame('["foo"|upper]', $this->object->square['§§0§§']);
		$this->assertSame('[baz§§0§§]',    $this->object->square['§§1§§']);
		$this->assertSame('["foo"]',       $this->object->square['§§2§§']);

		$this->assertSame($inter, $test);		
		
		$this->object->unescape_square($test);
		
		$this->assertSame($result, $test);		
	}

	/**
	 * @covers pixelpost\TemplateCompiler::escape_paren
	 * @covers pixelpost\TemplateCompiler::unescape_paren
	 */
	public function test_escape_paren()
	{
		$test = 'try escape this foo.bar(baz("foo"|upper))("foo")|lower';
		
		$inter = 'TRY ESCAPE THIS FOO.BAR::1::::2::|LOWER';		
		
		$result = 'TRY ESCAPE THIS FOO.BAR(baz("foo"|upper))("foo")|LOWER';
		
		$this->object->escape_paren($test);
		
		$test = strtoupper($test);
		
		$this->assertArrayHasKey('::0::', $this->object->paren);
		$this->assertArrayHasKey('::1::', $this->object->paren);
		$this->assertArrayHasKey('::2::', $this->object->paren);
		
		$this->assertSame('("foo"|upper)', $this->object->paren['::0::']);
		$this->assertSame('(baz::0::)',    $this->object->paren['::1::']);
		$this->assertSame('("foo")',       $this->object->paren['::2::']);

		$this->assertSame($inter, $test);		
		
		$this->object->unescape_paren($test);
		
		$this->assertSame($result, $test);		
	}

	/**
	 * @covers pixelpost\TemplateCompiler::escape_raw_block
	 * @covers pixelpost\TemplateCompiler::unescape_raw_block
	 */
	public function test_escape_raw_block()
	{
		$test = 'This is a demonstration: {% raw %}{% if myVar.foo %}{{ myVar.foo|upper }}{% endif %}{% endraw %}.';
		
		$inter = 'This is a demonstration: {% RAW 0 %}.';
		
		$escaped = '{% if myVar.foo %}{{ myVar.foo|upper }}{% endif %}';
		
		$result = 'This is a demonstration: {% if myVar.foo %}{{ myVar.foo|upper }}{% endif %}.';
		
		$this->object->tpl = $test;
		$this->object->escape_raw_block();
		
		$this->assertArrayHasKey('{% RAW 0 %}', $this->object->raw);
		
		$this->assertSame($escaped, $this->object->raw['{% RAW 0 %}']);

		$this->assertSame($inter, $this->object->tpl);		
		
		$this->object->unescape_raw_block();
		
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_block
	 */
	public function test_extract_block()
	{
		$test = 'Some text {% block title %}my title{% endblock %} and other '
		      . '{% block other %}other data{% endblock other %}.';
		
		$result = 'Some text {% BLOCK title %} and other {% BLOCK other %}.';
		
		$this->object->tpl = $test;
		
		$this->object->extract_block();
		
		$this->assertArrayHasKey('title', $this->object->block);
		$this->assertArrayHasKey('other', $this->object->block);
		$this->assertSame('my title', $this->object->block['title']);
		$this->assertSame('other data', $this->object->block['other']);
		
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_block
	 */
	public function test_extract_block_with_parent()
	{
		$test = 'Some text {% block title %}my title{% endblock %} and other '
		      . '{% block other %}other data{% endblock other %}.';
		
		$result = 'Some text {% BLOCK title %} and other {% BLOCK other %}.';
		
		$this->object->tpl = $test;
		
		$this->object->block['title'] = 'one title';
		$this->object->block['other'] = 'data and {% parent %}';
		
		$this->object->extract_block();
		
		$this->assertArrayHasKey('title', $this->object->block);
		$this->assertArrayHasKey('other', $this->object->block);
		
		$this->assertSame('one title', $this->object->block['title']);
		$this->assertSame('data and other data', $this->object->block['other']);
		
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::compile_block
	 */
	public function test_compiler_block()
	{
		$test = 'my test: {% BLOCK foo %} {% BLOCK bar %} and {% BLOCK baz %}';
		$foo = 'hello';
		$bar = 'world';
		$baz = 'people';
		$result = 'my test: hello world and people';
		
		$this->object->tpl = $test;		
		$this->object->block['foo'] = $foo;
		$this->object->block['bar'] = $bar;
		$this->object->block['baz'] = $baz;
		$this->object->compile_block();
		
		$this->assertSame($result, $this->object->tpl);		
	}


	/**
	 * @covers pixelpost\TemplateCompiler::make_if
	 */
	public function test_make_if()
	{
		$test = '{% if something %} do something '
		      . '{% elseif otherthing %} do otherthing '
			  . '{% else %} do nothing '
			  . '{% endif %}';
		
		$result = '<?php if ({[ something ]}) : ?> do something '
		        . '<?php elseif ({[ otherthing ]}) : ?> do otherthing '
			    . '<?php else : ?> do nothing '
			    . '<?php endif ?>';
		
		$this->object->tpl = $test;
		$this->object->make_if();
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_for
	 */
	public function test_make_for_values()
	{
		$test = '{% for v in my_array %} this is the loop: {{ loop.index }} => {{ v }} {% endfor %}';
		
		$result = '<?php $loop1 = new \pixelpost\TemplateLoop({[ my_array ]}); '
		        . 'foreach ({[ my_array ]} as $v) : '
				. '$loop1->iterate(); ?> '
		        . 'this is the loop: {{ #loop1.index }} => {{ #v }} '
			    . '<?php endforeach; unset($loop1); ?>';
		
		$this->object->tpl = $test;
		$this->object->make_for();
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_for
	 */
	public function test_make_for_keys_values()
	{
		$test = '{% for k,v in my_array %} this is the loop: {{ k }} => {{ v }} {% endfor %}';
		
		$result = '<?php $loop1 = new \pixelpost\TemplateLoop({[ my_array ]}); '
		        . 'foreach ({[ my_array ]} as $k => $v) : '
				. '$loop1->iterate(); ?> '
		        . 'this is the loop: {{ #k }} => {{ #v }} '
			    . '<?php endforeach; unset($loop1); ?>';
		
		$this->object->tpl = $test;
		$this->object->make_for();
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_for
	 */
	public function test_make_for_else()
	{
		$test = '{% for v in my_array %} do something {% elsefor %} do otherthing {% endfor %}';
		
		$result = '<?php $loop1 = new \pixelpost\TemplateLoop({[ my_array ]}); '
		        . 'if ($loop1->length > 0) : '
		        . 'foreach ({[ my_array ]} as $v) : '
				. '$loop1->iterate(); ?> '
		        . 'do something '
			    . '<?php endforeach; unset($loop1); else : ?> '
				. 'do otherthing '
				. '<?php endif ?>';
		
		$this->object->tpl = $test;
		$this->object->make_for();
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_for
	 */
	public function test_make_for_included()
	{
		$test = '{% for v in my_array %}{{ loop.index }} => {{ v }}{% for a in arr %}{{ loop.index }}=>{{ a }}{% endfor %}{% endfor %}';
		
		$result = '<?php $loop2 = new \pixelpost\TemplateLoop({[ my_array ]}); '
		        . 'foreach ({[ my_array ]} as $v) : '
				. '$loop2->iterate(); ?>'
		        . '{{ #loop2.index }} => {{ #v }}'
		        . '<?php $loop1 = new \pixelpost\TemplateLoop({[ arr ]}); '
		        . 'foreach ({[ arr ]} as $a) : '
				. '$loop1->iterate(); ?>'
		        . '{{ #loop1.index }}=>{{ #a }}'
			    . '<?php endforeach; unset($loop1); ?>'
			    . '<?php endforeach; unset($loop2); ?>';
		
		$this->object->tpl = $test;
		$this->object->make_for();
		$this->assertSame($result, $this->object->tpl);
	}
	
	/**
	 * @todo Implement test_make_extends().
	 */
	public function test_make_extends()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}
	
	/**
	 * @todo Implement test_make_include().
	 */
	public function test_make_include()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}
	
	/**
	 * @covers pixelpost\TemplateCompiler::make_inline
	 */
	public function test_make_inline()
	{
		$test = 'This is my {{ name|upper }}: new {: #test|if("feature", "fonctionnality") :}.';
		$result = 'This is my <?php echo mb_strtoupper($this->name, \'UTF-8\') ?>: new <?php $this->_filter_if($test, "feature", "fonctionnality") ?>.';
		
		$this->object->tpl = $test;
		$this->object->make_inline();
		
		$this->assertSame($result, $this->object->tpl);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_return_value()
	{
		$test   = '1 + 2 + foo + 23';
		$result = '1 + 2 + bar + 23';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->any())
			 ->method('auto')
			 ->will($this->returnValue('bar'));
		
		$callback = function($data) use ($mock) { return $mock->auto($data); };
		
		$this->object->extract_var($test, $callback);
		
		$this->assertSame($result, $test);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_match_var()
	{
		$test   = '1 + 2 + foo + 23';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(1))
			 ->method('auto')
			 ->with($this->equalTo('foo'));
		
		$callback = function($data) use ($mock) { return $mock->auto($data); };
		
		$this->object->extract_var($test, $callback);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_in_paren()
	{
		$test   = '1 + 2 + (foo == bar) + 23';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(3)) // 1 for (...), 1 for foo, 1 for bar
			 ->method('auto');
		
		$callback = function($data) use ($mock) { return $mock->auto($data); };
		
		$this->object->extract_var($test, $callback);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_match_object()
	{
		$test   = '1 + 2 + foo.bar + 23';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(1))
			 ->method('auto')
			 ->with($this->equalTo('foo.bar'));
		
		$callback = function($data) use ($mock) { return $mock->auto($data); };
		
		$this->object->extract_var($test, $callback);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_match_local_var()
	{
		$test   = '1 + 2 + #foo + 23';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(1))
			 ->method('auto')
			 ->with($this->equalTo('#foo'));
		
		$callback = function($data) use ($mock) { return $mock->auto($data); };
		
		$this->object->extract_var($test, $callback);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_match_constant()
	{
		$test   = '1 + 2 + @foo + 23';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(1))
			 ->method('auto')
			 ->with($this->equalTo('@foo'));
		
		$callback = function($data) use ($mock) { return $mock->auto($data); };
		
		$this->object->extract_var($test, $callback);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_match_array()
	{
		$test   = '1 + 2 + foo[0] + 23';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(1))
			 ->method('auto')
			 ->with($this->equalTo('foo§§0§§'));
		
		$callback = function($data) use ($mock) { return $mock->auto($data); };
		
		$this->object->extract_var($test, $callback);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_match_filter()
	{
		$test   = '1 + 2 + foo|e|a(12) + 23';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(1))
			 ->method('auto')
			 ->with($this->equalTo('foo|e|a::0::'));
		
		$callback = function($data) use ($mock) { return $mock->auto($data); };
		
		$this->object->extract_var($test, $callback);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::extract_var
	 */
	public function test_extract_var_real_example()
	{
		$test   = '1 + foo[bar] + big_Filter|default(#array[(foo + @bar)|upper|sub(bar|len)]|exists) + 23';
		$result = '1 + $this->foo[$this->bar] + $this->_filter_default($this->big_Filter, isset($array[substr(mb_strtoupper(($this->foo + bar), \'UTF-8\'), strlen($this->bar))]))';
		
		$object = $this->object;
		
		$callback = function($data) use ($object) { return $object->make_var($data); };
		
		$this->object->extract_var($test, $callback);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_var
	 */
	public function test_make_var()
	{
		$this->assertSame('$this->var', $this->object->make_var('var'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_var
	 */
	public function test_make_var_with_sub_object()
	{
		$this->assertSame('$this->var->foo', $this->object->make_var('var.foo'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_var
	 */
	public function test_make_var_local()
	{
		$this->assertSame('$var', $this->object->make_var('#var'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_var
	 */
	public function test_make_var_constant()
	{
		$this->assertSame('const', $this->object->make_var('@const'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_var
	 */
	public function test_make_var_keyword()
	{
		$this->assertSame('false', $this->object->make_var('false'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_var
	 */
	public function test_make_var_paren()
	{
		$this->assertSame('::1::', $this->object->make_var('::1::'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_var
	 */
	public function test_make_var_method_call()
	{
		$this->assertSame('$this->config::1::->title', $this->object->make_var('config::1::.title'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::make_var
	 */
	public function test_make_var_filter()
	{
		$this->assertSame('urlencode(nl2br($this->var))', $this->object->make_var('var|br|url'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::parse_filter
	 */
	public function test_parse_filter()
	{
		$this->assertSame('implode(\' \', %s)', $this->object->parse_filter('join'));		
	}

	/**
	 * @covers pixelpost\TemplateCompiler::parse_filter
	 */
	public function test_parse_filter_with_param()
	{
		$this->object->paren['::1::'] = '(foo)';
		
		$this->assertSame('implode(::1::, %s)', $this->object->parse_filter('join::1::'));
		$this->assertSame('foo', $this->object->paren['::1::']);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::parse_filter
	 */
	public function test_parse_filter_bad_filter()
	{
		$this->assertSame('%s', $this->object->parse_filter('no existant filter'));
	}

	/**
	 * @covers pixelpost\TemplateCompiler::replace_inline
	 */
	public function test_replace_inline()
	{
		$test   = 'foo {{ foo }} foo {[ foo ]} foo {: foo :} foo';
		$result = 'foo bar foo bar foo bar foo';
		
		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(3))
			 ->method('auto')
			 ->with($this->equalTo('foo'))
			 ->will($this->returnValue('bar'));
				
		$callback = function($data) use ($mock)
		{
			return $mock->auto($data);
		};
		
		$this->object->replace_inline($test, $callback);
		
		$this->assertSame($result, $test);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::replace_tag
	 */
	public function test_replace_tag_test_callback()
	{
		$test = 'This is a test class.';
		$result = 'This is new.';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->once())
			 ->method('auto')
			 ->with($this->equalTo(' test '), $this->equalTo('a'), $this->equalTo('class'))
			 ->will($this->returnValue('new'));
		
		$callback = function($data, $open, $close) use ($mock)
		{
			return $mock->auto($data, $open, $close);
		};
		
		$this->object->replace_tag($test, 'a', 'class', $callback);
		
		$this->assertSame($result, $test);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::replace_tag
	 */
	public function test_replace_tag_no_included_tag()
	{
		$test = 'This is a test class or a cool class ?';
		$result = 'This is new or genius ?';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(2))
			 ->method('auto')
			 ->will($this->onConsecutiveCalls('new', 'genius'));
		
		$callback = function($data, $open, $close) use ($mock)
		{
			return $mock->auto($data, $open, $close);
		};
		
		$this->object->replace_tag($test, 'a', 'class', $callback);
		
		$this->assertSame($result, $test);
	}

	/**
	 * @covers pixelpost\TemplateCompiler::replace_tag
	 */
	public function test_replace_tag_included_tag()
	{
		$test = 'This is a test, or a cool class, class or a new class ?';
		$result = 'This is bad or genius ?';

		$mock = $this->getMock('pixelpost\Request'); // whatever the class
		$mock->expects($this->exactly(3))
			 ->method('auto')
			 ->will($this->onConsecutiveCalls('new', 'bad', 'genius'));
		
		$callback = function($data, $open, $close) use ($mock)
		{
			return $mock->auto($data, $open, $close);
		};
		
		$this->object->replace_tag($test, 'a', 'class', $callback, true);
		
		$this->assertSame($result, $test);
	}

	/**
     * Fix bug with followed inner paren where
	 * this statement for example "( (1) (2) )" is matched like :
	 *
     * match 0: "(1)"
     * match 1: "( $0 (2)"
     * match 2: "$1 )"
	 *
	 * The correct match is :
     *
     * match 0: "(1)"
     * match 1: "(2)"
     * match 2: "( $0 $1 )"
	 *
	 * @covers Hype\Lib\Template\Compiler::replace_tag
	 */
	public function test_replace_tag_included_tag_followed()
	{
		$test   = 'a(a(1)a(2)a)a';
		$result = 'a([a([1])a([2])a])a';

		$callback = function($data, $open, $close)
		{
			return sprintf('([%s])', $data);
		};

		$this->object->replace_tag($test, '(', ')', $callback, true);

		$this->assertSame($result, $test);
	}
}
