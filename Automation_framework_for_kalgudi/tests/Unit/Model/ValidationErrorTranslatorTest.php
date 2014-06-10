<?php

namespace Unit\Model;

use PHPUnit_Framework_TestCase;
use Model\ValidationErrorTranslator;
use Model\ValidationError;
use Model\AbstractValidatableModel;
use Model\Exception\ValidationException;
use Symfony\Component\Validator\Constraints as Assert;
use Language\LanguageManager;
use Language\Loader\ArrayLanguageLoader;

class TestTranslatorModel extends AbstractValidatableModel
{
  protected function getValidationRules()
  {
    return array();
  }
}

class StringObject
{
  public function __toString()
  {
    return "my object";
  }
}

class ValidationErrorTranslatorTest extends PHPUnit_Framework_TestCase
{
  private function getLanguageManager()
  {
    $manager = new LanguageManager('en', new ArrayLanguageLoader(array(
      'en' => array(
        'model/unit/model/testtranslatormodel' => array(
          'email_field.email' => '":value" is not a valid email address',
          'int_field.type' => 'int_field should be an integer',
          'int_field.notblank' => 'int_field should not be blank',
          'another_field.any' => 'another field is invalid',
        ),
      ),
    )));

    return $manager;
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testLoadLanguageForModelWithNoLanguageThrowsException()
  {
    $manager = new LanguageManager('en', new ArrayLanguageLoader(array()));
    $translator = new ValidationErrorTranslator($manager);

    $translator->loadLanguageForModel(new TestTranslatorModel());
  }

  public function testLoadLanguageForModelWithLanguageSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());
  }

  public function testTranslateErrorWithSingleConstraintSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());

    $validationError = new ValidationError('int_field', array('type'), 'foo');

    $messages = $translator->translateError($validationError);
    $this->assertContains('int_field should be an integer', $messages);
  }

  public function testTranslateErrorWithMultipleConstraintsSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());

    $validationError = new ValidationError('int_field', array('type', 'notblank'), '');

    $messages = $translator->translateError($validationError);

    $this->assertContains('int_field should be an integer', $messages);
    $this->assertContains('int_field should not be blank', $messages);
  }

  public function testTranslateErrorForGenericMessageSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());

    $validationError = new ValidationError('another_field', array('notblank'), '');

    $messages = $translator->translateError($validationError);

    $this->assertContains('another field is invalid', $messages);
  }

  public function testTranslateErrorForMissingLanguageIsEmpty()
  {
    $manager = new LanguageManager('en', new ArrayLanguageLoader(array()));
    $translator = new ValidationErrorTranslator($manager);

    $validationError = new ValidationError('email_field', array('email'), 'foo');

    $messages = $translator->translateError($validationError);

    $this->assertEmpty($messages);
  }

  public function testTranslateErrorWithScalarSubstitutionSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());

    $validationError = new ValidationError('email_field', array('email'), 'foo');

    $messages = $translator->translateError($validationError);
    $this->assertContains('"foo" is not a valid email address', $messages);
  }

  public function testTranslateErrorWithInvalidSubstitutionSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());

    $validationError = new ValidationError('email_field', array('email'), array());

    $messages = $translator->translateError($validationError);
    $this->assertContains('"" is not a valid email address', $messages);
  }

  public function testTranslateErrorWithObjectToStringSubstitutionSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());

    $validationError = new ValidationError('email_field', array('email'), new StringObject());

    $messages = $translator->translateError($validationError);
    $this->assertContains('"my object" is not a valid email address', $messages);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Object is not an instance of ValidationError.
   */
  public function testTranslateErrorsWithInvalidErrorThrowsException()
  {
    $manager = new LanguageManager('en', new ArrayLanguageLoader(array()));
    $translator = new ValidationErrorTranslator($manager);

    $translator->translateErrors(array('foo'));
  }

  public function testTranslateErrorsForMultipleErrorsSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());

    $errors = array(
      new ValidationError('email_field', array('email'), 'foo'),
      new ValidationError('int_field', array('type'), 'foo'),
    );

    $messages = $translator->translateErrors($errors);

    $this->assertContains('"foo" is not a valid email address', $messages);
    $this->assertContains('int_field should be an integer', $messages);
  }

  public function testTranslateErrorsAndIndexMessagesByFieldSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel(new TestTranslatorModel());

    $errors = array(
      new ValidationError('email_field', array('email'), 'foo'),
      new ValidationError('int_field', array('type', 'notblank'), ''),
    );

    $messages = $translator->translateErrors($errors, true);

    $this->assertArrayHasKey('email_field', $messages);
    $emailMessages = $messages['email_field'];
    $this->assertInternalType('array', $emailMessages);
    $this->assertContains('"foo" is not a valid email address', $emailMessages);

    $this->assertArrayHasKey('int_field', $messages);
    $intMessages = $messages['int_field'];
    $this->assertInternalType('array', $intMessages);
    $this->assertContains('int_field should be an integer', $intMessages);
    $this->assertContains('int_field should not be blank', $intMessages);
  }

  public function testTranslateValidationExceptionSucceedsForPreloadedLanguage()
  {
    $model = new TestTranslatorModel();
    $translator = new ValidationErrorTranslator($this->getLanguageManager());
    $translator->loadLanguageForModel($model);

    $errors = array(
      'email_field' => new ValidationError('email_field', array('email'), 'foo'),
      'int_field' => new ValidationError('int_field', array('type'), 'foo'),
    );

    $exception = new ValidationException($model, $errors);

    $messages = $translator->translateValidationException($exception);

    $this->assertContains('"foo" is not a valid email address', $messages);
    $this->assertContains('int_field should be an integer', $messages);
  }

  public function testTranslateValidationExceptionSucceedsForLanguageLoadedViaException()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());

    $errors = array(
      'email_field' => new ValidationError('email_field', array('email'), 'foo'),
      'int_field' => new ValidationError('int_field', array('type'), 'foo'),
    );

    $exception = new ValidationException(new TestTranslatorModel(), $errors);

    $messages = $translator->translateValidationException($exception);

    $this->assertContains('"foo" is not a valid email address', $messages);
    $this->assertContains('int_field should be an integer', $messages);
  }

  public function testTranslateValidationExceptionAndIndexMessagesByFieldSucceeds()
  {
    $translator = new ValidationErrorTranslator($this->getLanguageManager());

    $errors = array(
      new ValidationError('email_field', array('email'), 'foo'),
      new ValidationError('int_field', array('type', 'notblank'), ''),
    );

    $exception = new ValidationException(new TestTranslatorModel(), $errors);

    $messages = $translator->translateValidationException($exception, true);

    $this->assertArrayHasKey('email_field', $messages);
    $emailMessages = $messages['email_field'];
    $this->assertInternalType('array', $emailMessages);
    $this->assertContains('"foo" is not a valid email address', $emailMessages);

    $this->assertArrayHasKey('int_field', $messages);
    $intMessages = $messages['int_field'];
    $this->assertInternalType('array', $intMessages);
    $this->assertContains('int_field should be an integer', $intMessages);
    $this->assertContains('int_field should not be blank', $intMessages);
  }
}
