<?php

namespace Integration\Repositories;

use Repository\SearchCorrections;
use Search\Correction;
use Search\Log;

/**
 * @group nosample
 */
class SearchCorrectionsTest extends \PHPUnit_Framework_TestCase
{
    public function testLogSearchCorrection()
    {
        $log = new Log();
        $log
            ->setSearchQuery('original query')
            ->setResultCount(10);

        if (!$log->save()) {
            $this->fail('Failed to save log');
        }

        $repository = new SearchCorrections();
        $result = $repository->logSearchCorrection($log->getId(), 'new query', 20);

        $this->assertTrue($result, 'failed to save correction');

        $correction = Correction::find("correction = 'new query'")->first();
        $this->assertNotEmpty($correction, 'could not find correction');

        $this->assertEquals(Correction::CORRECTION_TYPE_SPELLING, $correction->getCorrectionType());
        $this->assertEquals('new query', $correction->getSearchCorrection());
        $this->assertEquals(20, $correction->getResultCount());
        $this->assertEquals('original query', $correction->getOriginalSearchQuery());
        $this->assertEquals(10, $correction->getOriginalSearchResultCount());
        $this->assertNotEmpty($correction->getDateCreated());

        $correction->delete();
        $log->delete();
    }

    public function testLogSearchRecommendation()
    {
        $log = new Log();
        $log
            ->setSearchQuery('the original query')
            ->setResultCount(30);

        if (!$log->save()) {
            $this->fail('Failed to save log');
        }

        $repository = new SearchCorrections();
        $result = $repository->logSearchRecommendation($log->getId(), 'the new query', 50);

        $this->assertTrue($result, 'failed to save correction');

        $correction = Correction::find("correction = 'the new query'")->first();
        $this->assertNotEmpty($correction, 'could not find correction');

        $this->assertEquals(Correction::CORRECTION_TYPE_RECOMMENDATION, $correction->getCorrectionType());
        $this->assertEquals('the new query', $correction->getSearchCorrection());
        $this->assertEquals(50, $correction->getResultCount());
        $this->assertEquals('the original query', $correction->getOriginalSearchQuery());
        $this->assertEquals(30, $correction->getOriginalSearchResultCount());
        $this->assertNotEmpty($correction->getDateCreated());

        $correction->delete();
        $log->delete();
    }

    public function testLogSearchCorrectionWithoutSearchIdReturnsFalse()
    {
        $repository = new SearchCorrections();
        $result = $repository->logSearchRecommendation('', 'new query', 50);
        $this->assertFalse($result);
    }

    public function testLogSearchCorrectionWithMissingLogReturnsFalse()
    {
        $repository = new SearchCorrections();
        $result = $repository->logSearchRecommendation(12, 'new query', 50);
        $this->assertFalse($result);
    }
}
