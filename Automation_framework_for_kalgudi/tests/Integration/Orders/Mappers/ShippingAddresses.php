<?php

class Integration_Orders_Mappers_ShippingAddresses extends PHPUnit_Framework_TestCase {

    private $createdOrders = array();
    private $createdOrderAddresses = array();
    private $createdOrderShippingIds = array();

    const MYSQL_SIGNED_SMALLINT_MAX = 32767;

    private function createOrder()
    {
        $order = new \Orders\Order();
		$order->setOrdcustid(0);
        $order->save();

        $this->createdOrders[] = $order;

        return $order;
    }

    private function createOrderAddress($orderId)
    {
        $orderAddress = new \Orders\Address();
        $orderAddress->setOrderId($orderId);
        $orderAddress->save();

        $this->createdOrderAddresses[] = $orderAddress;

        return $orderAddress;
    }

    private function createOrderShippingRecord($orderId, $orderAddressId)
    {
        $orderShippingId = $this->getRandomUnusedKeyForTable('order_shipping', 'id', array($orderId));
        $orderShippingData = array('id' => $orderShippingId, 'order_address_id' => $orderAddressId, 'order_id' => $orderId);

        $insertQuery = new \DataModel\InsertQuery('order_shipping', $orderShippingData);
        $insertQuery->execute();

        $this->createdOrderShippingIds[] = $orderShippingId;

        return $orderShippingId;
    }

    private function getRandomUnusedKeyForTable($table, $primaryKey = 'id', $exclude = array())
    {
        $min = 1;
        $query = new DataModel_SelectQuery("SELECT ${primaryKey} FROM [|PREFIX|]${table} ORDER BY ${primaryKey} DESC LIMIT 1");
        $iterator = $query->getIterator();

        if (!$iterator->isEmpty()) {
            $row = $iterator->first();
            $highestKey = $row[$primaryKey];
            $min = $highestKey + 1;
        }

        do {
            $id = rand($min, self::MYSQL_SIGNED_SMALLINT_MAX);
        } while (in_array($id, $exclude));
        return $id;
    }

    public function testGetDataAssignsCorrectIdToAddress()
    {
        $mapper = new Orders\Mappers\ShippingAddresses();

        $order = $this->createOrder();
        $orderAddress = $this->createOrderAddress($order->getId());
        $this->createOrderShippingRecord($order->getId(), $orderAddress->getId());

        $mapper->setObject($order);
        $data = $mapper->getData();

        $this->assertCount(1, $data);

        $addresses = reset($data);

        $this->assertCount(1, $addresses);

        $address = reset($addresses);

        $this->assertEquals($orderAddress->getId(), $address->getId());

    }

    public function tearDown()
    {

        foreach ($this->createdOrders as $order) {
            $order->delete();
        }

        foreach ($this->createdOrderAddresses as $orderAddress) {
            $orderAddress->delete();
        }

        foreach ($this->createdOrderShippingIds as $id) {
            $query = new \DataModel\DeleteQuery('order_shipping', array('id' => $id));
            $query->execute();
        }
    }
}