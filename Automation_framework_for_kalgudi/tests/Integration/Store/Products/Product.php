<?php

class Integration_Store_Products_ProductTest extends Interspire_IntegrationTest
{
	public function testRedirectToCategory()
	{
		$product = $this->getMock('ISC_PRODUCT', array('isRedirectedToCategory', 'isHidden'));
		$request = $this->getMock('Interspire_Request', array('getResponse'));
		$response = $this->getMock('Interspire_Response', array('redirect'));

		$product->_prodid = 999;
		$product->_product['prodcatids'] = 1;

		$urlGenerator = new Store_UrlGenerator_Category();
		$url = $urlGenerator->getStoreFrontUrlFromId(1);

		$product->expects($this->any())
		->method('isRedirectedToCategory')
		->will($this->returnValue(true));

		$product->expects($this->any())
		->method('isHidden')
		->will($this->returnValue(false));

		//product should redirect to the category page
		$response->expects($this->once())
		->method('redirect')
		->with($this->equalTo($url, Interspire_Response::STATUS_TEMPORARY_REDIRECT))
		->will($this->returnValue(true));

		$request->expects($this->any())
		->method('getResponse')
		->will($this->returnValue($response));

		$product->HandlePage($request);
	}
}
