<?php

// require_once(APP_ROOT."/includes/classes/class.batch.importer.php");
// require_once(APP_ROOT."/includes/importer/products.php");

class ImporterMock extends ISC_BATCH_IMPORTER_BASE
{
	public function __construct(){}
}

class FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider filenameDataProvider
     * @param string $filename
     * @param bool $expected
     */
	public function testFileExtensionValidation($filename, $expected)
	{
        $importer = new ImporterMock;
        $actual = $importer->validateUploadedFileExtension($filename);
        $this->assertEquals($expected, $actual);
	}

	public function filenameDataProvider()
	{
        return array(
            array('test.xls', false),
            array('test.xlsx', false),
            array('test.XLSX', false),
            array('test', true),
            array('test.', true),
            array('test.csv', true),
            array('test.tsv', true),
            array('test.txt', true),
        );
	}
}
