<?php

abstract class Unit_Lib_Store_Api_Input extends Interspire_IntegrationTest
{
	abstract public function dataProvider();

	/**
	* @dataProvider dataProvider
	*/
	public function testCountInput(Store_Api_Input $input)
	{
		$this->assertEquals(10, count($input));
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testIsset(Store_Api_Input $input)
	{
		$this->assertTrue(isset($input->id));
		$this->assertFalse(isset($input->foo));
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testStringElement(Store_Api_Input $input)
	{
		$this->assertSame('MacBook', $input->name);
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testIntegerElement(Store_Api_Input $input)
	{
		$this->assertSame(1, $input->id);
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testFloatElement(Store_Api_Input $input)
	{
		$this->assertSame(1999.99, $input->price);
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testBooleanElement(Store_Api_Input $input)
	{
		$this->assertTrue($input->featured);
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testIteratorCurrent(Store_Api_Input $input)
	{
		$this->assertSame(1, $input->current());
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testIteratorKey(Store_Api_Input $input)
	{
		$this->assertSame('id', $input->key());
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testIteratorNext(Store_Api_Input $input)
	{
		$input->next();
		$this->assertSame('MacBook', $input->current());
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testIteratorRewind(Store_Api_Input $input)
	{
		$input->next();
		$input->next();
		$input->rewind();
		$this->assertSame(1, $input->current());
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testArrayElement(Store_Api_Input $input)
	{
		$this->assertInstanceOf('Store_Api_Input', $input->categories);
		$this->assertEquals(array(2,6,10,11), (array)$input->categories->getRawInput());
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testSingleElementArrayElement(Store_Api_Input $input)
	{
		$this->assertInstanceOf('Store_Api_Input', $input->related);
		$this->assertEquals(array(4), (array)$input->related->getRawInput());
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testObjectElement(Store_Api_Input $input)
	{
		$this->assertInstanceOf('Store_Api_Input', $input->details);
		$this->assertEquals(54.21, $input->details->tax);
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testComplexElement(Store_Api_Input $input)
	{
		$this->assertInstanceOf('Store_Api_Input', $input->images);

		$images = $input->images;

		$image = $images->current();
		$this->assertEquals(6, $image->id);
		$this->assertEquals('foo.jpg', $image->file);

		$images->next();
		$image = $images->current();
		$this->assertEquals(8, $image->id);
		$this->assertEquals('bar.png', $image->file);
	}

	/**
	* @dataProvider dataProvider
	*/
	public function testSingleElementComplexElement(Store_Api_Input $input)
	{
		$this->assertInstanceOf('Store_Api_Input', $input->videos);
		$videos = $input->videos;
		$video = $videos->current();

		$this->assertEquals(10, $video->id);
		$this->assertEquals('http://www.youtube.com/watch?foo', $video->url);
	}
}
