<?php

namespace Unit\Model\Analytics\Metrics;

use Analytics\Metrics\CashewLoggingSubscriber;
use PHPUnit_Framework_TestCase;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\GenericEvent;

class CashewLoggingSubscriberTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
                             ->getMockForAbstractClass();

        $this->subscriber = new CashewLoggingSubscriber($this->logger);
    }

    public function testStatSuccess()
    {
        $event = new Event();
        $event->exception = false;

        $this->logger->expects($this->never())
            ->method('critical');
    }
    public function testExceptionOnStat()
    {
        $event = new Event();
        $event->startTime   = 0;
        $event->endTime     = 0;
        $event->path        = "test-path";
        $event->backendPath = "test-backend-path";
        $event->cacheHit    = false;
        $event->success     = false;
        $event->stat        = false;
        $event->exception = new \RuntimeException("swift: curl error ###");

        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->anything(), array(
                'startTime'     => 0,
                'endTime'       => 0,
                'path'          => 'test-path',
                'backendPath'   => 'test-backend-path',
                'cacheHit'      => false,
                'success'       => false,
                'stat'          => false,
                'exception'     => "swift: curl error ###",
            ));

        $this->subscriber->logStatException($event);
    }
}
