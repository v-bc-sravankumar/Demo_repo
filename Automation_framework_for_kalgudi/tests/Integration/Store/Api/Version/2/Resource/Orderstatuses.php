<?php

class Unit_Lib_Store_Api_Version_2_Resource_Orderstatuses extends Interspire_IntegrationTest
{
	public function testGetStatuses()
	{
		$resource = new Store_Api_Version_2_Resource_Orderstatuses();
		$request = new Interspire_Request();
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$firstStatus = current($data);
		$this->assertEquals($firstStatus['id'], ORDER_STATUS_INCOMPLETE);

		$lastStatus = end($data);
		$this->assertEquals($lastStatus['id'], ORDER_STATUS_HELD_REVIEW);
	}

	public function testGetStatus()
	{
		$resource = new Store_Api_Version_2_Resource_Orderstatuses();
		$request = new Interspire_Request();
		$request->setUserParam('orderstatuses', ORDER_STATUS_COMPLETED);
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData(true);
		$this->assertArrayIsNotEmpty($data);

		$this->assertEquals($data['id'], ORDER_STATUS_COMPLETED);
		$this->assertEquals($data['name'], 'Completed');
	}
}
