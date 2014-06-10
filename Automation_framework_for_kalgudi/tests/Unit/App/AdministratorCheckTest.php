<?php

class Unit_App_AdministratorCheckTest extends PHPUnit_Framework_TestCase
{
    public function testUserId()
    {
        $authMock = $this
            ->getMock(
                'ISC_ADMIN_AUTH', // originalClassName
                array('GetUser'), // methods
                array(),          // arguments
                '',               // mockClassName
                false             // callOriginalConstructor
            );

        $authMock
            ->expects($this->at(0))
            ->method('GetUser')
            ->will(
                $this->returnValue(
                    array(
                        'pk_userid' => 1,
                        'username' => 'arbitrary-username'
                    )
                )
            );

        $authMock
            ->expects($this->at(1))
            ->method('GetUser')
            ->will(
                $this->returnValue(
                    array(
                        'pk_userid' => 2,
                        'username' => 'arbitrary-username'
                    )
                )
            );

        // Test that, when the user ID is 1, `isAdministrator' returns `true'.
        $this->assertTrue(ISC_ADMIN_AUTH::isAdministrator($authMock));

        // Test that, when the user ID is 2, `isAdministrator' returns `false'.
        $this->assertFalse(ISC_ADMIN_AUTH::isAdministrator($authMock));
    }

    public function testUserName()
    {
        $authMock = $this
            ->getMock(
                'ISC_ADMIN_AUTH', // originalClassName
                array('GetUser'), // methods
                array(),          // arguments
                '',               // mockClassName
                false             // callOriginalConstructor
            );

        $authMock
            ->expects($this->at(0))
            ->method('GetUser')
            ->will(
                $this->returnValue(
                    array(
                        'pk_userid' => 2,
                        'username' => 'admin'
                    )
                )
            );

        $authMock
            ->expects($this->at(1))
            ->method('GetUser')
            ->will(
                $this->returnValue(
                    array(
                        'pk_userid' => 2,
                        'username' => 'arbitrary-username'
                    )
                )
            );

        // Test that, when username is `admin', `isAdministrator' returns `true'.
        $this->assertTrue(ISC_ADMIN_AUTH::isAdministrator($authMock));

        // Test that, when username is not `admin', `isAdministrator' returns `false'.
        $this->assertFalse(ISC_ADMIN_AUTH::isAdministrator($authMock));
    }
}

