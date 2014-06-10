<?php

class Unit_Lib_Store_Api_Version_2_Resource extends Interspire_IntegrationTest
{
	public function testGetVersion()
	{
		$resource = new Store_Api_Version_2_Resource_Time();
		$this->assertEquals(2, $resource->getVersion());
	}
}
