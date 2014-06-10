<?php

namespace Unit\Shipping;

use Shipping\ZoneLocation;

class ZoneLocationTest extends \PHPUnit_Framework_TestCase
{
    protected $validData = array(
        'locationid' => '1',
        'zoneid' => '1',
        'locationtype' => 'country',
        'locationvalueid' => '1',
        'locationvalue' => 'Free Country',
        'locationcountryid' => null,
    );

    public function testCreateFromCountry()
    {
        $countryId = 9999;
        $countryName = 'Free Country';

        $country = new \Store_Country(array(
            'countryid' => $countryId,
            'countrycouregid' => null,
            'countryname' => $countryName,
            'countryiso2' => null,
            'countryiso3' => null,
            'countrycurrencycode' => null,
            'countrycurrencysymbol' => null,
            'countrycurrencyname' => null,
        ));

        $location = ZoneLocation::createFromCountry($country);

        $this->assertEquals('country', $location->getType());
        $this->assertEquals($countryName, $location->getValue());
        $this->assertEquals($countryId, $location->getValueId());
    }

    public function testCreateFromArrayCallsSetters()
    {
        $location = ZoneLocation::createFromArray($this->validData);

        $this->assertEquals($this->validData['locationid'], $location->getId());
        $this->assertEquals($this->validData['zoneid'], $location->getZoneId());
        $this->assertEquals($this->validData['locationtype'], $location->getType());
        $this->assertEquals($this->validData['locationvalueid'], $location->getValueId());
        $this->assertEquals($this->validData['locationvalue'], $location->getValue());
        $this->assertEquals($this->validData['locationcountryid'], $location->getCountryId());
    }

    public function testSetZoneIdCastsNumericStrings()
    {
        $location = new ZoneLocation();
        $location->setZoneId('1');
        $this->assertInternalType('int', $location->getZoneId());
    }

    public function testCreateFromArrayConvertsValueIdNumericStringToInt()
    {
        $location = ZoneLocation::createFromArray(array('valueid' => '1'));
        $this->assertInternalType('int', $location->getValueId());
    }

    public function testCreateFromArrayConvertsSetCountryIdNumericStringToInt()
    {
        $location = ZoneLocation::createFromArray(array('countryid' => '1'));
        $this->assertInternalType('int', $location->getCountryId());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCreateFromArrayDetectsInvalidType()
    {
        ZoneLocation::createFromArray(array('locationtype' => 'madeUpType'));
    }

    public function testIsColocatedWithDetectsColocatedZipLocations()
    {
        $location1 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_ZIP,
            'locationvalue' => '2000',
            'locationcountryid' => 13,
        ));

        $location2 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_ZIP,
            'locationvalue' => '2000',
            'locationcountryid' => 13,
        ));

        $location3 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_ZIP,
            'locationvalue' => '2066',
            'locationcountryid' => 13,
        ));

        $this->assertTrue($location1->isColocatedWith($location2));
        $this->assertFalse($location1->isColocatedWith($location3));
    }

    public function testIsColocatedWithDetectsColocatedStateLocations()
    {
        $location1 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_STATE,
            'locationvalue' => 'New South Wales',
            'locationvalueid' => 209,
            'locationcountryid' => 13,
        ));

        $location2 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_STATE,
            'locationvalue' => 'New South Wales',
            'locationvalueid' => 209,
            'locationcountryid' => 13,
        ));

        $location3 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_STATE,
            'locationvalue' => 'Australian Capital Territory',
            'locationvalueid' => 208,
            'locationcountryid' => 13,
        ));

        $this->assertTrue($location1->isColocatedWith($location2));
        $this->assertFalse($location1->isColocatedWith($location3));
    }

    public function testIsColocatedWithDetectsColocatedCountryLocations()
    {
        $location1 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_COUNTRY,
            'locationvalue' => 'Australia',
            'locationvalueid' => 13,
        ));

        $location2 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_COUNTRY,
            'locationvalue' => 'Australia',
            'locationvalueid' => 13,
        ));

        $location3 = ZoneLocation::createFromArray(array(
            'locationtype' => ZoneLocation::TYPE_COUNTRY,
            'locationvalue' => 'Afghanistan',
            'locationvalueid' => 1,
        ));

        $this->assertTrue($location1->isColocatedWith($location2));
        $this->assertFalse($location1->isColocatedWith($location3));
    }
}
