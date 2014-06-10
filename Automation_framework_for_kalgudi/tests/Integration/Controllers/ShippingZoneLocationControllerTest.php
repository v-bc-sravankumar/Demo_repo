<?php

namespace Integration\Controllers;

use Shipping\Zone;
use Shipping\ZoneLocation;

class ShippingZoneLocationControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ShippingZoneLocationController
     */
    protected $controller;

    public function setUp()
    {
        $this->controller = new \ShippingZoneLocationController();
    }

    public function testPutRequestToLocationsActionReplacesLocationsWithRequestLocations()
    {
        // set up
        $zone = $this->createZone();

        $location = ZoneLocation::createFromArray(array(
            'zoneid' => $zone->getId(),
            'locationtype' => ZoneLocation::TYPE_COUNTRY,
            'locationvalueid' => 1,
            'locationvalue' => 'Afghanistan',
        ));
        $location->save();

        $locations = array($location);
        $zone->setLocations($locations);

        $request = $this->getMock('\\Interspire_Request');
        $response = $this->getMock('\\Interspire_Response');

        $this->controller->setRequest($request);
        $this->controller->setResponse($response);

        $newLocationValueId = 2;
        $locationData = array(
            array(
                'zoneid' => $zone->getId(),
                'locationtype' => \Shipping\ZoneLocation::TYPE_COUNTRY,
                'locationvalueid' => $newLocationValueId,
                'locationvalue' => 'Albania',
            ),
        );

        $request->expects($this->any())->method('getMethod')->will($this->returnValue('PUT'));
        $request->expects($this->any())->method('param')->with('zone')->will($this->returnValue($zone->getId()));
        $request->expects($this->any())->method('getBody')->will($this->returnValue(json_encode($locationData)));
        $request->expects($this->any())->method('getAcceptMediaTypes')->will($this->returnValue(array('application/json')));

        // exercise
        $this->controller->locationsAction();

        // verify
        $iterator = ZoneLocation::find('zoneid=' . $zone->getId());
        $this->assertCount(1, $iterator);
        $newLocation = $iterator->first();
        $this->assertEquals($newLocationValueId, $newLocation->getValueId());

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


} 