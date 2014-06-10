<?php

namespace Integration\Shipping;

use Shipping\Method;
use Shipping\Zone;

class MethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Zone
     */
    private $zone;

    public function setUp()
    {
        $this->zone = Zone::createFromArray(array(
            'zonename' => uniqid('Test Zone '),
        ));
        $this->zone->save();
    }

    public function tearDown()
    {
        $this->zone->delete();
    }

    public function testKnownArrayOptionsAreRestoredAsArrays()
    {
        $methodData = array(
            'methodname' => uniqid('Test '),
            'methodmodule' => 'shipping_fedex',
        );

        $options = array(
            'service' => array('PRIORITY_OVERNIGHT', 'STANDARD_OVERNIGHT'),
            'delivery_services' => array('PRIORITY_OVERNIGHT', 'STANDARD_OVERNIGHT'),
        );

        $method = new Method();
        $method->setData($methodData);
        $method->setZoneId($this->zone->getId());

        foreach ($options as $name => $value) {
            $method->addOption($name, $value);
        }

        $method->save();

        $restoredMethod = Method::find($method->getId())->first()->toArray();

        $this->assertEquals($options, $restoredMethod['options']);

        $method->delete();
    }

    public function testMultipleInstancesOfUnknownArrayOptionsAreRestoredAsScalarValues()
    {
        $methodData = array(
            'methodname' => uniqid('Test '),
            'methodmodule' => 'shipping_fedex',
        );

        $options = array(
            'unknown' => array('PRIORITY_OVERNIGHT', 'STANDARD_OVERNIGHT'),
        );

        $method = new Method();
        $method->setData($methodData);
        $method->setZoneId($this->zone->getId());
        foreach ($options as $name => $value) {
            $method->addOption($name, $value);
        }
        $method->save();

        $restoredMethod = Method::find($method->getId())->first()->toArray();

        $this->assertInternalType('string', $restoredMethod['options']['unknown']);

        $method->delete();
    }
} 