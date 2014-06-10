<?php

class Integration_Orders_Mappers_Messages extends PHPUnit_Framework_TestCase
{

    protected $messageIds = array();

    public function messageFixture()
    {
        return array(
            array('messagefrom' => 'admin', 'subject' => 'Re: Order #100', 'message' => 'test1', 'datestamp' => 1361412122, 'messageorderid' => 100, 'messagestatus' => 'unread', 'staffuserid' => 1, 'isflagged' => 0),
            array('messagefrom' => 'customer', 'subject' => 'Re: Order #100', 'message' => 'test3', 'datestamp' => 1361412124, 'messageorderid' => 100, 'messagestatus' => 'read', 'staffuserid' => 0, 'isflagged' => 0),
            array('messagefrom' => 'customer', 'subject' => 'Re: Order #100', 'message' => 'test4', 'datestamp' => 1361412125, 'messageorderid' => 100, 'messagestatus' => 'unread', 'staffuserid' => 0, 'isflagged' => 0),
            array('messagefrom' => 'customer', 'subject' => 'Re: Order #100', 'message' => 'test5', 'datestamp' => 1361412126, 'messageorderid' => 100, 'messagestatus' => 'unread', 'staffuserid' => 0, 'isflagged' => 0),
        );
    }

    public function setUp()
    {
        foreach ($this->messageFixture() as $data) {
            $insert = new \DataModel\InsertQuery('order_messages', $data);
            $messageIds[] = $insert->execute();
        }
        $this->messageIds = $messageIds;
    }

    public function tearDown()
    {
        foreach ($this->messageIds as $id) {
            $delete = new \DataModel\DeleteQuery('order_messages', array(
                'messageid' => $id,
            ));
            $delete->execute();
        }
    }

    public function testUnreadMessagesAreMapped()
    {
        $order = $this->getMock('\Orders\Order', array('getId'));
        $order->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(100));

        $mapper = new \Orders\Mappers\Messages();
        $mapper->setObjects(array(
            $order,
        ));

        $data = $mapper->getData();
        $this->assertCount(2, $data[100]['incoming']['unread']);

    }

    public function testReadMessagesAreMapped()
    {
        $order = $this->getMock('\Orders\Order', array('getId'));
        $order->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(100));

        $mapper = new \Orders\Mappers\Messages();
        $mapper->setObjects(array(
            $order,
        ));

        $data = $mapper->getData();

        $this->assertCount(1, $data[100]['incoming']['read']);


    }

    public function testOutgoingMessagesAreMapped()
    {
        $order = $this->getMock('\Orders\Order', array('getId'));
        $order->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(100));

        $mapper = new \Orders\Mappers\Messages();
        $mapper->setObjects(array(
            $order,
        ));

        $data = $mapper->getData();

        $this->assertCount(1, $data[100]['outgoing']);
    }
}
