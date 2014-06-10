<?php

namespace Integration\Controllers;

use Shipping\Zone;
use Shipping\Method;

class ShippingZoneMethodControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ShippingZoneMethodController
     */
    protected $controller;

    public function setUp()
    {
        $this->controller = new \ShippingZoneMethodController();
    }

    public function testPostActionProduces404ResponseCodeWhenNoZoneFound()
    {
        // set up
        $request = $this->getMock('\\Interspire_Request');
        $response = $this->getMock('\\Interspire_Response');

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));
        $response->expects($this->once())->method('setStatus')->with(404);

        // exercise
        $this->controller->postAction(array('zone' => null));
    }

    public function testPostActionCreatesNewMethod()
    {
        // set up
        $zone = $this->createZone();

        $request = $this->getMock('\\Interspire_Request');
        $response = $this->getMock('\\Interspire_Response');

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $methodData = array(
            'methodname' => 'New Method',
            'fixed_type' => 'peritem'
        );

        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));
        $request->expects($this->once())->method('getBody')->will($this->returnValue(json_encode($methodData)));
        $response->expects($this->once())->method('setStatus')->with(201);

        $this->controller->postAction(array('zone' => $zone->getId()));

        // verify
        $iterator = Method::find('zoneid=' . $zone->getId());
        $this->assertCount(1, $iterator);

        $newMethod = $iterator->first();
        $this->assertEquals($methodData['methodname'], $newMethod->getMethodName());

        // tear down
        $zone->delete();
    }


    public function testPostActionProduces409ResponseCodeWhenDuplicateNameFound()
    {
        // set up

        $zone = $this->createZone('A Zone');

        $methodName = 'A Method';

        $existingMethod = new Method();
        $existingMethod->setMethodName($methodName);
        $existingMethod->setZoneId($zone->getId());
        $existingMethod->save();

        $newMethodData = array(
            'methodname' => $methodName,
            'fixed_type' => 'peritem'
        );

        $request = $this->getMock('\\Interspire_Request');
        $response = $this->getMock('\\Interspire_Response');

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));
        $request->expects($this->once())->method('getBody')->will($this->returnValue(json_encode($newMethodData)));
        $response->expects($this->once())->method('setStatus')->with(409);


        $this->controller->postAction(array(
            'zone' => $zone->getId(),
        ));

        // verify
        $iterator = Method::find('zoneid=' . $zone->getId());
        $this->assertCount(1, $iterator);

        // tear down
        $zone->delete();
    }

    public function testPutActionProduces409ResponseCodeWhenDuplicateNameFound()
    {
        // set up

        $zone = $this->createZone('A Zone');

        $existingMethodName1 = 'A Method';
        $existingMethodName2 = 'Another Method';

        $existingMethod1 = $this->createMethod($existingMethodName1, $zone->getId());

        $existingMethod2 = $this->createMethod($existingMethodName2, $zone->getId());

        $newMethodData = array(
            'methodname' => $existingMethodName1,
            'fixed_type' => 'peritem'
        );

        $request = $this->getMock('\\Interspire_Request');
        $response = $this->getMock('\\Interspire_Response');

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));
        $request->expects($this->once())->method('getBody')->will($this->returnValue(json_encode($newMethodData)));
        $response->expects($this->once())->method('setStatus')->with(409);

        $this->controller->putAction(array(
            'zone' => $zone->getId(),
            'id' => $existingMethod2->getId(),
        ));

        // verify
        $updatedMethod = Method::find($existingMethod2->getId())->first();
        $this->assertEquals($existingMethodName2, $updatedMethod->getName());

        // tear down
        $zone->delete();
    }

    /**
     * @return Zone
     */
    protected function createZone($name = 'New Zone', $locations = array())
    {
        $zone = new Zone();
        $zone
            ->setName($name)
            ->setType('country')
            ->setLocations($locations);
        $result = $zone->save();

        return $zone;
    }

    /**
     * @return Method
     */
    protected function createMethod($name = 'New Method', $zoneId = null)
    {
        $method = new Method();
        $method->setMethodName($name);
        $method->setZoneId($zoneId);
        $method->save();

        return $method;
    }

}
