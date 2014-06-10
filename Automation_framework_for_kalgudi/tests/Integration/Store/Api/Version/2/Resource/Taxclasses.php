<?php

class Unit_Lib_Store_Api_Version_2_Resource_Taxclasses extends Interspire_IntegrationTest
{
	public function testGetTaxClasses()
	{
		$resource = new Store_Api_Version_2_Resource_Taxclasses();
		$request = new Interspire_Request();
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData();
		$this->assertArrayIsNotEmpty($data);

		$firstStatus = current($data);
		$this->assertEquals($firstStatus['name'], 'Non-Taxable Products');

		$lastStatus = end($data);
		$this->assertEquals($lastStatus['name'], 'Gift Wrapping');
	}

	public function testGetTaxClass()
	{
		$resource = new Store_Api_Version_2_Resource_Taxclasses();
		$request = new Interspire_Request();
		$request->setUserParam($resource->getPluralName(), 1);
		$wrapper = $resource->getAction($request);

		$data = $wrapper->getData(true);
		$this->assertArrayIsNotEmpty($data);

		$this->assertEquals($data['id'], 1);
		$this->assertEquals($data['name'], 'Non-Taxable Products');
	}
}
