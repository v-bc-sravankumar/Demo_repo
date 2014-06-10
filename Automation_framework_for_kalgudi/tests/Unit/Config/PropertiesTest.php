<?php

class PropertiesTest extends PHPUnit_Framework_TestCase
{
	public function testGetNullValueWhenEmpty()
	{
		$properties = new Config\Properties(array());

		$this->assertNull($properties->get('hello'));
	}

	public function testGetDefaultValueWhenEmpty()
	{
		$properties = new Config\Properties(array());

		$this->assertFalse($properties->get('hello', false));
	}

	public function testGetInternalTypes()
	{
		$properties = new Config\Properties(array('hello'=>'world!', 'pi'=>M_PI, 'eleven'=>11));

		$this->assertEquals('world!', $properties->get('hello'));
		$this->assertEquals(M_PI, $properties->get('pi'));
		$this->assertEquals(11, $properties->get('eleven'));
	}

	public function testGetCompoundProperties()
	{
		$properties = new Config\Properties(array('one'=>array(1,2), 'two'=>array(1,2)));

		$this->assertEquals(array(1,2), $properties->get('one'));
		$this->assertEquals(array(1,2), $properties->get('two'));
	}

	public function testGetNestedCompoundProperties()
	{
		$properties = new Config\Properties(array('one'=>array('two'=>array('three'=>array('four','five')))));

		$this->assertEquals(new Config\Properties(array('three'=>array('four', 'five'))), $properties->get('one.two'));
		$this->assertEquals(array('four', 'five'), $properties->get('one.two.three'));
	}

	public function testNestedArrayAccess()
	{
		$properties = new Config\Properties(array('ein'=>array('zwei'=>array('drei'=>array('vier','fünf')))));

		$this->assertTrue(isset($properties['ein']['zwei']['drei']));
		$this->assertEquals('vier', $properties['ein']['zwei']['drei'][0]);
		$this->assertEquals('fünf', $properties['ein']['zwei']['drei'][1]);
	}

	/**
	 * @expectedException LogicException
	 */
	public function testCannotWriteToArrayAccessKeys()
	{
		$properties = new Config\Properties(array('ein'=>array('zwei'=>array('drei'=>array('vier','funf')))));

		$properties['ein']['zwei']['drei'] = 'vier';
	}

	/**
	 * @expectedException LogicException
	 */
	public function testCannotUnsetArrayAccessKeys()
	{
		$properties = new Config\Properties(array('ein'=>array('zwei'=>array('drei'=>array('vier','funf')))));

		unset($properties['ein']['zwei']['drei']);
	}

	public function testGetAll()
	{
		$props = array('ein'=>array('zwei'=>array('drei'=>array('vier','funf'))));
		$properties = new Config\Properties($props);

		$this->assertEquals($props, $properties->getAll());
	}

	public function testExtendDoesntOverwriteThisInstance()
	{
		$props = array(
			'foo' => 'bar',
			'multi' => array(
				'bar' => 'foo',
			),
		);

		$thisProperties = new Config\Properties($props);

		$extendFromProperties = new Config\Properties(array(
			'foo' => 'foo',
			'multi' => array(
				'bar' => 'bar',
			),
		));

		$newProps = $thisProperties->extend($extendFromProperties)->getAll();
		$this->assertEquals($props, $newProps);
	}

	public function testExtendRetainsUniqueProperties()
	{
		$thisProperties = new Config\Properties(array(
			'foo' => 'bar',
			'multi' => array(
				'first' => 'first',
			),
		));

		$extendFromProperties = new Config\Properties(array(
			'bar' => 'foo',
			'multi' => array(
				'second' => 'second',
			),
		));

		$expected = array(
			'foo' => 'bar',
			'bar' => 'foo',
			'multi' => array(
				'first' => 'first',
				'second' => 'second',
			),
		);

		$newProps = $thisProperties->extend($extendFromProperties)->getAll();
		$this->assertEquals($expected, $newProps);
	}

	public function testExtendDoesntMergeArrayOfScalars()
	{
		$thisProperties = new Config\Properties(array(
			'scalars' => array('foo', 'bar'),
			'assoc' => array(
				'this' => 'that',
			),
		));

		$extendFromProperties = new Config\Properties(array(
			'scalars' => array(1, 2, 3),
			'assoc' => array(
				'here' => 'there',
			),
		));

		$expected = array(
			'scalars' => array('foo', 'bar'),
			'assoc' => array(
				'this' => 'that',
				'here' => 'there',
			),
		);

		$newProps = $thisProperties->extend($extendFromProperties)->getAll();
		$this->assertEquals($expected, $newProps);
	}

	public function testExtendDoesntMergePartiallyAssociativeArrays()
	{
		$expected = array(
			'scalars' => array('foo' => 'bar', 1, 2),
		);

		$thisProperties = new Config\Properties($expected);

		$extendFromProperties = new Config\Properties(array(
			'scalars' => array('here' => 'there', 'this' => 'that'),
		));

		$newProps = $thisProperties->extend($extendFromProperties)->getAll();
		$this->assertEquals($expected, $newProps);
	}

	public function testExtendDoesntMergeArrayIfCorrespondingValueIsntArray()
	{
		$expected = array(
			'value' => array(1, 2, 3),
		);

		$thisProperties = new Config\Properties($expected);

		$extendFromProperties = new Config\Properties(array(
			'value' => 'foo',
		));

		$newProps = $thisProperties->extend($extendFromProperties)->getAll();
		$this->assertEquals($expected, $newProps);
	}

	public function testPropertiesAreIterable()
	{
		$props = array(
			'foo' => 'bar',
			'array' => array(1,2,3),
		);

		$properties = new Config\Properties($props);

		$this->assertInstanceOf('Traversable', $properties);

		$count = 0;

		foreach ($properties as $key => $value) {
			$this->assertArrayHasKey($key, $props);
			$this->assertEquals($props[$key], $value);

			$count++;
		}

		// ensure that we actually did traverse
		$this->assertEquals(count($props), $count);
	}

	public function testIteratingAssociativeReturnsProperties()
	{
		// iterating the 'Foo' key should return a Properties instance not the array
		$props = array(
			'Foo' => array('Hello' => 'World'),
		);

		$properties = new Config\Properties($props);

		foreach ($properties as $key => $value) {
			$this->assertEquals('Foo', $key);
			$this->assertInstanceOf('\Config\Properties', $value);
		}
	}
}