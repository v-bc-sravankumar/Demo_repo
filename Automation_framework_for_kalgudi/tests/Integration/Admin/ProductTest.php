<?php

namespace Integration\Admin;

class ProductTest extends \PHPUnit_Framework_TestCase
{
	public function testGetBackboneModelsProdReleaseDateFormat()
	{
		$product = array (
				'id' => 999,
				'preorder_release_date' => 'Mon, 22 Apr 2013 0:00:00 +0000',
		);

		$mockRepo = $this->getMock('\Repository\Products', array('findById','findVideos'));

		$mockRepo->expects($this->any())
			->method('findById')
			->will($this->returnValue($product));

		$mockRepo->expects($this->any())
			->method('findVideos')
			->will($this->returnValue(array()));

		$admin = $this->getMock('ISC_ADMIN_PRODUCT',
				array('getRepository'),
				array(),
				'Mock_Isc_Admin_Product',
				false);

		$admin->expects($this->any())
			->method('getRepository')
			->will($this->returnValue($mockRepo));

		$class = new \ReflectionClass("ISC_ADMIN_PRODUCT");
		$method = $class->getMethod("getBackboneModels");
		$method->setAccessible(true);

		$result = $method->invoke($admin, 999);

		$this->assertEquals($result['product']['preorder_release_date'], "04/22/2013");

	}
}
