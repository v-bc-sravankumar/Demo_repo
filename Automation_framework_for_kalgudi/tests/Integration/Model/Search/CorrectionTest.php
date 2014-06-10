<?php

namespace Integration\Model\Search;

use Search\Correction;

/**
 * @group nosample
 */
class CorrectionTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveLoad()
    {
        $time = time();

        $correction = new Correction();
        $correction
            ->setCorrectionType(Correction::CORRECTION_TYPE_SPELLING)
            ->setSearchCorrection('foo bar')
            ->setResultCount(43)
            ->setOriginalSearchQuery('foo')
            ->setOriginalSearchResultCount(4)
            ->setDateCreated($time);

        $this->assertEquals(Correction::CORRECTION_TYPE_SPELLING, $correction->getCorrectionType());
        $this->assertEquals('foo bar', $correction->getSearchCorrection());
        $this->assertEquals(43, $correction->getResultCount());
        $this->assertEquals('foo', $correction->getOriginalSearchQuery());
        $this->assertEquals(4, $correction->getOriginalSearchResultCount());
        $this->assertEquals($time, $correction->getDateCreated());

        $correctionId = $correction->save();

        $this->assertNotEmpty($correctionId);

        $loadCorrection = new Correction();
        if (!$loadCorrection->load($correctionId)) {
            $this->fail('Failed to load correction ' . $correctionId);
        }

        $this->assertEquals($correction->getCorrectionType(), $loadCorrection->getCorrectionType());
        $this->assertEquals($correction->getSearchCorrection(), $loadCorrection->getSearchCorrection());
        $this->assertEquals($correction->getResultCount(), $loadCorrection->getResultCount());
        $this->assertEquals($correction->getOriginalSearchQuery(), $loadCorrection->getOriginalSearchQuery());
        $this->assertEquals($correction->getOriginalSearchResultCount(), $loadCorrection->getOriginalSearchResultCount());
        $this->assertEquals($correction->getDateCreated(), $loadCorrection->getDateCreated());

        $correction->delete();
    }

    public function correctionTypeDataProvider()
    {
        return array(
            array(Correction::CORRECTION_TYPE_SPELLING),
            array(Correction::CORRECTION_TYPE_RECOMMENDATION),
        );
    }

    /**
     * @dataProvider correctionTypeDataProvider
     */
    public function testSetGetCorrectionType($type)
    {
        $correction = new Correction();
        $correction->setCorrectionType($type);
        $this->assertEquals($type, $correction->getCorrectionType());
    }

    public function testInvalidCorrectionTypeSetsToSpelling()
    {
        $correction = new Correction();
        $correction->setCorrectionType('foo');
        $this->assertEquals(Correction::CORRECTION_TYPE_SPELLING, $correction->getCorrectionType());
    }
}
