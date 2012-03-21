<?php

namespace pixelpost\core;

/**
 * Test class for Filter.
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @todo Implement test_is_string().
	 */
	public function test_is_string()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_int().
	 */
	public function test_is_int()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_float().
	 */
	public function test_is_float()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_bool().
	 */
	public function test_is_bool()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_null().
	 */
	public function test_is_null()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_array().
	 */
	public function test_is_array()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_resource().
	 */
	public function test_is_resource()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_object().
	 */
	public function test_is_object()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_date().
	 */
	public function test_is_date()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_numeric().
	 */
	public function test_is_numeric()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement test_is_scalar().
	 */
	public function test_is_scalar()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pixelpost\core\Filter::assume_string
	 */
	public function test_assume_string()
	{
		$foo = 'hello';
		Filter::assume_string($foo);
		$this->assertSame('hello', $foo);

		$foo = 3;
		Filter::assume_string($foo);
		$this->assertSame('3', $foo);

		$foo = 3.3;
		Filter::assume_string($foo);
		$this->assertSame('3.3', $foo);
	}

	/**
	 * @covers pixelpost\core\Filter::assume_int
	 */
	public function test_assume_int()
	{
		$foo = 'hello';
		Filter::assume_int($foo);
		$this->assertSame(0, $foo);

		$foo = 3;
		Filter::assume_int($foo);
		$this->assertSame(3, $foo);

		$foo = 3.3;
		Filter::assume_int($foo);
		$this->assertSame(3, $foo);
	}

	/**
	 * @covers pixelpost\core\Filter::assume_float
	 */
	public function test_assume_float()
	{
		$foo = 'hello';
		Filter::assume_float($foo);
		$this->assertSame(0.0, $foo);

		$foo = 3;
		Filter::assume_float($foo);
		$this->assertSame(3.0, $foo);

		$foo = 3.3;
		Filter::assume_float($foo);
		$this->assertSame(3.3, $foo);
	}

	/**
	 * @covers pixelpost\core\Filter::assume_bool
	 */
	public function test_assume_bool()
	{
		$foo = 'hello';
		Filter::assume_bool($foo);
		$this->assertSame(true, $foo);

		$foo = '';
		Filter::assume_bool($foo);
		$this->assertSame(false, $foo);

		$foo = 3;
		Filter::assume_bool($foo);
		$this->assertSame(true, $foo);

		$foo = 0;
		Filter::assume_bool($foo);
		$this->assertSame(false, $foo);

		$foo = 3.3;
		Filter::assume_bool($foo);
		$this->assertSame(true, $foo);

		$foo = null;
		Filter::assume_bool($foo);
		$this->assertSame(false, $foo);
	}

	/**
	 * @covers pixelpost\core\Filter::assume_array
	 */
	public function test_assume_array()
	{
		$foo = 'hello';
		Filter::assume_array($foo);
		$this->assertSame(array('hello'), $foo);

		$foo = 3;
		Filter::assume_array($foo);
		$this->assertSame(array(3), $foo);

		$foo = 3.3;
		Filter::assume_array($foo);
		$this->assertSame(array(3.3), $foo);

		$foo = array('foo', 'bar');
		Filter::assume_array($foo);
		$this->assertSame(array('foo', 'bar'), $foo);
	}

	/**
	 * @covers pixelpost\core\Filter::validate_email
	 */
	public function test_validate_email()
	{
		$this->assertTrue(Filter::validate_email('aaa@aaaa.com'));
		$this->assertFalse(Filter::validate_email('aaa-aaa_ccc@bb_aa.aa-aa.co.uk'));
		$this->assertFalse(Filter::validate_email('aaa-aaa_ccc@bb_aa'));
		$this->assertFalse(Filter::validate_email('aaa-aaa_ccc'));
	}

	/**
	 * @covers pixelpost\core\Filter::validate_date
	 */
	public function test_validate_date()
	{
		$this->assertTrue(Filter::validate_date('2011-10-23'));
		$this->assertTrue(Filter::validate_date('2011-10-23', 'Y-m-d'));
		$this->assertFalse(Filter::validate_date('2011-10-23', 'd-m-Y'));
	}

	/**
	 * @covers pixelpost\core\Filter::format_without_accent
	 */
	public function test_format_without_accent()
	{
		$accent	  = 'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËéèêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ';
		$noaccent = 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn';

		$this->assertSame($noaccent, Filter::format_without_accent($accent));
	}

	/**
	 * @covers pixelpost\core\Filter::format_for_url
	 */
	public function test_format_for_url()
	{
		$orig = 'Test My\'test';
		$url  = 'test-my-test';

		$this->assertSame($url, Filter::format_for_url($orig));
	}

	/**
	 * @covers pixelpost\core\Filter::format_for_xml
	 */
	public function test_format_for_xml()
	{
		$orig = 'Test <My\'test> & luna ^^';
		$xml  = 'Test My\'test  luna ^^';

		$this->assertSame($xml, Filter::format_for_xml($orig));
	}

	/**
	 * @todo Implement test_check_encoding().
	 */
	public function test_check_encoding()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @covers pixelpost\core\Filter::urlencode
	 */
	public function test_urlencode()
	{
		$test   = 'http://www.something.com/';
		$result = 'http%3A%252F%252Fwww.something.com%252F';

		$this->assertSame($result, Filter::urlencode($test));
	}

	/**
	 * @covers pixelpost\core\Filter::array_to_object
	 */
	public function test_array_to_object()
	{
		$array = array(
			'foo' => 'bar',
			'baz' => array(
				'apple'  => 'good',
				'orange' => 'bad',
			),
		);

		$object = Filter::array_to_object($array);

		$this->assertTrue(is_object($object));
		$this->assertTrue(property_exists($object, 'foo'));
		$this->assertTrue(property_exists($object, 'baz'));
		$this->assertSame('bar', $object->foo);
		$this->assertTrue(is_object($object->baz));
		$this->assertTrue(property_exists($object->baz, 'apple'));
		$this->assertTrue(property_exists($object->baz, 'orange'));
		$this->assertSame('good', $object->baz->apple);
		$this->assertSame('bad', $object->baz->orange);
	}

	/**
	 * @covers pixelpost\core\Filter::object_to_array
	 */
	public function test_object_to_array()
	{
		$foo = new \stdClass();
		$foo->bar = 'baz';
		$foo->foo = new \stdClass();
		$foo->foo->bar = 'baz';
		$foo->foo->foo = 56;

		$array = Filter::object_to_array($foo);

		$this->assertTrue(is_array($array));
		$this->assertTrue(array_key_exists('foo', $array));
		$this->assertTrue(array_key_exists('bar', $array));

		$this->assertSame('baz', $array['bar']);
		$this->assertTrue(is_array($array['foo']));

		$this->assertTrue(array_key_exists('foo', $array['foo']));
		$this->assertTrue(array_key_exists('bar', $array['foo']));

		$this->assertSame('baz', $array['foo']['bar']);
		$this->assertSame(56,    $array['foo']['foo']);
	}

	/**
	 * @covers pixelpost\core\Filter::object_to_array
	 */
	public function test_object_to_array_with_numeric_index()
	{
		$foo = (object) array('foo', 'bar', 'baz');

		$array = Filter::object_to_array($foo);

		$this->assertTrue(is_array($array));
		$this->assertTrue(array_key_exists(0, $array));
		$this->assertTrue(array_key_exists(1, $array));
		$this->assertTrue(array_key_exists(2, $array));

		$this->assertSame('foo', $array[0]);
		$this->assertSame('bar', $array[1]);
		$this->assertSame('baz', $array[2]);
	}

	/**
	 * @covers pixelpost\core\Filter::str_to_date
	 */
	public function test_str_to_date()
	{
		$date = '2010-01-23T02:34:56+02:00';

		Filter::str_to_date($date);

		$this->assertTrue($date instanceof \DateTime);
		$this->assertSame('2010/01/23 02:34', $date->format('Y/m/d H:i'));
	}

	/**
	 * @covers pixelpost\core\Filter::compare_version
	 */
	public function test_compare_version()
	{
		$this->assertTrue( Filter::compare_version('2.3.3', '2.3.4'));
		$this->assertNull( Filter::compare_version('2.3.4', '2.3.4'));
		$this->assertFalse(Filter::compare_version('2.3.5', '2.3.4'));

		$this->assertTrue( Filter::compare_version('2',           '2.3'));
		$this->assertTrue( Filter::compare_version('2.3',         '2.3.4'));
		$this->assertFalse(Filter::compare_version('2.3.4',       '2.3.4-beta'));
		$this->assertTrue( Filter::compare_version('2.3.4-alpha', '2.3.4-beta'));
		$this->assertTrue( Filter::compare_version('2.3.4-alpha1','2.3.4-alpha2'));
	}
}
