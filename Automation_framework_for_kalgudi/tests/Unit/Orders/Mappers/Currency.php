<?php
class Integration_Orders_Mappers_Currency extends PHPUnit_Framework_TestCase
{

	public function testCurrencyMapped() {

		$order1 = $this->getMock('\Orders\Order', array('getId'));
		$order1->expects($this->any())->method('getId')->will($this->returnValue(100));

		$order2 = $this->getMock('\Orders\Order', array('getId'));
		$order2->expects($this->any())->method('getId')->will($this->returnValue(200));

		$aud = new Store_Currency();
		$aud->setId(1);
		$aud->setCode('AUD');

		$usd = new Store_Currency();
		$usd->setId(2);
		$usd->setCode('USD');

		$currencyData = array(
			$order1->getId() => $aud,
			$order2->getId() => $usd,
		);

		$currencyMapper = $this->getMock('\Orders\Mappers\Currency', array('getData'));
		$currencyMapper->expects($this->any())->method('getData')->will($this->returnValue($currencyData));

		$mapper = new \DataModel\Mapper\Aggregate(array(
			$currencyMapper
		));
		$mapper->setObjects(array($order1, $order2));
		$mapper->decorate();

		$orders = $mapper->getObjects();

		$this->assertEquals($aud, $orders[0]->getCurrency());
		$this->assertEquals($usd, $orders[1]->getCurrency());

	}
}