<?php

namespace Unit\Model\Analytics\Metrics;

use Analytics\Metrics\CashewCacheStatsdSubscriber;
use PHPUnit_Framework_TestCase;
use Symfony\Component\EventDispatcher\Event;

class CashewCacheStatsdSubscriberTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->statsd = $this->getMock("Interspire_Statsd", array(
            "increment",
            "timing",
        ));

        $this->subscriber = new CashewCacheStatsdSubscriber($this->statsd, "%cachetype%,%metric%");
    }

    public function getIncrementData()
    {
        $data = array();

        $data[] = array("stat", false, "stat,miss", true, "stat,success");
        $data[] = array("stat", false, "stat,miss", false, "stat,fail");
        $data[] = array("stat", true, "stat,hit", true, "stat,success");

        $data[] = array("stream_open", false, "stream_open,miss", true, "stream_open,success");
        $data[] = array("stream_open", false, "stream_open,miss", false, "stream_open,fail");
        $data[] = array("stream_open", true, "stream_open,hit", true, "stream_open,success");

        $data[] = array("opendir", false, "opendir,miss", true, "opendir,success");
        $data[] = array("opendir", false, "opendir,miss", false, "opendir,fail");
        $data[] = array("opendir", true, "opendir,hit", true, "opendir,success");

        return $data;
    }

    /**
     * @dataProvider getIncrementData
     */
    public function testIncrementsCorrectly($eventName, $cached, $cacheKey, $success, $successKey)
    {
        $methods = CashewCacheStatsdSubscriber::getSubscribedEvents();
        $method  = $methods[$eventName];

        $event = new Event();
        $event->setName($eventName);
        $event->startTime = 0;
        $event->endTime   = 0;
        $event->cacheHit  = $cached;
        $event->success   = $success;

        $this->statsd->expects($this->at(0))
                     ->method("increment")
                     ->with($cacheKey);
        $this->statsd->expects($this->at(1))
                     ->method("increment")
                     ->with($successKey);

        $this->subscriber->$method($event);
    }

    public function getTimingData()
    {
        $data = array();

        $data[] = array(0, 0, 0);
        $data[] = array(0, 1, 1000);
        $data[] = array(1, 3, 2000);
        $data[] = array(2, 2.5, 500);

        $data[] = array(0, -1, null);
        $data[] = array(0, 1, 1000, true, "hit_duration");

        return $data;
    }

    /**
     * @dataProvider getTimingData
     */
    public function testTimesCorrectly($startTime, $endTime, $duration, $cacheHit = false, $durationKey = "miss_duration")
    {
        $event = new Event();
        $event->setName("stat");
        $event->cacheHit  = $cacheHit;
        $event->startTime = $startTime;
        $event->endTime   = $endTime;
        $event->success   = false;

        $key = "stat,$durationKey";
        $method = "logCacheableEvent";

        if ($duration === null) {
            $this->statsd->expects($this->never())
                         ->method("timing");
        } else {
            $this->statsd->expects($this->once())
                         ->method("timing")
                         ->with($key, $duration);
        }

        $this->subscriber->$method($event);
    }
}
