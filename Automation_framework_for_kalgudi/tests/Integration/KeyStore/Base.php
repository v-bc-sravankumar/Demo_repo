<?php

abstract class Unit_KeyStore_Base extends Interspire_IntegrationTest
{
	abstract public function instance ();

	public function setUp ()
	{
		parent::setUp();
		$this->instance()->flush();
		$this->assertEquals(0, $this->instance()->count());
	}

	/**
	* Basic smoke test for keystore. Tests the basic functionality of setting, getting, deleting, flushing and exist-checking.
	*/
	public function testSmoke ()
	{
		$keystore = $this->instance();

		$time = time();

		$keystore->set('foo', $time);
		$time = (string)$time;

		$this->assertTrue($keystore->exists('foo'));

		$this->assertEquals($time, $keystore->get('foo'));

		$keystore->delete('foo');

		$this->assertFalse($keystore->exists('foo'));

		$keystore->set('foo', $time);
		$this->assertTrue($keystore->exists('foo'));
		$keystore->flush();
		$this->assertFalse($keystore->exists('foo'));
	}

	public function keysDataProvider ()
	{
		$data = array();

		$insert = array('a', 'ab', 'abc', 'abcd', 'bcde', 'cdef', 'defg', 'efgh');

		$data[] = array($insert, '*', $insert);
		$data[] = array($insert, 'a', array('a'));
		$data[] = array($insert, 'a*', array('a', 'ab', 'abc', 'abcd'));
		$data[] = array($insert, 'a?', array('ab'));
		$data[] = array($insert, 'a??', array('abc'));
		$data[] = array($insert, '?b', array('ab'));
		$data[] = array($insert, '*d', array('abcd'));
		$data[] = array($insert, '*d*', array('abcd', 'bcde', 'cdef', 'defg'));
		$data[] = array($insert, '*[ab]*', array('a', 'ab', 'abc', 'abcd', 'bcde'));

		return $data;
	}

	/**
	* @dataProvider keysDataProvider
	*/
	public function testKeys ($data, $pattern, $expected)
	{
		$keystore = $this->instance();

		foreach ($data as $key)
		{
			$keystore->set($key, 1);
		}

		$result = $keystore->keys($pattern);
		sort($result);

		$this->assertEquals($expected, $result);
	}

	public function testMultiSet ()
	{
		$keystore = $this->instance();

		$keystore->multiSet(array('a' => '1', 'b' => '2'));
		$keystore->multiSet(array('b' => '3', 'c' => '4'));

		$this->assertEquals(3, $keystore->count());
		$this->assertEquals('1', $keystore->get('a'));
		$this->assertEquals('3', $keystore->get('b'));
		$this->assertEquals('4', $keystore->get('c'));
	}

	public function testMultiGet ()
	{
		$keystore = $this->instance();

		$insert = array(
			'abc' => '1',
			'bcd' => '1',
			'cde' => '1',
			'def' => '1',
		);

		$keystore->multiSet($insert);

		$this->assertEquals($insert, $keystore->multiGet(array('abc', 'bcd', 'cde', 'def')));

		$this->assertEquals(array(
			'abc' => '1',
			'bcd' => '1',
		), $keystore->multiGet('*b*'));
	}

	public function testMultiDelete ()
	{
		$keystore = $this->instance();

		$insert = array(
			'a' => '1',
			'b' => '1',
			'c' => '1',
			'd' => '1',
		);

		$keystore->multiSet($insert);
		$keystore->multiDelete(array('a', 'b', 'c'));

		$this->assertEquals(1, $keystore->count());
		$this->assertFalse($keystore->exists('a'));
		$this->assertFalse($keystore->exists('b'));
		$this->assertFalse($keystore->exists('c'));
		$this->assertTrue($keystore->exists('d'));

		$keystore->flush();

		$insert = array(
			'abc' => '1',
			'bcd' => '1',
			'cde' => '1',
			'def' => '1',
		);

		$keystore->multiSet($insert);
		$keystore->multiDelete('*b*');

		$this->assertEquals(2, $keystore->count());
		$this->assertFalse($keystore->exists('abc'));
		$this->assertFalse($keystore->exists('bcd'));
		$this->assertTrue($keystore->exists('cde'));
		$this->assertTrue($keystore->exists('def'));
	}

	public function testIncrement ()
	{
		$keystore = $this->instance();

		$this->assertEquals(1, $keystore->increment('test'));
		$this->assertEquals(2, $keystore->increment('test'));
		$this->assertEquals(4, $keystore->increment('test', 2));
		$this->assertEquals(4, $keystore->get('test'));
		$this->assertEquals(1, $keystore->increment('test', -3));
	}

	public function testDecrement ()
	{
		$keystore = $this->instance();

		$this->assertEquals(-1, $keystore->decrement('test'));
		$this->assertEquals(-2, $keystore->decrement('test'));
		$this->assertEquals(-4, $keystore->decrement('test', 2));
		$this->assertEquals(-4, $keystore->get('test'));
		$this->assertEquals(-1, $keystore->decrement('test', -3));
	}
}
