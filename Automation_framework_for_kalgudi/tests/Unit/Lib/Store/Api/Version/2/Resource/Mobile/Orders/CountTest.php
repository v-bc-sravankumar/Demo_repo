<?php

namespace Unit\Lib\Store\Api\Version\V2\Resource\Mobile\Orders;

use PHPUnit_Framework_TestCase;

use Interspire_Request;
use Store_Api_Version_2_Resource_Mobile_Orders_Count;

class CountTest extends PHPUnit_Framework_TestCase
{
    public function testGetStatuses()
    {
        $repository = $this->prepareRepository();

        $count = new TestableCount();
        $count->setRepository($repository);
        $statuses = $count->getStatuses();

        // Make sure the statuses are in order.
        $this->assertCount(4, $statuses);
        $this->assertEquals(array(
            (object) array('id' => 2, 'name' => 'test2'),
            (object) array('id' => 3, 'name' => 'test3'),
            (object) array('id' => 4, 'name' => 'test4'),
            (object) array('id' => 1, 'name' => 'test1'),
        ), $statuses);
    }

    private function prepareRepository()
    {
        $repository = $this
            ->getMockBuilder('Repository\Orders')
            ->setMethods(array('getStatuses'))
            ->getMock();

        // Return an unordered set of statuses.
        $repository
            ->expects($this->at(0))
            ->method('getStatuses')
            ->will($this->returnValue(array(
                array('order' => 5, 'id' => 1, 'name' => 'test1'),
                array('order' => 1, 'id' => 2, 'name' => 'test2'),
                array('order' => 2, 'id' => 3, 'name' => 'test3'),
                array('order' => 4, 'id' => 4, 'name' => 'test4'),
            )));

        return $repository;
    }

    public function providerGetAction()
    {
      return array(
        array(array('orddate > 1000')),
        array(null),
      );
    }

    /**
     * @dataProvider providerGetAction
     */
    public function testGetAction($conditions)
    {
        $orders = $this
            ->getMockBuilder('Store_Api_Version_2_Resource_Orders')
            ->disableOriginalConstructor()
            ->setMethods(array('getConditionsForGetAction'))
            ->getMock();

        $orders
          ->expects($this->any())
          ->method('getConditionsForGetAction')
          ->will($this->returnValue($conditions));

        $db = $this
            ->getMockBuilder('Db_Base')
            ->setMethods(array('Query', 'Fetch'))
            ->getMock();

        if (!empty($conditions)) {
          $contains = 'deleted=0 AND ' . implode(' AND ', $conditions);
        } else {
          $contains = 'deleted=0 GROUP';
        }

        $db
            ->expects($this->at(0))
            ->method('Query')
            ->with($this->stringContains($contains));

        $db
            ->expects($this->at(1))
            ->method('Fetch')
            ->will($this->returnValue(array('ordstatus' => 1, 'orders' => 10)));

        $db
            ->expects($this->at(2))
            ->method('Fetch')
            ->will($this->returnValue(array('ordstatus' => 2, 'orders' => 20)));

        $db
            ->expects($this->at(3))
            ->method('Fetch')
            ->will($this->returnValue(array('ordstatus' => 3, 'orders' => 30)));

        $db
            ->expects($this->at(4))
            ->method('Fetch')
            ->will($this->returnValue(array('ordstatus' => 4, 'orders' => 40)));

        $db
            ->expects($this->at(5))
            ->method('Fetch')
            ->will($this->returnValue(array('ordstatus' => 'orders', 'orders' => 50)));

        $repository = $this->prepareRepository();

        $count = new TestableCount();
        $count->setDb($db);
        $count->setRepository($repository);
        $count->setOrders($orders);
        $output = $count->getAction(new Interspire_Request());

        $this->assertEquals(array(
            'statuses' => array(
                (object) array('name' => 'test2', 'id' => 2, 'count' => 20),
                (object) array('name' => 'test3', 'id' => 3, 'count' => 30),
                (object) array('name' => 'test4', 'id' => 4, 'count' => 40),
                (object) array('name' => 'test1', 'id' => 1, 'count' => 10),
            ),
            'orders' => 50,
        ), $output->getData());
    }
}

class TestableCount extends Store_Api_Version_2_Resource_Mobile_Orders_Count
{
    private $db = null;

    public function __construct()
    {
        // Do not call parent constructor.
    }

    public function setDb($db)
    {
        $this->db = $db;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function getData()
    {
        return $this->_data;
    }
}

