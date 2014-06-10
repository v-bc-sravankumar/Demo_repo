<?php

class Unit_Lib_BigCommerce_AccountTest extends PHPUnit_Framework_TestCase
{
    protected $oldDb;

    public function testTransferUsedWithLimit()
    {
        // HACK: I really don't want to do this.
        $this->stubGlobalsDBToReturn(array('storage' => 11, 'transfers' => 20));

        $configMock = $this->getMock('Store_Settings');
        $configMock->expects($this->any())
                   ->method('get')
                   ->with('TransfersLimit')
                   ->will($this->returnValue(200));

        $account = new BigCommerce_Account($configMock);
        $this->assertEquals(
            10, // percent
            $account->getTransfersUsedPercent()
        );
    }

    public function testTransferUsedWithNoLimit()
    {
        $configMock = $this->getMock('Store_Settings');
        $configMock->expects($this->any())
                   ->method('get')
                   ->with($this->equalTo('TransfersLimit'))
                   ->will($this->returnValue(0));

        $account = new BigCommerce_Account($configMock);
        $this->assertEquals(
            0, // percent
            $account->getTransfersUsedPercent()
        );
    }

    // Test Helpers

    protected function setUp()
    {
        if (isset($GLOBALS['ISC_CLASS_DB'])) {
            $this->oldDb = $GLOBALS['ISC_CLASS_DB'];
        }
    }

    protected function tearDown()
    {
        $GLOBALS['ISC_CLASS_DB'] = $this->oldDb;
    }

    protected function stubGlobalsDBToReturn($returnData)
    {
        $db     = $this->getMock('ISC_CLASS_DB', array('Query', 'Fetch'));
        $result = $this->getMock('stdClass');

        $db->expects($this->once())
           ->method('Query')
           ->will($this->returnValue($result));
        $db->expects($this->once())
           ->method('Fetch')
           ->will($this->returnValue($returnData));

        $GLOBALS['ISC_CLASS_DB'] = $db;
    }
}
