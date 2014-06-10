<?php

namespace Unit\Shipping;

use Shipping\ConnectionSpecification;

class ConnectionSpecificationTest extends \PHPUnit_Framework_TestCase
{
    public function testIsSatisfiedByHandlesSchemaWithoutRequiredField()
    {
        $provider = $this->getProviderMock();
        $provider->expects($this->once())->method('getSettingsSchema')->will($this->returnValue(array()));
        $specification = new ConnectionSpecification($provider);

        $this->assertTrue($specification->isSatisfiedBy(array()));
    }

    public function testIsSatisfiedByVerifiesValidSettings()
    {
        $provider = $this->getProviderMock();
        $specification = new ConnectionSpecification($provider);

        $provider->expects($this->once())->method('getSettingsSchema')->will($this->returnValue(array(
            'key' => array(
                'required' => true,
            ),
        )));

        $this->assertTrue($specification->isSatisfiedBy(array(
            'key' => 'value',
        )));
    }

    public function testIsSatisfiedByVerifiesIncompleteSettings()
    {
        $provider = $this->getProviderMock();
        $specification = new ConnectionSpecification($provider);

        $provider->expects($this->once())->method('getSettingsSchema')->will($this->returnValue(array(
            'key' => array(
                'required' => true,
            ),
        )));

        $this->assertFalse($specification->isSatisfiedBy(array()));
    }

    private function getProviderMock()
    {
        $provider = $this->getMockBuilder('\\Shipping\\Provider')
            ->disableOriginalConstructor()
            ->getMock();
        return $provider;
    }
}