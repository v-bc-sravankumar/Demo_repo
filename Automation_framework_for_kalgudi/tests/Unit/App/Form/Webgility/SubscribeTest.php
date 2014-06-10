<?php

require_once dirname(__FILE__).'/../../../../../config/init/autoloader.php';

class Unit_Form_Webgility_SubscribeTest extends PHPUnit_Framework_TestCase
{
    public function testSubscribeSuccess()
    {
        $service = $this->getMockWithoutConstructor('\Services\Webgility', array('subscribe'));
        $service->expects($this->once())
                ->method('subscribe')
                ->with(
                    array(
                        'first_name' => 'Shieling',
                        'last_name'  => 'Xu',
                        'email'      => 's@xu.net',
                        'phone'      => '0299990000',
                        'password'   => 'asdfasdf',
                    ),
                    $quickBooksOnline = true,
                    'Bronze'
                )
                ->will($this->returnValue(12345));

        $form = new \Form\Webgility\Subscribe($service, 'Bronze');
        $form->setAttributes(array(
            'first-name'        => 'Shieling',
            'last-name'         => 'Xu',
            'email'             => 's@xu.net',
            'phone'             => '0299990000',
            'password'          => 'asdfasdf',
            'quickbooks-online' => true,
        ));
        $accountId = $form->subscribe();

        $this->assertEquals($accountId, 12345, 'Expecting subscribe() to return an account ID');
    }

    public function testSubscribeApiError()
    {
        $exceptions = array(
            array(
                new \Services\Webgility\Exception\InvalidResponse(),
                array('base' => "/couldn't communicate with Webgility/i"),
            ),
            array(
                new \Services\Webgility\Exception\InvalidApiKey(),
                array('base' => "/couldn't communicate with Webgility/i"),
            ),
            array(
                new \Services\Webgility\Exception\InvalidData(),
                array('base' => "/Couldn't subscribe you with those details/i"),
            ),
            array(
                new \Services\Webgility\Exception\UserAlreadyExists(),
                array('email' => "/user with that email address already exists/i"),
            ),
        );

        foreach($exceptions as $expectation) {
            list($exception, $msgExpectation) = $expectation;
            list($messageKey, $messageRegex)  = each($msgExpectation);

            $service = $this->getMockWithoutConstructor('\Services\Webgility', array('subscribe'));
            $service->expects($this->once())
                    ->method('subscribe')
                    ->will($this->throwException($exception));

            $form = new \Form\Webgility\Subscribe($service, 'Bronze');
            $form->setAttributes(array(
                'first-name'        => 'Shieling',
                'last-name'         => 'Xu',
                'email'             => 's@xu.net',
                'phone'             => '0299990000',
                'password'          => 'asdfasdf',
                'quickbooks-online' => true,
            ));
            $form->subscribe();
            $errors = $form->getErrors();

            $this->assertFalse($form->isValid());
            $this->assertTrue(!empty($errors[$messageKey][0]));
            $this->assertTrue(
              (bool) preg_match($messageRegex, $errors[$messageKey][0]),
              'Expected '.var_export($errors,1).' to match "'.$messageRegex.'"'
              .' in '.$messageKey.' for exception '.get_class($exception)
            );
        }
    }

    public function testRequiredFieldValidationErrors()
    {
        $form = new \Form\Webgility\Subscribe(new stdClass, 'Bronze');
        $form->setAttributes(array(
            'first-name'  => 'Present, not blank; should have no error',
            'last-name' => '', // present, but blank
        ));

        $this->assertFalse($form->isValid());
        $this->assertFalse(empty($form->errors));

        // Present attribute
        $this->assertTrue(empty($form->errors['first-name']));

        // Blank attribute
        $this->assertRegExp('/Last Name/',  $form->errors['last-name'][0]);

        // Missing attributes
        $this->assertRegExp('/Email/',      $form->errors['email'][0]);
        $this->assertRegExp('/Phone/',      $form->errors['phone'][0]);
        $this->assertRegExp('/Password/',   $form->errors['password'][0]);
        $this->assertRegExp('/QuickBooks/', $form->errors['quickbooks-online'][0]);
    }

    public function testPasswordFieldValidations()
    {
        $form = new \Form\Webgility\Subscribe(new stdClass, 'Bronze');
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
        $form = new \Form\Webgility\Subscribe(new stdClass, 'Bronze');

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

    public function testPhoneFieldValidations()
    {
        $form = new \Form\Webgility\Subscribe(new stdClass, 'Bronze');

        $invalidValues = array(
            '123456789', // 9 chars, too short
            '1234-56789', // 10 chars, but only 9 digits
        );
        foreach ($invalidValues as $value) {
            $form->setAttributes(array(
                'phone'  => $value,
            ));
            $this->assertFalse($form->isValid());
            $this->assertFalse(empty($form->errors['phone']), "Expected '$value' to be invalid");
            $this->assertRegExp('/at least 10 digits/', $form->errors['phone'][0]);
        }

        $validValues = array(
            '1234567890', // 10 digits
            'abc 1234567890', // 10 digits, extraneous characters
        );
        foreach ($validValues as $value) {
            $form->setAttributes(array(
                'phone'  => $value,
            ));
            $form->isValid();
            $this->assertTrue(empty($form->errors['phone']), "Expected '$value' to be valid");
        }
    }

    public function testSetDefaults()
    {
        $form = new \Form\Webgility\Subscribe(new stdClass, 'Bronze');
        $formAttributes = $form->getAttributes();
        $this->assertEquals(0, count($formAttributes));

        $user = new \Store_User();
        $user->setUserFirstname('foo');
        $user->setUserLastname('bar');
        $user->setUserEmail('foo@bar.com');

        $form->setDefaults($user);

        $formAttributes = $form->getAttributes();

        $this->assertEquals($formAttributes['first-name'], $user->getUserFirstname());
        $this->assertEquals($formAttributes['last-name'], $user->getUserLastname());
        $this->assertEquals($formAttributes['email'], $user->getUserEmail());
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
