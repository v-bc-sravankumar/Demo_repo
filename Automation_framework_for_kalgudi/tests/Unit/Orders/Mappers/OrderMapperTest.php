<?php

use Orders\Mappers\OrderMapper;

class Unit_Orders_Mappers_OrderMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMapToDbColumnNameReturnsDbColumnName()
    {
        $mapper = new OrderMapper();
        $ordersResource = $this->getMock('\Store_Api_Version_2_Resource_Orders');
        $dbColumnName = 'ordcustid';
        $apiFieldName = 'customer_id';
        $fields = array(
            $apiFieldName => array(
                'type' => 'int',
                'db_field' => array('orders' => $dbColumnName),
            ),
        );
        $ordersResource->expects($this->once())->method('getFields')->will($this->returnValue($fields));
        $mapper->setOrdersResource($ordersResource);

        $actual = $mapper->mapToDbColumnName($apiFieldName);

        $this->assertEquals($dbColumnName, $actual);
    }
}