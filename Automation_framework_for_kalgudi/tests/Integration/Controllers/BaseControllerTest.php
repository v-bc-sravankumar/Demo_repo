<?php

class Unit_Controllers_BaseControllerTest extends PHPUnit_Framework_TestCase
{
    public function testUnauthorizedAccess()
    {
        $controller = new BaseController();

        $request = $this->getMock('Interspire_Request', array('getAcceptMediaTypes'));
        $request->expects($this->any())
            ->method('getAcceptMediaTypes')
            ->will($this->returnValue(array('html')));

        $response = $this->getMock('Interspire_Response', array('setStatus', 'setBody', 'sendResponse', 'end'));
        $response->expects($this->any())
            ->method('setStatus')
            ->with(403);

        $validator = $this->getMock('ISC_ADMIN_AUTH', array('HasPermission'));
        $validator->expects($this->any())
            ->method('HasPermssion')
            ->will($this->returnValue(false));

        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setRequirePermission(AUTH_Manage_Settings)
            ->setPermissionValidator($validator);

        $controller->beforeAction();
    }
}
