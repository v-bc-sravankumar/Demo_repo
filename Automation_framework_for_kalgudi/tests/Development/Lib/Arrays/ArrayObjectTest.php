<?php

abstract class ArrayObjectTest extends Interspire_UnitTest
{

	public abstract function createArray();

	protected $array;

	public function setUp()
	{
		parent::setUp();
		$this->array = $this->createArray();
	}

	public function tearDown()
	{
		unset($this->array);
		parent::tearDown();
	}

	public function testSetGet()
	{
		$expected = __FUNCTION__;
		$key = "_one_";
		$this->array[$key] = $expected;
		$this->assertEquals($expected, $this->array[$key]);
	}

	public function testSetGetNested()
	{
		$expected = __FUNCTION__;
		$this->array['next'] = array();
		$this->array['next']['_one_'] = $expected;

		$this->assertEquals($expected, $this->array['next']['_one_']);

	}

	public function testSetGetNestedDeep()
	{
		$this->array["animal"] = array();
		$this->array["animal"]["birds"] = array();
		$this->array["animal"]["birds"]["chicken"] = "chicken";
		$this->assertEquals($this->array["animal"]["birds"]["chicken"], "chicken");

		$this->array["animal"]["reptiles"] = array();
		$this->array["animal"]["reptiles"]["crocodile"] = "crocodile";
		$this->assertEquals($this->array["animal"]["reptiles"]["crocodile"], "crocodile");
	}

	public function testAppendGet()
	{
		$expected = "one";
		$this->array[] = $expected;
		$this->assertEquals($this->array[0], $expected);
	}

	public function testAppendSetGet()
	{
		$this->array[] = "zero";
		$this->array["next"] = __FUNCTION__;

		$this->assertEquals($this->array[0], "zero");
		$this->assertEquals($this->array["next"], __FUNCTION__);

	}

	public function testAppendSetAppendGet()
	{
		$this->array[] = "zero";
		$this->array["next"] = __FUNCTION__;
		$this->array[] = "one";

		$this->assertEquals($this->array[0], "zero");
		$this->assertEquals($this->array["next"], __FUNCTION__);
		$this->assertEquals($this->array[1], "one");

	}

	public function testIsset()
	{
		$this->array[] = "zero";
		$this->assertTrue(isset($this->array[0]));
		$this->assertFalse(isset($this->array[1]));

		$this->array["next"] = __FUNCTION__;
		$this->assertTrue(isset($this->array["next"]));
		$this->assertFalse(isset($this->array["prev"]));
	}

	public function testIssetNested()
	{
		$this->array["animal"] = array();
		$this->array["animal"]["birds"] = "chicken";

		$this->assertTrue(isset($this->array["animal"]));
		$this->assertTrue(isset($this->array["animal"]["birds"]));

		$this->assertFalse(isset($this->array["reptiles"]));
	}

	public function testIssetNestedDeep()
	{
		$this->array["animal"] = array();
		$this->array["animal"]["birds"] = array();
		$this->array["animal"]["birds"]["chicken"] = "chicken";
		$this->array["animal"]["birds"]["can_fly"] = array();

		for ($i = 0; $i < 10; $i++) {
			$this->array["animal"]["birds"]["can_fly"][] = "bird $i";
		}

		$this->assertTrue(isset($this->array["animal"]["birds"]["chicken"]));
		for ($i = 0; $i < 10; $i++) {
			$this->assertTrue(isset($this->array["animal"]["birds"]["can_fly"][$i]));
			$this->assertFalse(isset($this->array["animal"]["birds"]["can_fly"][$i+10]));
		}
	}

	public function testUnset()
	{
		$this->array["animal"] = "cats";
		$this->assertTrue(isset($this->array["animal"]));
		unset($this->array["animal"]);
		$this->assertFalse(isset($this->array["animal"]));
	}

	public function testUnsetNested()
	{
		$this->array["animal"] = array();
		$this->array["animal"]["birds"] = array();
		$this->array["animal"]["birds"]["chicken"] = "chicken";

		$this->assertTrue(isset($this->array["animal"]));
		$this->assertTrue(isset($this->array["animal"]["birds"]));
		$this->assertTrue(isset($this->array["animal"]["birds"]["chicken"]));

		unset($this->array["animal"]["birds"]["chicken"]);
		$this->assertFalse(isset($this->array["animal"]["birds"]["chicken"]));

		unset($this->array["animal"]);
		$this->assertFalse(@isset($this->array["animal"]["birds"])); // this will generate a notice
		$this->assertFalse(isset($this->array["animal"]));

	}

