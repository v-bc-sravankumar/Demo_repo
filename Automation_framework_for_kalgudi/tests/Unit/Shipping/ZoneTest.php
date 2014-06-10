<?php

namespace Unit\Shipping;

use Shipping\Zone;
use Shipping\ZoneLocation;

class ZoneTest extends \PHPUnit_Framework_TestCase
{

    private $validZoneData = array(
        'zonename' => 'Test Zone',
        'zonetype' => Zone::TYPE_COUNTRY,
    );

    public function testGetLocationsEqualsLocationsSet()
    {
        $zoneData = array(
            'zonename' => 'Two Countries',
            'zonetype' => 'country',
        );
        $zone = new Zone($zoneData);

        $location1 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_COUNTRY,
            'locationvalueid' => 1,
            'locationvalue' => 'Afghanistan',
        ));

        $location2 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_COUNTRY,
            'locationvalueid' => 2,
            'locationvalue' => 'Albania',
        ));

        $locations = array($location1, $location2);

        $zone->setLocations($locations);

        $this->assertEquals($locations, iterator_to_array($zone->getLocations()));
    }

    public function testSetDataSetsData()
    {
        $validData = array(
            'zoneid' => null,
            'zonename' => 'Test Zone',
            'zonetype' => Zone::TYPE_COUNTRY,
            'zonefreeshipping' => 0,
            'zonefreeshippingtotal' => 0,
            'zonefreeshippingexcludefixed' => 0,
            'zonehandlingtype' => null,
            'zonehandlingfee' => 0,
            'zonehandlingseparate' => 1,
            'zoneenabled' => 1,
            'zonevendorid' => null,
            'zonedefault' => 0,
        );

        $zone = new Zone();
        $zone->setData($validData);
        $this->assertEquals($validData, $zone->toArray(false, false));
    }

    public function testValidateAcceptsValidData()
    {
        $zone = new Zone();
        $zone->setData($this->validZoneData);

        $location = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_COUNTRY,
            'locationvalue' => 'Albania',
        ));
        $zone->setLocations(array($location));

        $this->assertEmpty($zone->validate());
    }

    public function testValidateDetectsEmptyName()
    {

        $zone = new Zone();
        $zone->setType(Zone::TYPE_COUNTRY);
        $location = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_COUNTRY,
            'locationvalue' => 'Albania',
        ));
        $zone->setLocations(array($location));

        $errors = $zone->validate();
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('name:required', $errors);
    }

    public function testValidateDetectsEmptyLocations()
    {
        $zone = new Zone();
        $zone->setData($this->validZoneData);

        $errors = $zone->validate();
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('locations:required', $errors);
    }

}
