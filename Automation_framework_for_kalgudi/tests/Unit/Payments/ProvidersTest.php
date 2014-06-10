<?php

namespace Unit\Payments;

use PHPUnit_Framework_TestCase;

use Payments\Providers;

class ProvidersTest extends PHPUnit_Framework_TestCase
{
    public function trueFalseDataProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider trueFalseDataProvider
     */
    public function testIsProviderInTestMode($isTestMode)
    {
        $mockQuery = $this->getMockBuilder('DataModel_SelectQuery')
            ->setMethods(array('whereEquals', 'getIterator'))
            ->getMock();

        $mockIterator = $this->getMockBuilder('Db_QueryIterator')
            ->setMethods(array('first'))
            ->disableOriginalConstructor()
            ->getMock();

        $mockQuery->expects($this->at(0))
            ->method('whereEquals')
            ->with($this->equalTo('modulename'), $this->equalTo('test-provider'));
        $mockQuery->expects($this->at(1))
            ->method('whereEquals')
            ->with($this->equalTo('variablename'), $this->equalTo('testmode'));
        $mockQuery->expects($this->at(2))
            ->method('getIterator')
            ->will($this->returnValue($mockIterator));

        $mockIterator->expects($this->at(0))
            ->method('first')
            ->will($this->returnValue(
                array('variableval' => $isTestMode ? 'YES' : 'NO')
            ));

        Providers::isProviderInTestMode('test-provider', $mockQuery);
    }
}
