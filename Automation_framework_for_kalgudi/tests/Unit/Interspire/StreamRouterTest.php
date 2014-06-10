<?php

use org\bovigo\vfs\vfsStream;

class Unit_Interspire_StreamRouterTest extends PHPUnit_Framework_TestCase
{
	private $_routes;

	public function setUp()
	{
		// save any routes we have before running the tests.
		$this->_routes = Interspire_StreamRouter::getAllRoutes();

		Interspire_StreamRouter::removeAllRoutes();

		// set up a simple test fixture - points nowhere, for testing translations only
		Interspire_StreamRouter::addRoute('test-fixture', '#^test-fixture://simple/(.+)$#', 'test-fixture-simple://$1', true);

		// set up a fixture that points to vfsStream - for testing real file operations
		$fs = array(
			'root' => array(
				'directory' => array(
					'directory-text-2.txt' => 'hello',
					'directory-text-1.txt' => 'hello',
				),
				'empty-directory' => array(),
				'text.txt' => 'world',
			),
		);

		vfsStream::setup('/', 0755, $fs);

		Interspire_StreamRouter::addRoute('test-fixture', '#^test-fixture://vfs(/(.*)|)$#', vfsStream::url('root') . '$1', true);
	}

	public function tearDown()
	{
		Interspire_StreamRouter::removeAllRoutes();

		// restore saved routes
		foreach ($this->_routes as $scheme => $routes) {
			foreach ($routes as $from => $to) {
				Interspire_StreamRouter::addRoute($scheme, $from, $to, true);
			}
		}
	}

	public function testCanValidateValidPattern()
	{
		$this->assertTrue(Interspire_StreamRouter::validatePattern('#/#'));
	}

	public function testValidatingInvalidPatternDoesNotIssuePhpError()
	{
		$this->assertFalse(Interspire_StreamRouter::validatePattern('#/'));
	}

