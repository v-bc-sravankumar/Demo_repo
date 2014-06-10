<?php

namespace Unit\Model\Analytics\Metrics;

use Analytics\Metrics\SwiftStatsdSubscriber;
use PHPUnit_Framework_TestCase;
use Symfony\Component\EventDispatcher\Event;

/**
 * Statsd Test Dummy
 *
 * I don't want phpunit's lack of ability to fluently set expectations on
 * an object to drive the design of the code, so I'm choosing to record calls
 * on a dummy implementation instead of set expectations via phpunit's api.
 */
class StatsdDummy
{
    public $calls = array();

    public function increment($key)
    {
        $this->calls["increment"][] = $key;
    }

    public function count($key, $value)
    {
        $this->calls["count"][] = array($key, $value);
    }

    public function timing($key, $value)
    {
        $this->calls["timing"][] = array($key, $value);
    }
}

class SwiftStatsdSubscriberTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->statsd = new StatsdDummy();

        $this->subscriber = new SwiftStatsdSubscriber($this->statsd, "%cachetype%,%metric%");

        $this->event = array(
            "method"       => "METHOD",
            "success"      => true,
            "error_number" => 0,
            "curl_info" => array(
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
            ),
        );
    }

    public function testCountsSuccess()
    {
        $this->subscriber->onHttp($this->event);
        $this->assertContains("swift.http.success", $this->statsd->calls["increment"]);
        $this->assertContains("swift.http.METHOD.success", $this->statsd->calls["increment"]);
    }

    public function testDoesntCountSuccessOnError()
    {
        $this->event["success"] = false;
        $this->subscriber->onHttp($this->event);
        $this->assertNotContains("swift.http.success", $this->statsd->calls["increment"]);
        $this->assertNotContains("swift.http.METHOD.success", $this->statsd->calls["increment"]);
    }

    public function testCountsError()
    {
        $this->event["error_number"] = 123;
        $this->subscriber->onHttp($this->event);
        $this->assertContains("swift.http.error.123", $this->statsd->calls["increment"]);
        $this->assertContains("swift.http.METHOD.error.123", $this->statsd->calls["increment"]);
    }

    public function testDoesntCountErrorOnSuccess()
    {
        $this->subscriber->onHttp($this->event);
        $this->assertNotContains("swift.http.error.123", $this->statsd->calls["increment"]);
        $this->assertNotContains("swift.http.METHOD.error.123", $this->statsd->calls["increment"]);
    }

    public function testCountsHttpCodes()
    {
        $this->event["curl_info"]["http_code"] = 200;
        $this->subscriber->onHttp($this->event);
        $this->assertContains("swift.http.METHOD.response_code.200", $this->statsd->calls["increment"]);
    }

    public function testRecordsTotalTime()
    {
        $this->event["curl_info"]["total_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.METHOD.total_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsNamelookupTime()
    {
        $this->event["curl_info"]["namelookup_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.METHOD.namelookup_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsConnectTime()
    {
        $this->event["curl_info"]["connect_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.METHOD.connect_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsPretransferTime()
    {
        $this->event["curl_info"]["pretransfer_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.METHOD.pretransfer_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsStarttransferTime()
    {
        $this->event["curl_info"]["starttransfer_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.METHOD.starttransfer_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsAggregateTotalTime()
    {
        $this->event["curl_info"]["total_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.total_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsAggregateNamelookupTime()
    {
        $this->event["curl_info"]["namelookup_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.namelookup_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsAggregateConnectTime()
    {
        $this->event["curl_info"]["connect_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.connect_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsAggregatePretransferTime()
    {
        $this->event["curl_info"]["pretransfer_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.pretransfer_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testRecordsAggregateStarttransferTime()
    {
        $this->event["curl_info"]["starttransfer_time"] = 1;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.starttransfer_time", 1000), $this->statsd->calls["timing"]);
    }

    public function testCountsDownloadSize()
    {
        $this->event["curl_info"]["size_download"] = 1000000;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.size_download", 1000000), $this->statsd->calls["count"]);
        $this->assertContains(array("swift.http.METHOD.size_download", 1000000), $this->statsd->calls["count"]);
    }

    public function testCountsUploadSize()
    {
        $this->event["curl_info"]["size_upload"] = 1000000;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.size_upload", 1000000), $this->statsd->calls["count"]);
        $this->assertContains(array("swift.http.METHOD.size_upload", 1000000), $this->statsd->calls["count"]);
    }

    public function testCountsDownloadSpeed()
    {
        $this->event["curl_info"]["speed_download"] = 1000000;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.speed_download", 1000000), $this->statsd->calls["count"]);
        $this->assertContains(array("swift.http.METHOD.speed_download", 1000000), $this->statsd->calls["count"]);
    }

    public function testCountsUploadSpeed()
    {
        $this->event["curl_info"]["speed_upload"] = 1000000;
        $this->subscriber->onHttp($this->event);
        $this->assertContains(array("swift.http.speed_upload", 1000000), $this->statsd->calls["count"]);
        $this->assertContains(array("swift.http.METHOD.speed_upload", 1000000), $this->statsd->calls["count"]);
    }
}
