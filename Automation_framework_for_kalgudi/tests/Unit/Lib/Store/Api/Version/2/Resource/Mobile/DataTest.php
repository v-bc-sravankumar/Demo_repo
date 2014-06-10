<?php

namespace Unit\Lib\Store\Api\Version\V2\Resource\Mobile;
use Analytics\Metrics;

class DataTest extends \PHPUnit_Framework_TestCase
{

	private $ordersCountMock;
	private $dashboardMock;
	private $metricsMock;

	public function setUp()
	{
		$this->ordersCountMock = $this
			->getMockBuilder('\Store_Api_Version_2_Resource_Mobile_Orders_Count')
			->setMethods(array('getAction'));

		$this->dashboardMock = $this
			->getMockBuilder('\Store_Api_Version_2_Resource_Mobile_Dashboard')
			->setMethods(array('getAction'));

		$this->metricsMock = $this
			->getMockBuilder('\Analytics\Metrics')
			->setMethods(array('getSummary'));
	}

	public function testAggregateData()
	{
		$ordersCountMock = $this->ordersCountMock->getMock();
		$ordersCountMock
			->expects($this->once())
			->method('getAction')
			->will($this->returnValue(new DataStub(array('OrdersCountKey' => 'OrdersCountValue'))));

		$dashboardMock = $this->dashboardMock->getMock();
		$dashboardMock
			->expects($this->once())
			->method('getAction')
			->will($this->returnValue(new DataStub(array(array('DashboardKey' => 'DashboardValue')))));

		$metricsMock = $this->metricsMock->getMock();
		$metricsMock
			->expects($this->at(0))
			->method('getSummary')
			->with($this->equalTo('visitors'), $this->equalTo(Metrics::LAST7DAYS))
			->will($this->returnValue('VisitorsLast7Days'));

		$metricsMock
			->expects($this->at(1))
			->method('getSummary')
			->with($this->equalTo('visitors'), $this->equalTo(Metrics::LAST30DAYS))
			->will($this->returnValue('VisitorsLast30Days'));

		$metricsMock
			->expects($this->at(2))
			->method('getSummary')
			->with($this->equalTo('orders'), $this->equalTo(Metrics::LAST7DAYS))
			->will($this->returnValue('OrdersLast7Days'));

		$metricsMock
			->expects($this->at(3))
			->method('getSummary')
			->with($this->equalTo('orders'), $this->equalTo(Metrics::LAST30DAYS))
			->will($this->returnValue('OrdersLast30Days'));

		$data = new \Store_Api_Version_2_Resource_Mobile_Data();
		$data->setOrdersCount($ordersCountMock)
			->setDashboard($dashboardMock)
			->setMetrics($metricsMock);

		$result = $data->getAction()->getData();
		$expected = array(
			'OrdersCountKey' => 'OrdersCountValue',
			'DashboardKey' => 'DashboardValue',
			'visitorsLast7Days' => 'VisitorsLast7Days',
			'visitorsLast30Days' => 'VisitorsLast30Days',
			'ordersLast7Days' => 'OrdersLast7Days',
			'ordersLast30Days' => 'OrdersLast30Days',
		);

		$this->assertEquals($expected, $result);
	}

}


class DataStub
{

	private $returnValue;

	public function __construct($returnValue)
	{
		$this->returnValue = $returnValue;
	}

	public function getData()
	{
		return $this->returnValue;
	}
}
