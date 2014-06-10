<?php

class Integration_Store_Api_Version_2_Resource_Analytics_Event extends PHPUnit_Framework_TestCase
{
    private function getApiRequest($data = null)
    {
        $server = array(
          'REQUEST_URI' => '/api/v2/analytics/event',
          'REQUEST_METHOD' => 'POST',
        );

        $body = null;
        if ($data !== null) {
          $body = json_encode($data);
          $server['CONTENT_TYPE'] = 'application/json';
        }

        return new Interspire_Request(null, null, null, $server, $body);
    }

    public function assertEventData($expected, $actual)
    {
        $this->assertInstanceOf('DateTime', Interspire_DateTime::parseIso8601Date($actual['event_when']));

        unset($actual['event_when']);

        $this->assertEquals($expected, $actual);
    }

    private function assertEventTriggered($eventName, $expectedData, $postData)
    {
        $self = $this;
        $eventHandler = function(Interspire_Event $event) use ($eventName, $expectedData, $self) {
            $self->assertEquals($eventName, $event->eventName);
            $data = $event->data;

            $self->assertEventData($expectedData, $event->data);

            return true;
        };

        $request = $this->getApiRequest($postData);

        $segmentIO = $this->getMock('BC\Analytics', array('handleEvent'));
        $segmentIO
            ->expects($this->once())
            ->method('handleEvent')
            ->with($this->callback($eventHandler));

        $resource = $this->getMock('Store_Api_Version_2_Resource_Analytics_Event', array('getSegmentIO'));
        $resource
            ->expects($this->once())
            ->method('getSegmentIO')
            ->will($this->returnValue($segmentIO));

        $result = $resource->postAction($request);

        $this->assertEquals($postData['event_name'], $result['event_name']);
        $this->assertEquals($postData['application'], $result['application']);
        $this->assertEventData($expectedData, $result['data']);
    }

    public function testPostTriggersEvent()
    {
        $eventName = uniqid('event.');

        $expectedData = array(
            'foo' => 'bar',
            'nested' => array(
                'abc' => 'def',
            ),
            'system' => 'Bigcommerce',
            'application' => 'my_application',
            'session_hash' => session_id(),
            'store_hash' => \Platform\Account::getInstance()->getStoreHash(),
            'variant_hash' => '',
        );

        $postData = array(
            'event_name' => $eventName,
            'application' => 'my_application',
            'data' => array(
                'foo' => 'bar',
                'nested' => array(
                    'abc' => 'def',
                ),
            ),
        );

        $this->assertEventTriggered($eventName, $expectedData, $postData);
    }

    public function testPostWithoutDataIsSuccessful()
    {
        $eventName = uniqid('event.');

        $expectedData = array(
            'system' => 'Bigcommerce',
            'application' => 'my_application',
            'session_hash' => session_id(),
            'store_hash' => \Platform\Account::getInstance()->getStoreHash(),
            'variant_hash' => '',
        );

        $postData = array(
            'event_name' => $eventName,
            'application' => 'my_application',
        );

        $this->assertEventTriggered($eventName, $expectedData, $postData);
    }

    /**
     * @expectedException Store_Api_Exception_Resource_MethodNotFound
     */
    public function testPostToEntityDisallowed()
    {
        $request = $this->getApiRequest();
        $request->setUserParam('event', 5);

        $resource = new Store_Api_Version_2_Resource_Analytics_Event();
        $result = $resource->postAction($request);
    }
}
