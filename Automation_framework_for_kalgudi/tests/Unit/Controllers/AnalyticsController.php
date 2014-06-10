<?php
use Store\Controllers;

class Unit_Controllers_AnalyticsControllerTest extends PHPUnit_Framework_TestCase
{
	public function testDefaultTrendsActionSorter()
	{
		$enabled = \Store_Feature::isEnabled('StoreTrends');
		\Store_Feature::override('StoreTrends', true);
		$controller = new AnalyticsController();
		$requestMock = new Interspire_Request(array(), array(), array(),  array(), array());
		$controller->setRequest($requestMock);

		$this->assertEquals($controller->getTrendsPeriod(), \Analytics\Metrics::DAILY);

		\Store_Feature::override('StoreTrends', $enabled);
	}

	public function testNonDefaultTrendsActionSorter()
	{
		Store_Config::override('ShopPath', 'http://foo.com');
		$enabled = \Store_Feature::isEnabled('StoreTrends');
		\Store_Feature::override('StoreTrends', true);
		$controller = new AnalyticsController();

		$preference = array('sort' =>'yearly','preference'=>'viewStoreTrends');

		$requestMock = new Interspire_Request($preference, array(), array(), array(), array());
		$controller->setRequest($requestMock);

		$this->assertEquals($controller->getTrendsPeriod(), \Analytics\Metrics::YEARLY);

		\Store_Feature::override('StoreTrends', $enabled);
	}
}