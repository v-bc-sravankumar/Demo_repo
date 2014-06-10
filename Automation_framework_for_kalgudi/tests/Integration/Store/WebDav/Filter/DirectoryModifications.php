<?php

/**
 * Unit tests to make sure requests to GET, MOVE, COPY, and DELETE requets
 * on directories are filtered correctly.
 */
class Unit_Lib_Store_WebDav_Filter_DirectoryModifications extends Interspire_UnitTest
{

	/** @var Store_WebDav_Filter_DirectoryModifications */
	private $filter;

	/**
	 * An array of all default directory paths in WebDav.
	 * @var Array
	 */
	protected static $protectedPaths = array(
		'content',
		'exports',
		'template',
		'import_files',
		'product_downloads',
		'product_images',
		'product_images/a',
		'product_images/z',
		'product_images/attribute_rule_images',
		'product_images/attribute_value_images',
		'product_images/configured_products',
		'product_images/configured_products_tmp',
		'product_images/header_images',
		'product_images/optionset_rule_images',
		'product_images/sample_images',
		'product_images/uploaded_images',
		'product_images/vendor_images',
		'product_images/wrap_images',
	);

	/**
	 * Data provider for the GET/PUT/DELETE tests.
	 *
	 * @return Array
	 */
	public static function protectedPathsDataProvider()
	{
		$testData = array();

		foreach (self::$protectedPaths as $protectedPath) {
			$testData[] = array($protectedPath);
		}

		return $testData;
	}

	/**
	 * Builds COPY request test data to verify no folders
	 * can be duplicated as a protected directory name.
	 *
	 * @return Array
	 */
	public static function copyDataProvider()
	{
		$testData = array();

		foreach (self::$protectedPaths as $protectedPath) {
			$originPath = $protectedPath . '-backup';
			$testData[] = array($originPath, $protectedPath);
		}

		return $testData;
	}

	/**
	 * Builds MOVE request paths to make sure no protected directory
	 * can be renamed, as well as making sure no directory can be renamed
	 * to a protected directory name.
	 *
	 * @return Array
	 */
	public static function moveDataProvider()
	{
		$testData = array();

		foreach (self::$protectedPaths as $protectedPath) {
			// add the protected directory as a source.
			$destinationPath = $protectedPath . '-new';
			$testData[] = array($protectedPath, $destinationPath);

			// add the protected direcotry as the destination.
			$originPath = $protectedPath . '-backup';
			$testData[] = array($originPath, $protectedPath);
		}

		return $testData;
	}

	/**
	 * COPY and MOVE requests supply a "destination" header along with the request,
	 * the destination path is retrieved by calling the Sabre\DAV\Server::getCopyAndMoveInfo()
	 * method which returns an array containing the destination path, and if it exists or not.
	 *
	 * @param string $destinationPath The destination path which should be mocked.
	 * @return Sabre\DAV\Server
	 */
	private function _getSabreDAVServerMock($destinationPath)
	{
		// for the scope of these tests lets assume
		// the destination folder does not exist.
		$mockCopyAndMoveData = array(
			'destination' => $destinationPath,
			'destinationExists' => false,
			'destinationNode' => false,
		);

		$serverMock = $this->getMock('Sabre\DAV\Server');

		$serverMock->expects($this->once())
			->method('subscribeEvent')
			->will($this->returnValue(true));

		$serverMock->expects($this->once())
			->method('getCopyAndMoveInfo')
			->will($this->returnValue($mockCopyAndMoveData));

		return $serverMock;
	}

	public function setUp()
	{
		$this->filter = new Store_WebDav_Filter_DirectoryModifications();
	}

	/**
	 * Test COPY requests are disallowed on protected directories.
	 *
	 * @dataProvider copyDataProvider
	 */
	public function testCopyProtectedDirectoryDisallowed($originPath, $destinationPath)
	{
		$serverMock = $this->_getSabreDAVServerMock($destinationPath);
		$this->filter->initialize($serverMock);

		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('COPY', $originPath);
	}

	/**
	 * Test MOVE requests are disallowed on protected directories.
	 *
	 * @dataProvider moveDataProvider
	 * @return void
	 */
	public function testMoveProtectedDirectoryDisallowed($originPath, $destinationPath)
	{
		$serverMock = $this->_getSabreDAVServerMock($destinationPath);
		$this->filter->initialize($serverMock);

		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('MOVE', $originPath);
	}

	/**
	 * Test GET requests are allowed on protected directories.
	 *
	 * @dataProvider protectedPathsDataProvider
	 * @return void
	 */
	public function testGetProtectedDirectoryAllowed($path)
	{
		$result = $this->filter->beforeMethodHandler('GET', $path);

		$this->assertTrue($result);
	}

	/**
	 * Test MKCOL requests are for protected directory paths.
	 *
	 * @dataProvider protectedPathsDataProvider
	 * @return void
	 */
	public function testMkcolProtectedDirectoryDisallowed($path)
	{
		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('MKCOL', $path);
	}

	/**
	 * Test DELETE requests are disallowed on protected directories.
	 *
	 * @dataProvider protectedPathsDataProvider
	 * @return void
	 */
	public function testDeleteProtectedDirectoryDisallowed($path)
	{
		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('DELETE', $path);
	}

	public function testDeleteProductImagesSubDirectoryAllowed()
	{
		$result = $this->filter->beforeMethodHandler('DELETE', 'product_images/my_directory');

		$this->assertTrue($result);
	}

	public function testMkcolProductImagesSubDirectoryAllowed()
	{
		$result = $this->filter->beforeMethodHandler('MKCOL', 'product_images/my_directory');

		$this->assertTrue($result);
	}

	public function testCopyProductImagesSubDirectoryAllowed()
	{
		$serverMock = $this->_getSabreDAVServerMock('product_images/new_directory');

		$this->filter->initialize($serverMock);
		$result = $this->filter->beforeMethodHandler('COPY', 'product_images/my_directory');

		$this->assertTrue($result);
	}

	public function testMoveProductImagesSubDirectoryAllowed()
	{
		$serverMock = $this->_getSabreDAVServerMock('product_images/new_directory');

		$this->filter->initialize($serverMock);
		$result = $this->filter->beforeMethodHandler('MOVE', 'product_images/old_directory');

		$this->assertTrue($result);
	}
}