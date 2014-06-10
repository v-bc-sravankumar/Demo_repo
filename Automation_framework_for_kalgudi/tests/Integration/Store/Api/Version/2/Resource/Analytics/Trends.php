<?php

class Integration_Store_Api_Version_2_Resource_Analytics_Trends extends \Interspire_IntegrationTest
{
	private $resource = null;
	private $_dummyOrders = array();

	public static function setUpBeforeClass()
	{
		\Interspire_DataFixtures::getInstance()->removeData('analytics_trends');
		\Interspire_DataFixtures::getInstance()->loadData('analytics_trends');
	}

	public static function tearDownAfterClass()
	{
		\Interspire_DataFixtures::getInstance()->removeData('analytics_trends');
	}

	public function setUp()
	{
		$this->resource = new \Store_Api_Version_2_Resource_Analytics_Trends();
		$this->resource->setTimeNow("@1389619500"); // 2014-01-13 T 13:25:00 + 00:00

		$this->_createDummyOrder(array(
                'ordcustid' => 7,
                'orddate' => "1387839093",
                'ordlastmodified' => "1389653493",
                'subtotal_ex_tax' => 445.0000,
                'ordstatus' => 6
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 7,
                'orddate' => "1389135093",
                'ordlastmodified' => "1389653493",
                'subtotal_ex_tax' => 1960.0000,
                'ordstatus' => 1
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 10,
                'orddate' => "1388098293",
                'ordlastmodified' => "1389653493",
                'subtotal_ex_tax' => 966.0000,
                'ordstatus' => 1
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 3,
                'orddate' => "1385419893",
                'ordlastmodified' => "1389653494",
                'subtotal_ex_tax' => 560.0000,
                'ordstatus' => 2
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 4,
                'orddate' => "1387493494",
                'ordlastmodified' => "1389653494",
                'subtotal_ex_tax' => 5502.0000,
                'ordstatus' => 10
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 6,
                'orddate' => "1388875894",
                'ordlastmodified' => "1389653494",
                'subtotal_ex_tax' => 4116.0000,
                'ordstatus' => 10
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 2,
                'orddate' => "1384469494",
                'ordlastmodified' => "1389653494",
                'subtotal_ex_tax' => 611.0000,
                'ordstatus' => 3
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 2,
                'orddate' => "1386888694",
                'ordlastmodified' => "1389653495",
                'subtotal_ex_tax' => 369.0000,
                'ordstatus' => 8
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 3,
                'orddate' => "1386715894",
                'ordlastmodified' => "1389653495",
                'subtotal_ex_tax' => 2788.0000,
                'ordstatus' => 11
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 6,
                'orddate' => "1388789495",
                'ordlastmodified' => "1389653495",
                'subtotal_ex_tax' => 196.0000,
                'ordstatus' => 9
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 6,
                'orddate' => "1387234295",
                'ordlastmodified' => "1389653495",
                'subtotal_ex_tax' => 196.0000,
                'ordstatus' => 4
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 1,
                'orddate' => "1384728695",
                'ordlastmodified' => "1389653495",
                'subtotal_ex_tax' => 3973.0000,
                'ordstatus' => 6
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 7,
                'orddate' => "1389653495",
                'ordlastmodified' => "1389653496",
                'subtotal_ex_tax' => 935.0000,
                'ordstatus' => 4
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 1,
                'orddate' => "1389653578",
                'ordlastmodified' => "1389653578",
                'subtotal_ex_tax' => 833.0000,
                'ordstatus' => 6
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 6,
                'orddate' => "1389567178",
                'ordlastmodified' => "1389653578",
                'subtotal_ex_tax' => 3038.0000,
                'ordstatus' => 4
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 4,
                'orddate' => "1389653602",
                'ordlastmodified' => "1389653602",
                'subtotal_ex_tax' => 801.0000,
                'ordstatus' => 8
            ));

		$this->_createDummyOrder(array(
                'ordcustid' => 5,
                'orddate' => "1389567202",
                'ordlastmodified' => "1389653602",
                'subtotal_ex_tax' => 2926.0000,
                'ordstatus' => 8
            ));

	}

	public function tearDown()
	{
		$this->resource = null;
		foreach ($this->_dummyOrders as $order) {
			try {
				$this->_deleteOrder($order);
			} catch (Store_Api_Exception_Resource_ResourceNotFound $e) {
				// a test already deleted this? ignore
			}
		}
	}

	private function _createDummyOrder ($data = array())
	{

		if (array_key_exists('subtotal_ex_tax', $data)) {
			$price = $data['subtotal_ex_tax'];
			$data['subtotal_inc_tax'] = $price;
			$data['total_ex_tax'] = $price;
			$data['total_inc_tax'] = $price;
		}

		$data = array_merge(array(
			'ordtoken' => GenerateOrderToken(),
		), $data);

		$orderId = Store::getStoreDb()->InsertQuery('orders', $data);
		$this->assertTrue(isId($orderId), "dummy order insert failed: " . store::getStoreDb()->GetErrorMsg());

		$orderId = (int)$orderId;
		$this->_dummyOrders[] = $orderId;

		return Store::getStoreDb()->FetchRow("SELECT * FROM [|PREFIX|]orders WHERE orderid = " . $orderId);
	}

