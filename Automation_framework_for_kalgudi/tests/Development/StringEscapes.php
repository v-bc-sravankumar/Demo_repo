<?php
/**
 * This test suite revolves around the Interspire_String::explodeEscape and Interspire_String::escapeString functions
 */
class Unit_Lib_Store_StringEscapes extends PhpUnit_Framework_TestCase
{

	private function escapeAndExplode($delimiter, $escaper, $parts)
	{
		$escapedParts = array();
		foreach ($parts as $part) {
			$escapedParts[] = Interspire_String::escapeString($delimiter, $escaper, $part);
		}
		return Interspire_String::explodeEscape($delimiter, $escaper, implode($delimiter, $escapedParts));
	}

	// TEST ESCAPES

	public function testEscapeEmpty()
	{
		$this->assertEquals('', Interspire_String::escapeString('', '', ''));
	}

	public function testEscapeNoEscapeEmpty()
	{
		$this->assertEquals('', Interspire_String::escapeString('/', '\\', ''));
	}

	public function testEscapeNoEscape()
	{
		$this->assertEquals('string', Interspire_String::escapeString('/', '\\', 'string'));
	}

	public function testEscapeExpected()
	{
		$this->assertEquals('string\/string', Interspire_String::escapeString('/', '\\', 'string/string'));
	}

	public function testEscapeExpectedWord()
	{
		$this->assertEquals('string\//string', Interspire_String::escapeString('//', '\\', 'string//string'));
	}

	public function testEscapeExpectedMulti1()
	{
		$this->assertEquals('string\/string\;hellow', Interspire_String::escapeString(array('/', ';'), '\\', 'string/string;hellow'));
	}

	public function testEscapeExpectedMulti2()
	{
		$this->assertEquals('string\/string\;hellow\/', Interspire_String::escapeString(array('/', ';'), '\\', 'string/string;hellow/'));
	}

	public function testEscapeExpectedMulti3()
	{
		$this->assertEquals('\/string\/string\;hellow\/', Interspire_String::escapeString(array('/', ';'), '\\', '/string/string;hellow/'));
	}

	public function testEscapeExpectedMulti4()
	{
		$this->assertEquals('\/string\\\/string\;hellow\/', Interspire_String::escapeString(array('/', ';'), '\\', '/string\/string;hellow/'));
	}

	public function testEscapeExpectedBoundary1()
	{
		$this->assertEquals('\/', Interspire_String::escapeString(array('/', ';'), '\\', '/'));
	}

	public function testEscapeExpectedBoundary2()
	{
		$this->assertEquals('\/\;', Interspire_String::escapeString(array('/', ';'), '\\', '/;'));
	}

	public function testEscapeExpectedBoundary3()
	{
		$this->assertEquals('\/\/\;', Interspire_String::escapeString(array('/', ';'), '\\', '//;'));
	}

	public function testEscapeExpectedHectic()
	{
		$this->assertEquals('\/\/\;', Interspire_String::escapeString(array('/', '//', ';'), '\\', '//;'));
	}

	// TEST EXPLODES

	public function testExplodeEmpty()
	{
		$this->assertEquals(array(''), Interspire_String::explodeEscape('/', '\\', ''));
	}

	public function testExplodeBoundary1()
	{
		$this->assertEquals(array('', '', '', ''), Interspire_String::explodeEscape('/', '\\', '///'));
	}

	public function testExplodeBoundary2()
	{
		$this->assertEquals(array('', ''), Interspire_String::explodeEscape('/', '\\', '/'));
	}

	public function testExplodeBoundary3()
	{
		$this->assertEquals(array('/'), Interspire_String::explodeEscape('/', '\\', '\/'));
	}

	public function testExplodeBoundary4()
	{
		$this->assertEquals(array('//', '/'), Interspire_String::explodeEscape('/', '\\', '\/\//\/'));
	}

	public function testExplodeBoundary5()
	{
		$this->assertEquals(array('', '', '/'), Interspire_String::explodeEscape('/', '\\', '//\/'));
	}

	public function testExplodeExpected1()
	{
		$this->assertEquals(array('Rocks/'), Interspire_String::explodeEscape('/', '\\', 'Rocks\/'));
	}

	public function testExplodeExpected2()
	{
		$this->assertEquals(array('Things', 'Rock/Pebbles', 'Large'), Interspire_String::explodeEscape('/', '\\', 'Things/Rock\/Pebbles/Large'));
	}

	public function testExplodeExpected3()
	{
		$this->assertEquals(array('', 'Rock/Pebbles'), Interspire_String::explodeEscape('/', '\\', '/Rock\/Pebbles'));
	}

