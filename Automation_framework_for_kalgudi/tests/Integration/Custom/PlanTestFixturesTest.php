<?php


namespace Integration\Custom;

class PlanTestFixturesTest extends \PHPUnit_Framework_TestCase
{
    private $path;

    public function setUp()
    {
        $this->path = realpath(dirname(__FILE__) . '/../../Fixtures/Plans');
    }

    public function testXmlFixturesPathExists()
    {
        $this->assertFileExists($this->path);
    }

    /**
     * Test that files in /tests/Fixtures/Plans/*.xml are valid XML
     */
	public function testXmlFixtureFilesAreValid()
	{
        foreach (glob($this->path . '/*.xml') as $file) {
            /* Assert XML is valid by comparing the file contents against an XML parser */
            $this->assertXmlStringEqualsXmlString(file_get_contents($file), simplexml_load_file($file)->asXml());
        }
	}
}
