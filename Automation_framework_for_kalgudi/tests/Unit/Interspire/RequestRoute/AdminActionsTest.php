<?php
class Unit_Interspire_RequestRoute_AdminActionsTest extends PHPUnit_Framework_TestCase
{

	private function getMockRequest($uri)
	{
		$request = $this->getMock('Interspire_Request');
		$request->expects($this->any())
		->method('getAppPath')
		->will($this->returnValue($uri));

		return $request;
	}

	public function testPreFollowInitSession()
	{
		$this->markTestSkipped('Broken due to dependencies on PHP configuration.');

		$sessionId = session_id();
		if ($sessionId) {
			session_destroy();
		}
		$this->assertEquals("", session_id());

		$request = $this->getMockRequest('/foo/bar');

		$controller = new Store_RequestRoute_AdminActions();

		$class = new ReflectionClass("Store_RequestRoute_AdminActions");
		$method = $class->getMethod("_preFollow");
		$method->setAccessible(true);

		$method->invoke($controller, $request);

		$this->assertNotNull(session_id());
		session_id($sessionId);
	}

	public function testPreFollowInvalidRequest()
	{
		$request = $this->getMockRequest('/foo/bar');

		$mockAuth = $this
			->getMockBuilder('ISC_ADMIN_AUTH')
			->disableOriginalConstructor()
			->getMock();

		$mockAuth->expects($this->any())
		->method('IsLoggedIn')
		->will($this->returnValue(true));

		$controller = $this->getMock('Store_RequestRoute_AdminActions',
				array('isValidRequest','getPermissionValidator', 'initAdmin'));

		$controller->expects($this->any())
			->method('isValidRequest')
			->will($this->returnValue(false));

		$controller->expects($this->any())
			->method('getPermissionValidator')
			->will($this->returnValue($mockAuth));

		$class = new ReflectionClass("Store_RequestRoute_AdminActions");
		$method = $class->getMethod("_preFollow");
		$method->setAccessible(true);

		$this->assertFalse($method->invoke($controller, $request));
	}

	public function testPreFollowLoggedInNotLoggedIn()
	{
		$truePermissionValidator = $this
			->getMockBuilder('ISC_ADMIN_AUTH')
			->disableOriginalConstructor()
			->getMock();

		$truePermissionValidator->expects($this->any())
			->method('IsLoggedIn')
			->will($this->returnValue(true));

		$request = $this->getMockRequest('/foo/bar');

		$controller = $this->getMock('Store_RequestRoute_AdminActions', array('initAdmin'));

		$class = new ReflectionClass("Store_RequestRoute_AdminActions");

		$adminActions_preFollow = $class->getMethod("_preFollow");
		$adminActions_preFollow->setAccessible(true);

		$adminAction_setPermissionValidator = $class->getMethod("setPermissionValidator");
		$adminAction_setPermissionValidator->setAccessible(true);

		$adminAction_setPermissionValidator->invoke($controller, $truePermissionValidator);
		$this->assertTrue($adminActions_preFollow->invoke($controller, $request));

		$falsePermissionValidator = $this
			->getMockBuilder('ISC_ADMIN_AUTH')
			->disableOriginalConstructor()
			->getMock();

		$falsePermissionValidator->expects($this->any())
			->method('IsLoggedIn')
			->will($this->returnValue(false));

		$adminAction_setPermissionValidator->invoke($controller, $falsePermissionValidator);
		$this->assertFalse($adminActions_preFollow->invoke($controller, $request));
	}

	public function testPreFollowNotLoggedInJSONRequest()
	{
		$request = $this->getMockRequest('/foo/bar');

		// mock json mime type header
		$request->expects($this->any())
		->method('getAcceptMediaTypes')
		->will($this->returnValue(array('Accept'=>'application/json',)));

		$mockAuth = $this
			->getMockBuilder('ISC_ADMIN_AUTH')
			->disableOriginalConstructor()
			->getMock();

		// set unauthenticated
		$mockAuth->expects($this->any())
		->method('IsLoggedIn')
		->will($this->returnValue(false));

		$controller = $this->getMock('Store_RequestRoute_AdminActions',
				array('isValidRequest','getPermissionValidator', 'initAdmin'));

		$controller->expects($this->any())
			->method('isValidRequest')
			->will($this->returnValue(false));

		$controller->expects($this->any())
			->method('getPermissionValidator')
			->will($this->returnValue($mockAuth));

		$class = new ReflectionClass("Store_RequestRoute_AdminActions");
		$method = $class->getMethod("_preFollow");
		$method->setAccessible(true);

		// expect to allow request/route to continue BaseController.php where beforeAction() will return 403
		$this->assertTrue($method->invoke($controller, $request));
	}

}
