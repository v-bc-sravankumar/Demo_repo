<?php

namespace Integration\Controllers;

use Shipping\Zone;
use Interspire_Response as Response;

class ShippingZoneControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ShippingZoneController
     */
    protected $controller;

    protected $validZoneData = array(
        'name' => 'Free Country',
        'type' => Zone::TYPE_COUNTRY,
        'locations' => array(
            array(
                'locationid' => '1',
                'zoneid' => '1',
                'locationtype' => 'country',
                'locationvalueid' => '1',
                'locationvalue' => 'Free Country',
                'locationcountryid' => null,
            ),
        ),
    );

    public function setUp()
    {
        $this->controller = new \ShippingZoneController();
    }

    public function testCanUpdateDefaultZone()
    {
        $zone = Zone::createFromArray(array(
            'zonename' => 'Test Zone',
            'zonetype' => Zone::TYPE_COUNTRY,
            'zonedefault' => 1,
            'zonehandlingfee' => 1,
            'zonehandlingtype' => 'none'
        ));
        $zone->save();

        $updatedHandlingFee = 2;
        $updatedHandlingType = 'global';

        $request = $this->createMockRequest(array(
            'type' => Zone::TYPE_COUNTRY,
            'handling_fee' => $updatedHandlingFee,
            'zonehandlingtype' => $updatedHandlingType,
        ));

        $response = $this->getMock('\\Interspire_Response');
        $response->expects($this->once())->method('setStatus')->with(Response::STATUS_NO_CONTENT);

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $this->controller->putAction(array(
            'id' => $zone->getId(),
        ));

        $iterator = Zone::find($zone->getId());

        $this->assertCount(1, $iterator);

        $updatedZone = $iterator->first();

        $this->assertEquals($updatedHandlingFee, $updatedZone->getHandlingFee());
        $this->assertEquals($updatedHandlingType, $updatedZone->getHandlingType());

        $zone->delete();
    }

    public function testPutActionProduces404ResponseCodeWhenNoZoneFound()
    {
        // set up
        $request = $this->getMock('\\Interspire_Request');
        $response = $this->getMock('\\Interspire_Response');

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));
        $response->expects($this->once())->method('setStatus')->with(404);

        // exercise
        $this->controller->putAction(array('id' => 9999));
    }

    public function testPostActionCreatesNewZone()
    {
        $zoneData = $this->validZoneData;

        $request = $this->createMockRequest($zoneData);
        $response = $this->getMock('\\Interspire_Response');
        $response->expects($this->once())->method('setStatus')->with(201);

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $viewBag = $this->controller->postAction();

        // verify
        $iterator = Zone::find($viewBag['zoneid']);
        $this->assertCount(1, $iterator);

        $newZone = $iterator->first();
        $this->assertEquals($zoneData['name'], $newZone->getName());
        $this->assertEquals($zoneData['type'], $newZone->getType());

        // tear down
        $newZone->delete();
    }

    public function testPostActionIgnoresFieldsNotPartOfModel()
    {
        $zoneData = $this->validZoneData;
        $zoneData['area'] = 35;

        $request = $this->createMockRequest($zoneData);
        $response = $this->getMock('\\Interspire_Response');
        $response->expects($this->once())->method('setStatus')->with(201);

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $viewBag = $this->controller->postAction();

        // verify
        $iterator = Zone::find($viewBag['zoneid']);
        $this->assertCount(1, $iterator);


        // tear down
        $iterator->first()->delete();
    }

    public function testPutActionUpdatesZone()
    {
        $zone = Zone::createFromArray(array(
            'zonetype' => Zone::TYPE_COUNTRY,
        ));
        $zone->save();

        $zoneData = $this->validZoneData;

        $request = $this->createMockRequest($zoneData);
        $response = $this->getMock('\\Interspire_Response');
        $response->expects($this->once())->method('setStatus')->with(204);

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $this->controller->putAction(array('id' => $zone->getId()));

        // verify
        $iterator = Zone::find($zone->getId());
        $this->assertCount(1, $iterator);

        $updatedZone = $iterator->first();
        $this->assertEquals($zoneData['name'], $updatedZone->getName());
        $this->assertEquals($zoneData['type'], $updatedZone->getType());

        // tear down
        $zone->delete();
    }

    public function testZoneDeletionThrows404ForNonexistentZone()
    {
        // Setup mocks.
        $request = $this->getMock('\\Interspire_Request');
        $response = $this->getMock('\\Interspire_Response');

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        // Setup expectations.
        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));
        $response->expects($this->once())->method('setStatus')->with(404);

        // Exercise.
        $this->controller->deleteAction(array('id' => 9999));
    }

    public function testPostActionProduces400ResponseCodeWhenNoLocationsProvided()
    {
        $zoneData = array(
            'name' => 'Free Country',
        );

        $request = $this->createMockRequest($zoneData);
        $response = $this->getMock('\\Interspire_Response');
        $response->expects($this->once())->method('setStatus')->with(400);

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $this->controller->postAction();
    }

    public function testZoneDeletionWorks()
    {
        // Setup data.
        $zone = Zone::createFromArray(array(
            'zonename' => 'Foo',
            'zonetype' => Zone::TYPE_COUNTRY,
        ));
        $zone->save();

        // Setup mocks/expectations.
        $request = $this->getMock('\\Interspire_Request');
        $response = $this->getMock('\\Interspire_Response');
        $response->expects($this->once())->method('setStatus')->with(204);

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        // Exercise.
        $this->controller->deleteAction(array('id' => $zone->getId()));

        // Verify.
        $iterator = Zone::find($zone->getId());
        $this->assertEquals(0, iterator_count($iterator));
    }

    public function testCannotDeleteLastZone()
    {
        // Setup data.
        $this->assertEquals(1, Zone::find()->count());
        $zone = Zone::find()->first();

        // Setup mocks/expectations.
        $request = $this->getMock('\\Interspire_Request');
        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));
        $response = $this->getMock('\\Interspire_Response');
        $response->expects($this->once())->method('setStatus')->with(400);

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        // Exercise.
        $this->controller->deleteAction(array('id' => $zone->getId()));
    }

    protected function createMockRequest($data)
    {
        $request = $this->getMock('\\Interspire_Request');
        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));
        $request->expects($this->once())->method('getBody')->will($this->returnValue(json_encode($data)));

        return $request;
    }
}

