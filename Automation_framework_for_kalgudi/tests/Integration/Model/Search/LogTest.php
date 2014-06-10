<?php

namespace Integration\Model\Search;

use Search\Log;

/**
 * @group nosample
 */
class LogTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveLoad()
    {
        $time = time();

        $log = new Log();
        $log
            ->setSearchQuery('search query')
            ->setResultCount(250)
            ->setHasClickThrough(true)
            ->setDateCreated($time);

        $this->assertEquals('search query', $log->getSearchQuery());
        $this->assertEquals(250, $log->getResultCount());
        $this->assertTrue($log->getHasClickThrough());
        $this->assertEquals($time, $log->getDateCreated());

        $this->assertTrue($log->save(), 'Failed to save log');

        $logId = $log->getId();

        $loadLog = new Log();
        if (!$loadLog->load($logId)) {
            $this->fail('Failed to load log ' . $logId);
        }

        $this->assertEquals($log->getSearchQuery(), $loadLog->getSearchQuery());
        $this->assertEquals($log->getResultCount(), $loadLog->getResultCount());
        $this->assertEquals($log->getHasClickThrough(), $loadLog->getHasClickThrough());
        $this->assertEquals($log->getDateCreated(), $loadLog->getDateCreated());

        $log->delete();
    }
}