	public function testCanAddRoute()
	{
		$this->assertTrue(Interspire_StreamRouter::addRoute('foo', '#^foo://bar/(.*)$#', 'baz://$1', true));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCannotAddRouteWithInvalidFromPattern()
	{
		$this->assertFalse(Interspire_StreamRouter::addRoute('foo', 'foo://bar', 'baz://$1', true));
	}

	public function testCanTranslateSimpleUrl()
	{
		$input = 'test-fixture://simple/foo/bar';
		$expected = 'test-fixture-simple://foo/bar';

		$this->assertSame($expected, Interspire_StreamRouter::translateUrl($input));
	}

	public function testCannotTranslateUnmappedScheme()
	{
		$input = 'test-fixture-unmapped://simple/foo/bar';

		$this->assertFalse(Interspire_StreamRouter::translateUrl($input));
	}

	public function testCannotTranslateUnmatchedPattern()
	{
		$input = 'test-fixture://simple-unmapped/foo/bar';

		$this->assertFalse(Interspire_StreamRouter::translateUrl($input));
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testCannotAddDuplicateMapping()
	{
		$this->assertFalse(Interspire_StreamRouter::addRoute('test-fixture', '#^test-fixture://simple/(.+)$#', 'test-fixture-simple://$1', true));
	}

	public function testCanAddSeveralSchemeMappings()
	{
		$this->assertTrue(Interspire_StreamRouter::addRoute('test-fixture', '#^test-fixture://simple-other/(.+)$#', 'test-fixture-simple-other://$1', true));
	}

	public function testFirstOfSeveralSchemeMappingsCanBeMatched()
	{
		Interspire_StreamRouter::addRoute('test-fixture', '#^test-fixture://simple-other/(.+)$#', 'test-fixture-simple-other://$1', true);

		$input = 'test-fixture://simple/foo/bar';
		$expected = 'test-fixture-simple://foo/bar';

		$this->assertSame($expected, Interspire_StreamRouter::translateUrl($input));
	}

	public function testLastOfSeveralSchemeMappingsCanBeMatched()
	{
		Interspire_StreamRouter::addRoute('test-fixture', '#^test-fixture://simple-other/(.+)$#', 'test-fixture-simple-other://$1', true);

		$input = 'test-fixture://simple-other/foo/bar';
		$expected = 'test-fixture-simple-other://foo/bar';

		$this->assertSame($expected, Interspire_StreamRouter::translateUrl($input));
	}

	public function testCanMatchSeveralSchemeMappings()
	{
		Interspire_StreamRouter::addRoute('test-other', '#^test-other://simple/(.+)$#', 'test-other-simple://$1', true);

		$input = 'test-other://simple/foo/bar';
		$expected = 'test-other-simple://foo/bar';

		$this->assertSame($expected, Interspire_StreamRouter::translateUrl($input));
	}

	public function testCanCheckDirectoryExists()
	{
		$this->assertTrue(file_exists('test-fixture://vfs/directory'));
	}

	public function testCanCheckDirectoryDoesNotExist()
	{
		$this->assertFalse(file_exists('test-fixture://vfs/nonexistent-directory'));
	}

	public function testCanCheckFileExists()
	{
		$this->assertTrue(file_exists('test-fixture://vfs/text.txt'));
	}

	public function testCanCheckFileDoesNotExist()
	{
		$this->assertFalse(file_exists('test-fixture://vfs/nonexistent-file'));
	}

	public function testCanStatFile()
	{
		$this->assertInternalType('array', stat('test-fixture://vfs/text.txt'));
	}

	public function testCorrectFileMtime()
	{
		$this->assertLessThanOrEqual(time(), filemtime('test-fixture://vfs/text.txt'));
	}

	public function testCanReadFile()
	{
		$this->assertInternalType('string', file_get_contents('test-fixture://vfs/text.txt'));
	}

	public function testFileIsReadCorrectly()
	{
		$this->assertSame('world', file_get_contents('test-fixture://vfs/text.txt'));
	}

	public function testCanScanDirectory()
	{
		$this->assertInternalType('array', scandir('test-fixture://vfs/directory'));
	}

	public function testDirectoryIsScannedCorrectly()
	{
		$expected = array(
			'directory-text-1.txt',
			'directory-text-2.txt',
		);

		$this->assertSame($expected, scandir('test-fixture://vfs/directory'));
	}

	public function testCanUnlinkFile()
	{
		$this->assertTrue(unlink('test-fixture://vfs/text.txt'));
	}

	public function testFileIsUnlinked()
	{
		unlink('test-fixture://vfs/text.txt');
		$this->assertFileNotExists('test-fixture://vfs/text.txt');
	}

	public function testCanRenameFile()
	{
		$this->assertTrue(rename('test-fixture://vfs/text.txt', 'test-fixture://vfs/renamed.txt'));
	}

	public function testOldFileIsRenamed()
	{
		rename('test-fixture://vfs/text.txt', 'test-fixture://vfs/renamed.txt');
		$this->assertFileNotExists('test-fixture://vfs/text.txt');
	}

	public function testNewFileIsRenamed()
	{
		rename('test-fixture://vfs/text.txt', 'test-fixture://vfs/renamed.txt');
		$this->assertFileExists('test-fixture://vfs/renamed.txt');
	}

	public function testCanWriteToExistingFile()
	{
		$this->assertSame(3, file_put_contents('test-fixture://vfs/text.txt', 'foo'));
	}

	public function testExistingFileIsWrittenCorrectly()
	{
		file_put_contents('test-fixture://vfs/text.txt', 'foo');
		$this->assertSame('foo', file_get_contents('test-fixture://vfs/text.txt'));
	}

	public function testCanWriteToNewFile()
	{
		$this->assertSame(3, file_put_contents('test-fixture://vfs/new.txt', 'foo'));
	}

	public function testNewFileIsWrittenCorrectly()
	{
		file_put_contents('test-fixture://vfs/new.txt', 'foo');
		$this->assertSame('foo', file_get_contents('test-fixture://vfs/new.txt'));
	}

	public function testCanCreateDirectory()
	{
		$this->assertTrue(mkdir('test-fixture://vfs/new-directory'));
	}

	public function testDirectoryCreated()
	{
		mkdir('test-fixture://vfs/new-directory');
		$this->assertTrue(is_dir('test-fixture://vfs/new-directory'));
	}

	public function testCanRemoveDirectory()
	{
		$this->assertTrue(rmdir('test-fixture://vfs/empty-directory'));
	}

	public function testDirectoryRemoved()
	{
		rmdir('test-fixture://vfs/empty-directory');
		$this->assertFalse(is_dir('test-fixture://vfs/empty-directory'));
	}

	public function testCanRemoveCreatedDirectory()
	{
		mkdir('test-fixture://vfs/new-directory');
		$this->assertTrue(rmdir('test-fixture://vfs/new-directory'));
	}

	public function testCreatedDirectoryRemoved()
	{
		mkdir('test-fixture://vfs/new-directory');
		rmdir('test-fixture://vfs/new-directory');
		$this->assertFalse(is_dir('test-fixture://vfs/new-directory'));
	}

	public function dataProviderUnencodedFilenames()
	{
		$data = array();

		$data[] = array('file with spaces');
		$data[] = array('file trailing space ');
		$data[] = array(' file leading space');
		$data[] = array('character ! test');
		$data[] = array('character @ test');
		$data[] = array('character # test');
		$data[] = array('character $ test');
		$data[] = array('character % test');
		$data[] = array('character ^ test');
		$data[] = array('character & test');
		$data[] = array('character * test');
		$data[] = array('character ( test');
		$data[] = array('character ) test');
		$data[] = array('character - test');
		$data[] = array('character = test');

		// vfsStream doesn't support these characters on filenames, which is probably fine
//		$data[] = array("character \0 test");
//		$data[] = array("character \r test");
//		$data[] = array("character \n test");

		return $data;
	}

	/**
	 * @dataProvider dataProviderUnencodedFilenames
	 * @param string $path
	 */
	public function testUrlEncodingIsNotRequiredForStreamRoutedFileWriting($path)
	{
		$path = 'test-fixture://vfs/' . $path;
		$this->assertSame(3, file_put_contents($path, 'foo'), "file write error for path $path");
	}

	/**
	 * @dataProvider dataProviderUnencodedFilenames
	 * @param string $path
	 */
	public function testUrlEncodingIsNotRequiredForStreamRoutedFileReading($path)
	{
		$path = 'test-fixture://vfs/' . $path;
		file_put_contents($path, "foo");
		$this->assertSame('foo', file_get_contents($path), "file contents mismatch for path $path");
	}

	/**
	 * @dataProvider dataProviderUnencodedFilenames
	 * @param string $path
	 */
	public function testUrlEncodingIsNotRequiredForStreamRoutedDirectoryMaking($path)
	{
		$path = 'test-fixture://vfs/' . $path;
		$this->assertTrue(mkdir($path), "mkdir on $path failed");
	}
}