	private function _deleteOrder ($id, $resource = null)
	{
		if ($resource === null) {
			$resource = new Store_Api_Version_2_Resource_Orders();
		}

		$id = (int)$id;
		if (!$id) {
			throw new Exception;
		}

		$request = new Interspire_Request();
		$request->setUserParam('orders', $id);
		return $resource->deleteAction($request);
	}

	/**
	 * GET analytics/trends - defaults to period=daily
	 */
	public function testGetPeriodDaily() 
	{
	    $request = new \Interspire_Request(null, null, null,
	                                       array('REQUEST_METHOD' => 'get',
	                                             'CONTENT_TYPE' => 'application/json'));
	    $data = $this->resource->getAction($request)->getData();
	    $expected = array('periods' => array('current' => array('from' => '2014-01-13T00:00:00+00:00', 'to' => '2014-01-13T13:25:00+00:00'),
	                                         'previous' => array('from' => '2014-01-12T00:00:00+00:00', 'to' => '2014-01-12T13:25:00+00:00'),
	                                         'previous_totals' => array('from' => '2014-01-12T00:00:00+00:00', 'to' => '2014-01-12T23:59:59+00:00')),
	            'orders' => array('current' => 0,
	                              'previous' => 0,
	                              'previous_totals' => 1),
	            'revenue' => array('current' => 0,
	                              'previous' => 0,
	                              'previous_totals' => 2926.0),
	            'visitors' => array('current' => 3,
	                              'previous' => 2,
	                              'previous_totals' => 2),
	            'conversion' => array('current' => 0,
	                              'previous' => 0,
	                              'previous_totals' => 0.5)
	    );
	    $this->assertEquals($expected, $data);
	}
	
	/**
	 * GET analytics/trends?period=monthly
	 */
	public function testGetPeriodMonthly()
	{
	    $request = new \Interspire_Request(array('period' => "monthly"), 
	                                       null, null,
                            	           array('REQUEST_METHOD' => 'get',
                            	                 'CONTENT_TYPE' => 'application/json'));
	    $data = $this->resource->getAction($request)->getData();
	    $expected = array('periods' => array('current' => array('from' => '2014-01-01T00:00:00+00:00', 'to' => '2014-01-13T13:25:00+00:00'),
	                                         'previous' => array('from' => '2013-12-01T00:00:00+00:00', 'to' => '2013-12-13T13:25:00+00:00'),
	                                         'previous_totals' => array('from' => '2013-12-01T00:00:00+00:00', 'to' => '2013-12-31T23:59:59+00:00')),
	            'orders' => array('current' => 3,
	                              'previous' => 2,
	                              'previous_totals' => 3),
	            'revenue' => array('current' => 7238.0,
	                              'previous' => 3157.0,
	                              'previous_totals' => 8659.0),
	            'visitors' => array('current' => 11,
	                              'previous' => 7,
	                              'previous_totals' => 11),
	            'conversion' => array('current' => 0.27272727272727,
	                              'previous' => 0.28571428571429,
	                              'previous_totals' => 0.27272727272727)
	    );
	    $this->assertEquals($expected, $data);
	}
	
	/**
	 * GET analytics/trends with from_date and to_date parameters
	 */
	public function testGetRange()
	{
	    $request = new \Interspire_Request(array('from_date' => "2014-01-12",
	                                             'to_date' => "2014-01-14"), 
	                                       null, null,
	                                       array('REQUEST_METHOD' => 'get',
	                                             'CONTENT_TYPE' => 'application/json'));
	    $data = $this->resource->getAction($request)->getData();
	    $expected = array('orders' => 2,
        	              'revenue' => 3727,
        	              'visitors' => 5,
        	              'conversion' => 0.4);
	    $this->assertEquals($expected, $data);
	}
	
	/**
	 * GET analytics/trends supplying from_date but no to_date
	 * @expectedException Store_Api_Exception_Request_InvalidField
	 * @expectedExceptionCode 400
	 */
	public function testGetRangeNotSupplied()
	{
	    $request = new \Interspire_Request(array('from_date' => "2014-01-12"),
                                    	   null, null,
                                    	   array('REQUEST_METHOD' => 'get',
                                    	         'CONTENT_TYPE' => 'application/json'));
	    $data = $this->resource->getAction($request)->getData();
	}
	
	/**
	 * GET analytics/trends supplying invalid from_date and to_date
	 * @expectedException Store_Api_Exception_Request_InvalidField
	 * @expectedExceptionCode 400
	 */
	public function testGetRangeInvalid()
	{
	    $request = new \Interspire_Request(array('from_date' => "1300-88-18",
	                                             'to_date' => "13-99-77"),
                        	               null, null,
                        	               array('REQUEST_METHOD' => 'get',
                        	                     'CONTENT_TYPE' => 'application/json'));
	    $data = $this->resource->getAction($request)->getData();
	}
	
	/**
	 * GET analytics/trends supplying invalid period
	 * @expectedException Store_Api_Exception_Request_InvalidField
	 * @expectedExceptionCode 400
	 */
	public function testGetPeriodInvalid()
	{
	    $request = new \Interspire_Request(array('period' => "purplemonkeydishwasher"),
                            	           null, null,
                            	           array('REQUEST_METHOD' => 'get',
                            	                 'CONTENT_TYPE' => 'application/json'));
	    $data = $this->resource->getAction($request)->getData();
	}
}