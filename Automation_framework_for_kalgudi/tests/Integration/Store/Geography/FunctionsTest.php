<?php

namespace Integration\Store\Geography;

use Store\Geography\Functions;
use Store_Country as Country;
use Store_Country_States as States;

class FunctionsTest extends \Interspire_IntegrationTest
{
    public function testGetStateNamesByCountryIdsReturnsStateNamesGroupedByCountryId()
    {
        $australia = Country::findByCountryName('Australia')->current();
        $id = $australia->getId();
        $australianStates = States::findByCountryId($id);
        $stateIds = Functions::getStateNamesByCountryIds(array($id));
        $this->assertArrayHasKey($id, $stateIds);
        $this->assertCount($australianStates->count(), $stateIds[$id]);
    }

    public function testGetStateNamesByCountryIdsWithEmptyListReturnsEmptyList()
    {
        $states = Functions::getStateNamesByCountryIds(array());
        $this->assertEmpty($states);
    }

    public function testGetCountryNamesByCountryIdsReturnsCountryNamesIndexedByCountryIds()
    {
        $expected = array(
            13 => 'Australia',
            225 => 'United Kingdom',
        );

        $countries = Functions::getCountryNamesByCountryIds(array_keys($expected));
        $this->assertEquals($expected, $countries);
    }

    public function testGetCountryNamesByCountryIdsWithEmptyListReturnsEmptyList()
    {
        $countries = Functions::getCountryNamesByCountryIds(array());
        $this->assertEmpty($countries);
    }
}