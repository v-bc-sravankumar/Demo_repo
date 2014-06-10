<?php

/**
 * Unit tests to make sure requests to..
 */
class Unit_Lib_Store_WebDav_Filter_InvalidFilePaths extends Interspire_UnitTest
{

	/** @var Store_WebDav_Filter_InvalidFilePaths */
	private $filter;

	/**
	 * List of file types which should not allowed through the filter.
	 * @var Array
	 */
	private static $invalidFileTypes = array(
		'.php',
		'.cgi',
		'.pl',
		'.shtml',
		'.phtml',
	);


	public static function invalidFilePathsDataProvider()
	{
		$filePaths = array();

		foreach (self::$invalidFileTypes as $fileType) {
			$filePaths[] = array('/test' . $fileType);
		}

		return $filePaths;
	}

	public static function invalidMoveAndCopyPathsDataProvider()
	{
		$filePaths = array();

		foreach (self::$invalidFileTypes as $fileType) {
			$filePaths[] = array('test.txt', '/test' . $fileType);
		}

		return $filePaths;
	}

	private function _getServerMock($destinationPath)
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
		$this->filter = new Store_WebDav_Filter_InvalidFilePaths();
	}

	/**
	 * @dataProvider invalidFilePathsDataProvider
	 */
	public function testCreateInvalidFileTypeDisallowed($filePath)
	{
		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('PUT', $filePath);
	}

	/**
	 * @dataProvider invalidFilePathsDataProvider
	 */
	public function testReadInvalidFileTypeDisallowed($filePath)
	{
		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('GET', $filePath);
	}

	/**
	 * @dataProvider invalidFilePathsDataProvider
	 */
	public function testDeleteInvalidFileTypeAllowed($filePath)
	{
		$result = $this->filter->beforeMethodHandler('DELETE', $filePath);

		$this->assertTrue($result);
	}

	/**
	 * @dataProvider invalidMoveAndCopyPathsDataProvider
	 */
	public function testRenameToInvalidTypeDisallowed($originPath, $destinationPath)
	{
		$serverMock = $this->_getServerMock($destinationPath);
		$this->filter->initialize($serverMock);

		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('MOVE', $originPath);
	}

	/**
	 * @dataProvider invalidMoveAndCopyPathsDataProvider
	 */
	public function testCopyToInvalidTypeDisallowed($originPath, $destinationPath)
	{
		$serverMock = $this->_getServerMock($destinationPath);
		$this->filter->initialize($serverMock);

		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('COPY', $originPath);
	}

	/**
	 * @dataProvider invalidFilePathsDataProvider
	 */
	public function testInvalidUppercaseFileNamesDisallowed($filePath)
	{
		$filePath = strtoupper($filePath);

		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('PUT', $filePath);
	}

	public function testCreateValidFileTypeAllowed()
	{
		$result = $this->filter->beforeMethodHandler('PUT', '/product_images/import/test.jpg');

		$this->assertTrue($result);
	}

	public function testMoveValidFileTypeAllowed()
	{
		$serverMock = $this->_getServerMock('/product_images/import/new.jpg');
		$this->filter->initialize($serverMock);

		$result = $this->filter->beforeMethodHandler('MOVE', '/product_images/import/old.jpg');

		$this->assertTrue($result);
	}

	public function testCopyValidFileTypeAllowed()
	{
		$serverMock = $this->_getServerMock('/product_images/import/new.jpg');
		$this->filter->initialize($serverMock);

		$result = $this->filter->beforeMethodHandler('COPY', '/product_images/import/old.jpg');

		$this->assertTrue($result);
	}

	public function testDeleteWebDavReadmeDisallowed()
	{
		$this->setExpectedException('Sabre\DAV\Exception\Forbidden');
		$this->filter->beforeMethodHandler('DELETE', 'README_WebDav.txt');
	}
}