	public function testTraversal()
	{
		$expected = explode(' ', 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit...');
		foreach ($expected as $part) {
			$this->array[] = $part;
		}
		foreach ($this->array as $key => $value) {
			$this->assertEquals($value, $expected[$key]);
		}
	}

	public function testTraversalNested()
	{
		$str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sem dui, pulvinar eget tincidunt eget, ornare vitae eros. Nulla ante massa, venenatis nec iaculis vel, auctor vel odio. Fusce semper nulla eu metus volutpat iaculis. Phasellus et purus enim. Nunc sit amet justo eu nibh volutpat convallis non ut diam. Integer et lacus vitae nisi egestas imperdiet. Curabitur venenatis magna a orci pulvinar dapibus cursus enim iaculis. Pellentesque hendrerit, nunc a aliquet molestie, est augue vehicula mi, ac consequat quam magna non nulla. Praesent lectus erat, varius vel luctus ac, condimentum eu ligula. Nulla sodales pretium tellus, eu mattis enim ultricies et. Vestibulum dictum sem vel sem consequat consequat.';
		$parts = explode('.', $str);
		$expected = array();
		foreach ($parts as $part) {
			$chuncks = explode(' ', $part);
			$expected[] = $chuncks;
			$this->array[] = $chuncks;
		}
		foreach ($this->array as $key => $vals) {
			foreach ($vals as $nkey => $value) {
				$this->assertEquals($value, $expected[$key][$nkey]);
			}
		}
	}

	public function testCount()
	{
		$expected = explode(' ', 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit...');
		foreach ($expected as $part) {
			$this->array[] = $part;
		}
		$this->assertEquals(count($expected), count($this->array));
	}

	public function testCountNested()
	{
		$str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sem dui, pulvinar eget tincidunt eget, ornare vitae eros. Nulla ante massa, venenatis nec iaculis vel, auctor vel odio. Fusce semper nulla eu metus volutpat iaculis. Phasellus et purus enim. Nunc sit amet justo eu nibh volutpat convallis non ut diam. Integer et lacus vitae nisi egestas imperdiet. Curabitur venenatis magna a orci pulvinar dapibus cursus enim iaculis. Pellentesque hendrerit, nunc a aliquet molestie, est augue vehicula mi, ac consequat quam magna non nulla. Praesent lectus erat, varius vel luctus ac, condimentum eu ligula. Nulla sodales pretium tellus, eu mattis enim ultricies et. Vestibulum dictum sem vel sem consequat consequat.';
		$parts = explode('.', $str);
		$expected = array();
		foreach ($parts as $part) {
			$chuncks = explode(' ', $part);
			$expected[] = $chuncks;
			$this->array[] = $chuncks;
		}
		foreach ($this->array as $key => $vals) {
			$this->assertEquals(count($vals), count($expected[$key]));
		}
		$this->assertEquals(count($this->array), count($expected));
	}

	public function testKeysNested()
	{
		$str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sem dui, pulvinar eget tincidunt eget, ornare vitae eros. Nulla ante massa, venenatis nec iaculis vel, auctor vel odio. Fusce semper nulla eu metus volutpat iaculis. Phasellus et purus enim. Nunc sit amet justo eu nibh volutpat convallis non ut diam. Integer et lacus vitae nisi egestas imperdiet. Curabitur venenatis magna a orci pulvinar dapibus cursus enim iaculis. Pellentesque hendrerit, nunc a aliquet molestie, est augue vehicula mi, ac consequat quam magna non nulla. Praesent lectus erat, varius vel luctus ac, condimentum eu ligula. Nulla sodales pretium tellus, eu mattis enim ultricies et. Vestibulum dictum sem vel sem consequat consequat.';
		$parts = explode('.', $str);
		$expected = array();
		foreach ($parts as $part) {
			$chuncks = explode(' ', $part);
			$expected[] = $chuncks;
			$this->array[] = $chuncks;
		}
		$expectedKeys = array_keys($parts);
		$keys = array();
		foreach ($this->array as $key => $vals) {
			$keys[] = $key;
			$expectedNestedKeys = array_keys($expected[$key]);
			$nestedKeys = array();
			foreach ($vals as $k => $v) {
				$nestedKeys[] = $k;
			}
			sort($expectedNestedKeys);
			sort($nestedKeys);
			$this->assertEquals($expectedNestedKeys, $nestedKeys);
		}
		sort($expectedKeys);
		sort($keys);
		$this->assertEquals($expectedKeys, $keys);
	}

}