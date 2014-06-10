<?php

class Unit_Settings_Driver_Service extends PHPUnit_Framework_TestCase
{
	public function testCanPullConfigFromService()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('fetchRawConfigFromService', 'getConfigCache'));

		$fixture = array(
			'foo' => 'bar',
		);

		$settings->expects($this->once())
			->method('getConfigCache')
			->will($this->returnValue(false));

		$settings->expects($this->once())
			->method('fetchRawConfigFromService')
			->will($this->returnValue(json_encode($fixture)));

		$results = $settings->pull();
		$this->assertSame($fixture, $results);
	}

	public function testCachedConfigHasPriorityOverServiceConfig()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('fetchConfigFromService', 'getConfigCache'));

		$fixture = array(
			'foo' => 'baz',
		);

		$settings->expects($this->once())
			->method('getConfigCache')
			->will($this->returnValue($fixture));

		$settings->expects($this->never())
			->method('fetchConfigFromService');

		$results = $settings->pull();
		$this->assertSame($fixture, $results);
	}

	public function testCacheMissOnPullWillUpdateCache()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('fetchRawConfigFromService', 'getConfigCache', 'setConfigCache'));

		$fixture = array(
			'foo' => 'bar',
		);

		$settings->expects($this->once())
			->method('getConfigCache')
			->will($this->returnValue(false));

		$settings->expects($this->once())
			->method('fetchRawConfigFromService')
			->will($this->returnValue(json_encode($fixture)));

		$settings->expects($this->once())
			->method('setConfigCache')
			->with($fixture);

		$results = $settings->pull();
		$this->assertSame($fixture, $results);
	}

	public function testCanPushConfigChanges()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => 'bar',
		);

		$expected = json_encode(array(
			'set' => $changes,
		));

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testCanPushConfigDeletes()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$deletes = array(
			'foo' => 'bar',
		);

		$expected = json_encode(array(
			'unset' => $deletes,
		));

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push(array(), $deletes);
	}

	public function testFlushRemovesCache()
	{
		$settings = $this->getMock('Store_Settings_Driver_Service', array('removeConfigCache'));
		$settings->expects($this->atLeastOnce())
		         ->method('removeConfigCache');
		$settings->flush();
	}

	public function testPushFlushesCacheByDefault()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang', 'removeConfigCache'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => 'bar',
		);

		$http->expects($this->once())
			->method('patch');

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->expects($this->once())
			->method('removeConfigCache');

		$settings->push($changes);
	}

	public function testCanFlushCacheOnPush()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang', 'removeConfigCache'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => 'bar',
		);

		$http->expects($this->once())
			->method('patch');

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->expects($this->once())
			->method('removeConfigCache');

		$settings->setClearCacheOnPush(true);

		$settings->push($changes);
	}

	public function testCanSendBooleanPatch()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => true,
		);

		$expected = '{"set":{"foo":true}}';

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testCanSendIntegerPatch()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => 1234,
		);

		$expected = '{"set":{"foo":1234}}';

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testCanSendDoublePatch()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => 1234.5678,
		);

		$expected = '{"set":{"foo":1234.5678}}';

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testCanSendBasicStringPatch()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => 'bar',
		);

		$expected = '{"set":{"foo":"bar"}}';

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testCanSendUtf8StringPatch()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => '€¥¢£',
		);

		$expected = '{"set":{"foo":"\u20ac\u00a5\u00a2\u00a3"}}';

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testCanSendSimpleArrayPatch()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => array(
				'alpha',
				'beta',
				'gamma',
			),
		);

		$expected = '{"set":{"foo":["alpha","beta","gamma"]}}';

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testCanSendNestedArrayPatch()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => array(
				'alpha' => array(1,2,3),
				'beta' => array(4,5,6),
				'gamma' => array(7,8,9),
			),
		);

		$expected = '{"set":{"foo":{"alpha":[1,2,3],"beta":[4,5,6],"gamma":[7,8,9]}}}';

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testCanSendEmptyArrayPatch()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => array(),
		);

		$expected = '{"set":{"foo":[]}}';

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$settings->push($changes);
	}

	public function testFailedHttpPatchCausesException()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('patch', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$changes = array(
			'foo' => 'bar',
		);

		$expected = json_encode(array(
			'set' => $changes,
		));

		$http->expects($this->once())
			->method('patch')
			->with($this->anything(), $expected);

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(500));

		$this->setExpectedException('Store_Settings_Driver_Service_PushException');
		$settings->push($changes);
	}

	public function testFailedHttpGetCausesException()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getConfigCache', 'getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('get', 'getStatus'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$settings->expects($this->once())
			->method('getConfigCache')
			->will($this->returnValue(false));

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(500));

		$this->setExpectedException('Store_Settings_Driver_Service_PullException', 'Config service error: ');
		$settings->pull();
	}

	public function testInvalidJsonFromHttpGetCausesException()
	{
		/** @var Store_Settings_Driver_Service $settings */
		$settings = $this->getMock('Store_Settings_Driver_Service', array('getConfigCache', 'getHttpClient', 'getLang'));

		$http = $this->getMock('Interspire_Http_Client', array('get', 'getStatus', 'getBody'));
		$settings->expects($this->any())
			->method('getHttpClient')
			->will($this->returnValue($http));

		$settings->expects($this->once())
			->method('getConfigCache')
			->will($this->returnValue(false));

		$http->expects($this->once())
			->method('getStatus')
			->will($this->returnValue(200));

		$http->expects($this->once())
			->method('getBody')
			->will($this->returnValue('foo'));

		$this->setExpectedException('Store_Settings_Driver_Service_PullException', 'Invalid json response from config service');
		$settings->pull();
	}
}