	// TEST ESCAPE + EXPLODE

	public function testEscapeExplodeSimple1()
	{
		$delimiter = '/';
		$escaper = '\\';
		$parts = array('Things', 'Rocks/Pebbles', 'Large Rocks');
		$this->assertEquals($parts, $this->escapeAndExplode($delimiter, $escaper, $parts));
	}

	public function testEscapeExplodeSimple2()
	{
		$delimiter = '/';
		$escaper = '\\';
		$parts = array('/Things', 'Rocks/Pebbles', '\/\/Large Rocks');
		$this->assertEquals($parts, $this->escapeAndExplode($delimiter, $escaper, $parts));
	}

	public function testEscapeExplodeSimple3()
	{
		$delimiter = '/';
		$escaper = '\\';
		$parts = array('//', '/', '\//');
		$this->assertEquals($parts, $this->escapeAndExplode($delimiter, $escaper, $parts));
	}

	public function testEscapeExplodeSimple4()
	{
		$delimiter = '/';
		$escaper = '\\';
		$parts = array('Things', '/Rocks/Pebbles/', '/Large //Rocks///');
		$this->assertEquals($parts, $this->escapeAndExplode($delimiter, $escaper, $parts));
	}

	public function testEscapeExplodeMimicCategoryExportImport()
	{
		$categoryCache = array (
			1 => array (
				'categoryid' => '1',
				'catname' => 'Shop Mac',
				'catparentid' => '0',
			),
			2 => array (
				'categoryid' => '2',
				'catname' => 'Shop iPhone',
				'catparentid' => '0',
			),
			3 => array (
				'categoryid' => '3',
				'catname' => 'Shop iPod',
				'catparentid' => '0',
			),
			4 => array (
				'categoryid' => '4',
				'catname' => 'Accessories',
				'catparentid' => '1',
			),
			5 => array (
				'categoryid' => '5',
				'catname' => 'Notebook Cases',
				'catparentid' => '1',
			),
			6 => array (
				'categoryid' => '6',
				'catname' => 'Video Devices',
				'catparentid' => '1',
			),
			7 => array (
				'categoryid' => '7',
				'catname' => 'Accessories',
				'catparentid' => '2',
			),
			8 => array (
				'categoryid' => '8',
				'catname' => 'Accessories',
				'catparentid' => '3',
			),
			9 => array (
				'categoryid' => '9',
				'catname' => 'Software',
				'catparentid' => '1',
			),
			10 => array (
				'categoryid' => '10',
				'catname' => 'Rock/Pebbles',
				'catparentid' => '11',
			),
			11 => array (
				'categoryid' => '11',
				'catname' => 'Rock',
				'catparentid' => '0',
			),
			12 => array (
				'categoryid' => '12',
				'catname' => 'Pebbles',
				'catparentid' => '11',
			),
			13 => array (
				'categoryid' => '13',
				'catname' => 'Rocks; Big Ones',
				'catparentid' => '10',
			),
		);

		$categories = array();
		$categoryTrails = array();
		// generate trails for each one
		foreach ($categoryCache as $categoryId => $category) {
			$categoryTrail = '';
			$parentId = $categoryId;
			do {
				$categoryTrail = Interspire_String::escapeString('/', '\\', $categoryCache[$parentId]['catname']) . '/' . $categoryTrail;
				$parentId = $categoryCache[$parentId]['catparentid'];
			}
			while(isset($categoryCache[$parentId]) && $parentId != 0);

			$categoryTrail = rtrim($categoryTrail, '/');
			// Cache the result
			$categoryTrails[] = $categoryTrail;
			$categories[] = $category['catname'];
		}
		$categories = sort(array_unique($categories));

		// do some work
		$escapedTrails = array();
		foreach ($categoryTrails as $trail) {
			$escapedTrails[] = Interspire_String::escapeString(';', '\\', $trail);
		}
		$bigChunk = implode(';', $escapedTrails);

		// unwind
		$chunks = Interspire_String::explodeEscape(';', '\\', $bigChunk);

		$this->assertEquals($categoryTrails, $chunks);

		$chunkCats = array();
		foreach ($chunks as $chunk) {
			$chunkCatsPart = Interspire_String::explodeEscape('/', '\\', $chunk);
			$chunkCats = array_merge($chunkCats, $chunkCatsPart);
		}

		$chunkCats = sort(array_unique($chunkCats));

		$this->assertEquals($categories, $chunkCats);

	}

}