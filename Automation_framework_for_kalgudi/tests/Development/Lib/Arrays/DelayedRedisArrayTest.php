<?php

use RedisArray\RedisArray;

require_once('ArrayObjectTest.php');

use RedisArray\DelayedRedisArray;

class DelayedRedisArrayTest extends ArrayObjectTest
{

	public function createArray()
	{
		$credis = new \Predis\Client();
		$credis->select(10);
		return new DelayedRedisArray(new RedisArray("__DelayedRedisArrayTests::array__", $credis));
	}

	public function testSetGet()
	{
		$expected = __FUNCTION__;
		$key = "_one_";
		$this->array[$key] = $expected;
		$this->array->save();

		$this->assertEquals($expected, $this->array[$key]);
	}

	public function testSetGetNested()
	{
		$expected = __FUNCTION__;
		$this->array['next'] = array();
		$this->array['next']['_one_'] = $expected;
		$this->array->save();

		$this->assertEquals($expected, $this->array['next']['_one_']);

	}

	public function testSetGetNestedDeep()
	{
		$this->array["animal"] = array();
		$this->array["animal"]["birds"] = array();
		$this->array["animal"]["birds"]["chicken"] = "chicken";
		$this->array->save();
		$this->assertEquals($this->array["animal"]["birds"]["chicken"], "chicken");

		$this->array["animal"]["reptiles"] = array();
		$this->array["animal"]["reptiles"]["crocodile"] = "crocodile";
		$this->array->save();
		$this->assertEquals($this->array["animal"]["reptiles"]["crocodile"], "crocodile");
	}

	public function testAppendGet()
	{
		$expected = "one";
		$this->array[] = $expected;
		$this->array->save();
		$this->assertEquals($this->array[0], $expected);
	}

	public function testAppendSetGet()
	{
		$this->array[] = "zero";
		$this->array["next"] = __FUNCTION__;
		$this->array->save();
		$this->assertEquals($this->array[0], "zero");
		$this->assertEquals($this->array["next"], __FUNCTION__);

	}

	public function testAppendSetAppendGet()
	{
		$this->array[] = "zero";
		$this->array["next"] = __FUNCTION__;
		$this->array[] = "one";
		$this->array->save();
		$this->assertEquals($this->array[0], "zero");
		$this->assertEquals($this->array["next"], __FUNCTION__);
		$this->assertEquals($this->array[1], "one");

	}

	public function testIsset()
	{
		$this->array[] = "zero";
		$this->array->save();
		$this->assertTrue(isset($this->array[0]));
		$this->assertFalse(isset($this->array[1]));

		$this->array["next"] = __FUNCTION__;
		$this->array->save();
		$this->assertTrue(isset($this->array["next"]));
		$this->assertFalse(isset($this->array["prev"]));
	}

	public function testIssetNested()
	{
		$this->array["animal"] = array();
		$this->array["animal"]["birds"] = "chicken";
		$this->array->save();
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
		$this->array->save();
		$this->assertTrue(isset($this->array["animal"]["birds"]["chicken"]));
		for ($i = 0; $i < 10; $i++) {
			$this->assertTrue(isset($this->array["animal"]["birds"]["can_fly"][$i]));
			$this->assertFalse(isset($this->array["animal"]["birds"]["can_fly"][$i+10]));
		}
	}

	public function testUnset()
	{
		$this->array["animal"] = "cats";
		$this->array->save();
		$this->assertTrue(isset($this->array["animal"]));
		unset($this->array["animal"]);
		$this->array->save();
		$this->assertFalse(isset($this->array["animal"]));
	}

	public function testUnsetNested()
	{
		$this->array["animal"] = array();
		$this->array["animal"]["birds"] = array();
		$this->array["animal"]["birds"]["chicken"] = "chicken";
		$this->array->save();
		$this->assertTrue(isset($this->array["animal"]));
		$this->assertTrue(isset($this->array["animal"]["birds"]));
		$this->assertTrue(isset($this->array["animal"]["birds"]["chicken"]));

		unset($this->array["animal"]["birds"]["chicken"]);
		$this->array->save();
		$this->assertFalse(isset($this->array["animal"]["birds"]["chicken"]));

		unset($this->array["animal"]);
		$this->array->save();
		$this->assertFalse(@isset($this->array["animal"]["birds"])); // this will generate a notice
		$this->assertFalse(isset($this->array["animal"]));

	}

	public function testTraversal()
	{
		$expected = explode(' ', 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit...');
		foreach ($expected as $part) {
			$this->array[] = $part;
		}
		$this->array->save();
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
		$this->array->save();
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
		$this->array->save();
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
		$this->array->save();
		foreach ($this->array as $key => $vals) {
			$this->assertEquals(count($vals), count($expected[$key]));
		}
		$this->assertEquals(count($this->array), count($expected));
	}

	private function fillBatchInsertWriteOpsNested(&$data, $nest = true)
	{
		for ($i = 0; $i < (DelayedRedisArray::WRITE_OPS_BEFORE_SAVE * 5); $i++) {
			// you are here
			if ($nest && $i % mt_rand(50, 100) === 0) {
				$subdata = array();
				$this->fillBatchInsertWriteOpsNested($subdata, false);
				$data[] = $subdata;
			} else {
				$data[] = uniqid('test', true).mt_rand(0, 999999);
			}

		}
	}

	public function testBatchInsertWriteOps()
	{
		$data = array();
		$this->fillBatchInsertWriteOpsNested($data, false);

		foreach ($data as $key => $val) {
			$this->array[] = $val;
		}
		$this->array->save();

		$this->assertEquals(count($data), count($this->array));
		foreach ($this->array as $key => $val) {
			$this->assertEquals($data[$key], $val);
		}

	}

	public function testBatchInsertWriteOpsNested()
	{
		$data = array();
		$this->fillBatchInsertWriteOpsNested($data);

		foreach ($data as $key => $val) {
			if (is_array($val)) {
				$this->array[$key] = array();
				foreach ($val as $v) {
					$this->array[$key][] = $v;
				}
			} else {
				$this->array[] = $val;
			}
		}
		$this->array->save();

		$this->assertEquals(count($data), count($this->array));
		foreach ($this->array as $key => $val) {
			if (is_array($data[$key])) {
				$this->assertEquals(count($data[$key]), count($val));
				foreach ($val as $k => $v) {
					$this->assertEquals($data[$key][$k], $v);
				}
			} else {
				$this->assertEquals($data[$key], $val);
			}
		}

	}

	public function testBatchKeysNested()
	{
		$str = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus sem dui, pulvinar eget tincidunt eget, ornare vitae eros. Nulla ante massa, venenatis nec iaculis vel, auctor vel odio. Fusce semper nulla eu metus volutpat iaculis. Phasellus et purus enim. Nunc sit amet justo eu nibh volutpat convallis non ut diam. Integer et lacus vitae nisi egestas imperdiet. Curabitur venenatis magna a orci pulvinar dapibus cursus enim iaculis. Pellentesque hendrerit, nunc a aliquet molestie, est augue vehicula mi, ac consequat quam magna non nulla. Praesent lectus erat, varius vel luctus ac, condimentum eu ligula. Nulla sodales pretium tellus, eu mattis enim ultricies et. Vestibulum dictum sem vel sem consequat consequat.';
		$parts = explode('.', $str);
		$expected = array();
		foreach ($parts as $part) {
			$chuncks = explode(' ', $part);
			$expected[] = $chuncks;
			$this->array[] = $chuncks;
		}
		$this->array->save();
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

	public function tearDown()
	{
		$this->array->delete();
		parent::tearDown();
	}

}