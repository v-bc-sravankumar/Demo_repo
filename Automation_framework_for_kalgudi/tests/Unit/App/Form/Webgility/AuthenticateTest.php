<?php

require_once dirname(__FILE__).'/../../../../../config/init/autoloader.php';

class Unit_Form_Webgility_AuthenticateTest extends PHPUnit_Framework_TestCase
{
    public function testAuthenticateSuccess()
    {
        $service = $this->getMockWithoutConstructor('\Services\Webgility', array('authenticate'));
        $service->expects($this->once())
                ->method('authenticate')
                ->with('s@xu.net', 'asdfasdf', 'Bronze')
                ->will($this->returnValue(array(
                    'id' => 12345,
                    'quickbooks_online' => false,
                )));

        $form = new \Form\Webgility\Authenticate($service, 'Bronze');
        $form->setAttributes(array(
            'email'             => 's@xu.net',
            'password'          => 'asdfasdf',
        ));
        $details = $form->authenticate();

        $this->assertEquals(
            $details,
            array(
                'id' => 12345,
                'quickbooks_online' => false,
            ),
            'Expecting authenticate() to return an account ID'
        );
    }

    public function testAuthenticateApiError()
    {
        $exceptions = array(
            array(
                new \Services\Webgility\Exception\InvalidResponse(),
                "/couldn't communicate with Webgility/i"
            ),
            array(
                new \Services\Webgility\Exception\InvalidApiKey(),
                "/couldn't communicate with Webgility/i"
            ),
            array(
                new \Services\Webgility\Exception\InvalidData(),
                "/Couldn't log you in with that email and password/i"
            ),
            array(
                new \Services\Webgility\Exception\InvalidAuthenticationDetails(),
                "/Couldn't log you in with that email and password/i"
            ),
        );

        foreach($exceptions as $expectation) {
            list($exception, $messageRegex) = $expectation;

            $service = $this->getMockWithoutConstructor('\Services\Webgility', array('authenticate'));
            $service->expects($this->once())
                    ->method('authenticate')
                    ->will($this->throwException($exception));

            $form = new \Form\Webgility\Authenticate($service, 'Bronze');
            $form->setAttributes(array(
                'email'             => 's@xu.net',
                'password'          => 'asdfasdf',
            ));
            $form->authenticate();
            $errors = $form->getErrors();

            $this->assertFalse($form->isValid());
            $this->assertTrue(!empty($errors['base'][0]));
            $this->assertTrue((bool) preg_match($messageRegex, $errors['base'][0]));
        }
    }

    public function testRequiredFieldValidationErrors()
    {
        // TODO: This duplicates required-field test coverage from AuthenticateTest; consider
        // testing the parent class directly.
        $form = new \Form\Webgility\Authenticate(new stdClass, 'Bronze');
        $form->setAttributes(array(
            'email' => '', // present, but blank
            'password'  => 'Present, not blank',
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse(empty($form->errors));

        // Present attribute
        $this->assertTrue(empty($form->errors['password']));

        // Blank attribute
        $this->assertRegExp('/Email/',  $form->errors['email'][0]);
    }

    public function testPasswordFieldValidations()
    {
        // TODO: This duplicates required-field test coverage from AuthenticateTest; consider
        // testing the parent class directly.
        $form = new \Form\Webgility\Authenticate(new stdClass, 'Bronze');
        $form->setAttributes(array(
            'password'  => 'short', // < 6
        ));
        $this->assertFalse($form->isValid());
        $this->assertFalse(empty($form->errors['password']));
        $this->assertRegExp('/6.*characters long/', $form->errors['password'][0]);

        $form->setAttributes(array(
            'password'  => 'toolonglonglonglooong', // 21 chars, > 20
        ));
        $this->assertFalse($form->isValid());
        $this->assertFalse(empty($form->errors['password']));
        $this->assertRegExp('/20.*characters long/', $form->errors['password'][0]);

        $form->setAttributes(array(
            'password'  => 'justfine',
        ));
        $form->isValid();
        $this->assertTrue(empty($form->errors['password']));
    }

    public function testEmailFieldValidations()
    {
        // TODO: This duplicates required-field test coverage from AuthenticateTest; consider
        // testing the parent class directly.
        $form = new \Form\Webgility\Authenticate(new stdClass, 'Bronze');

        $form->setAttributes(array(
            'email'  => 'invalid',
        ));
        $this->assertFalse($form->isValid());
        $this->assertFalse(empty($form->errors['email']));
        $this->assertRegExp('/email.*not valid/', $form->errors['email'][0]);

        $form->setAttributes(array(
            'email'  => 'v@lid.org.au',
        ));
        $form->isValid();
        $this->assertTrue(empty($form->errors['email']));
    }

    // Test Helpers

    protected function getMockWithoutConstructor($originalClassName, $methods)
    {
        // The following code is unfortunately not a joke.
        return $this->getMock(
            $originalClassName,
            $methods,
            $arguments = array(),
            $mockClassName = '',
            $callOriginalConstructor = false // Disable constructor
        );
    }
}
