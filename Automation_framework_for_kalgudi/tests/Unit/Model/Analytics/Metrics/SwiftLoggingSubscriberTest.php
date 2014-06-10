<?php

namespace Unit\Model\Analytics\Metrics;

use Analytics\Metrics\SwiftLoggingSubscriber;
use PHPUnit_Framework_TestCase;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\GenericEvent;

class SwiftLoggingSubscriberTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
                             ->getMockForAbstractClass();

        $this->subscriber = new SwiftLoggingSubscriber($this->logger);

        $this->payload = array(
            'method'          => 'METHOD',
            'success'         => true,
            'error_number'    => 0,
            'request_headers' => array(),
            'curl_info' => array(
                'http_code'          => 0,
                'total_time'         => 0,
                'namelookup_time'    => 0,
                'connect_time'       => 0,
                'pretransfer_time'   => 0,
                'starttransfer_time' => 0,
                'size_download'      => 0,
                'size_upload'        => 0,
                'speed_download'     => 0,
                'speed_upload'       => 0,
            ),
        );

        $this->event = new GenericEvent('', $this->payload);
    }

    public function testDebugsHttp()
    {
        $this->logger->expects($this->once())
                     ->method('debug')
                     ->with($this->anything(), $this->payload);

        $this->subscriber->onHttp($this->event);
    }

    public function testCriticalOnCurlError()
    {
        $this->payload['error_number'] = 123;
        $this->event = new GenericEvent('', $this->payload);

        $this->logger->expects($this->once())
                     ->method('critical')
                     ->with($this->anything(), $this->payload);

        $this->subscriber->onHttp($this->event);
    }

    public function testCriticalOnCurlNonSuccess()
    {
        $this->payload['success'] = false;
        $this->event = new GenericEvent('', $this->payload);

        $this->logger->expects($this->once())
                     ->method('critical')
                     ->with($this->anything(), $this->payload);

        $this->subscriber->onHttp($this->event);
    }

    public function testCriticalOnAuthorisationFailure()
    {
        $this->logger->expects($this->once())
                     ->method('critical')
                     ->with($this->anything(), $this->payload);

        $this->subscriber->onAuthorisationFailure($this->event);
    }

    public function testDebugScrubsAuthToken()
    {
        $expected = $this->payload;

        $this->payload['request_headers'][] = 'X-Auth-Token: FOO';
        $expected['request_headers'][]      = 'X-Auth-Token: redacted';

        $this->event = new GenericEvent('', $this->payload);

        $this->logger->expects($this->once())
                     ->method('debug')
                     ->with($this->anything(), $expected);

        $this->subscriber->onHttp($this->event);
    }

    public function testAuthFailureScrubsAuthToken()
    {
        $expected = $this->payload;

        $this->payload['request_headers'][] = 'X-Auth-Token: FOO';
        $expected['request_headers'][]      = 'X-Auth-Token: redacted';

        $this->event = new GenericEvent('', $this->payload);

        $this->logger->expects($this->once())
                     ->method('critical')
                     ->with($this->anything(), $expected);

        $this->subscriber->onAuthorisationFailure($this->event);
    }

    public function testNoticeOnAppend()
    {
        $payload = array(
            'modes' => array(
                'write'    => true,
                'truncate' => false,
                'seek'     => true,
            ),
        );

        $event = new GenericEvent('', $payload);

        $this->logger->expects($this->once())
                     ->method('notice')
                     ->with($this->anything(), $payload);

        $this->subscriber->onStreamOpenOpened($event);
    }

    public function testNoNoticeOnRegularWrites()
    {
        $payload = array(
            'modes' => array(
                'write'    => true,
                'truncate' => true,
                'seek'     => false,
            ),
        );

        $event = new GenericEvent('', $payload);

        $this->logger->expects($this->never())
                     ->method('notice');

        $this->subscriber->onStreamOpenOpened($event);
    }
}
