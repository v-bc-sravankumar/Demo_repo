<?php

class Unit_Controllers_RemoteTest extends PHPUnit_Framework_TestCase
{
    public function testEventActionTriggersProvidedEventWithProvidedData()
    {
        $controller = new ISC_REMOTE();

        $name = 'Event.One';
        $data = array(
            'one' => 1,
            'two' => 2,
        );

        $mockEventClass = $this->getMockClass('\Store_Event', array('trigger', 'exists'));
        $mockEventClass::staticExpects($this->once())->method('trigger')->with($name, $data);
        $mockEventClass::staticExpects($this->once())->method('exists')->will($this->returnValue(true));

        $controller->setEventClass($mockEventClass);
        $get = array('w' => 'event');
        $request = $get;
        $body = array(
            'data' => $data,
            'name' => $name,
        );
        $body = json_encode($body);
        $request = new Interspire_Request($get, array(), $request, null, $body);

        $controller->eventAction($request);
    }
}
