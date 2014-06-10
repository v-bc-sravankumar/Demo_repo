<?php

namespace Unit\Store\Search;

use Store\Search\ElasticEventSubscriber;
use Bigcommerce\SearchClient\Provider\Elastic\Event;
use Bigcommerce\SearchClient\Provider\Elastic\Events;

class ElasticEventSubscriberTest extends \PHPUnit_Framework_TestCase
{
    private $statsd;
    private $subscriber;

    private function getEvent($curlInfo = array())
    {
        $curlInfo = array_merge(array(
            "http_code"          => 0,
            "total_time"         => 0,
            "namelookup_time"    => 0,
            "connect_time"       => 0,
            "pretransfer_time"   => 0,
            "starttransfer_time" => 0,
            "size_download"      => 0,
            "size_upload"        => 0,
            "speed_download"     => 0,
            "speed_upload"       => 0,
        ), $curlInfo);

        return new Event($curlInfo);
    }

    public function setUp()
    {
        $this->statsd = $this
            ->getMockBuilder('\Store_Statsd')
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = new ElasticEventSubscriber($this->statsd);
    }

    private function setTimingExpectation($index, $metric, $value)
    {
        $this->statsd
            ->expects($this->at($index))
            ->method('timing')
            ->with(
                $this->equalTo($metric),
                $this->equalTo($value)
            );
    }

    private function setCountExpectation($index, $metric, $value)
    {
        $this->statsd
            ->expects($this->at($index))
            ->method('count')
            ->with(
                $this->equalTo($metric),
                $this->equalTo($value)
            );
    }

    public function testResponseCodeIsRecorded()
    {
        $this->statsd
            ->expects($this->once())
            ->method('increment')
            ->with($this->equalTo('search.elastic.http.my_event.response_code.200'));

        $event = $this->getEvent(array('http_code' => 200));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testTotalTimeIsRecorded()
    {
        $this->setTimingExpectation(1, 'search.elastic.http.total_time', 1000);
        $event = $this->getEvent(array('total_time' => 1));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testNameLookupTimeIsRecorded()
    {
        $this->setTimingExpectation(2, 'search.elastic.http.namelookup_time', 2000);
        $event = $this->getEvent(array('namelookup_time' => 2));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testConnectTimeIsRecorded()
    {
        $this->setTimingExpectation(3, 'search.elastic.http.connect_time', 3000);
        $event = $this->getEvent(array('connect_time' => 3));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testPreTransferTimeIsRecorded()
    {
        $this->setTimingExpectation(4, 'search.elastic.http.pretransfer_time', 4000);
        $event = $this->getEvent(array('pretransfer_time' => 4));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testStartTransferTimeIsRecorded()
    {
        $this->setTimingExpectation(5, 'search.elastic.http.starttransfer_time', 5000);
        $event = $this->getEvent(array('starttransfer_time' => 5));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodTotalTimeIsRecorded()
    {
        $this->setTimingExpectation(6, 'search.elastic.http.my_event.total_time', 6000);
        $event = $this->getEvent(array('total_time' => 6));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodNameLookupTimeIsRecorded()
    {
        $this->setTimingExpectation(7, 'search.elastic.http.my_event.namelookup_time', 7000);
        $event = $this->getEvent(array('namelookup_time' => 7));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodConnectTimeIsRecorded()
    {
        $this->setTimingExpectation(8, 'search.elastic.http.my_event.connect_time', 8000);
        $event = $this->getEvent(array('connect_time' => 8));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodPreTransferTimeIsRecorded()
    {
        $this->setTimingExpectation(9, 'search.elastic.http.my_event.pretransfer_time', 9000);
        $event = $this->getEvent(array('pretransfer_time' => 9));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodStartTransferTimeIsRecorded()
    {
        $this->setTimingExpectation(10, 'search.elastic.http.my_event.starttransfer_time', 10000);
        $event = $this->getEvent(array('starttransfer_time' => 10));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodSpeedDownloadIsRecorded()
    {
        $this->setCountExpectation(11, 'search.elastic.http.my_event.speed_download', 11);
        $event = $this->getEvent(array('speed_download' => 11));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodSpeedUploadIsRecorded()
    {
        $this->setCountExpectation(12, 'search.elastic.http.my_event.speed_upload', 12);
        $event = $this->getEvent(array('speed_upload' => 12));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodSizeDownloadIsRecorded()
    {
        $this->setCountExpectation(13, 'search.elastic.http.my_event.size_download', 13);
        $event = $this->getEvent(array('size_download' => 13));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }

    public function testMethodSizeUploadIsRecorded()
    {
        $this->setCountExpectation(14, 'search.elastic.http.my_event.size_upload', 14);
        $event = $this->getEvent(array('size_upload' => 14));
        $this->subscriber->logCurlInfo($event, 'my_event');
    }
}
