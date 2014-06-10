<?php
class Unit_Email_State extends PHPUnit_Framework_TestCase
{
	const FROM_ADDR = "from@example.com";
	const FROM_NAME = "Example Sender";
	const TO_ADDR = "to@example.com";
	const TO_NAME = "Example Recipient";
	const TO_FORMAT = "h";
	const SUBJECT = "Test Subject";
	const CHAR_SET = "utf-8";
	const BODY = "<h1>Test Message</h1>";
	const MB_CHAR = "♥";

	protected function buildEmail()
	{
		$email = new \Email\Api();
		$email->From(self::FROM_ADDR, self::FROM_NAME);
		$email->AddRecipient(self::TO_ADDR, self::TO_NAME, self::TO_FORMAT);
		$email->AddBody("html", self::BODY);
		$email->Set("Subject", self::SUBJECT);
		$email->Set("CharSet", self::CHAR_SET);
		return $email;
	}

	public function testGetState()
	{
		$email = $this->buildEmail();
		$state = $email->getState();

		$this->assertEquals($state['body']['h'], self::BODY);
		$this->assertEquals($state['ReplyTo'], self::FROM_ADDR);
		$this->assertEquals($state['BounceAddress'], self::FROM_ADDR);
		$this->assertEquals($state['FromAddress'], self::FROM_ADDR);
		$this->assertEquals($state['FromName'], self::FROM_NAME);
		$this->assertEquals($state['_Recipients'][0]['address'], self::TO_ADDR);
		$this->assertEquals($state['_Recipients'][0]['name'], self::TO_NAME);
		$this->assertEquals($state['_Recipients'][0]['format'], self::TO_FORMAT);
		$this->assertEquals($state['Subject'], self::SUBJECT);
		$this->assertEquals($state['CharSet'], self::CHAR_SET);
	}

	public function testRestoreFromState()
	{
		$origEmail = $this->buildEmail();
		$state = $origEmail->getState();
		$emailFromState = new \Email\Api($state);

		$this->assertEquals($origEmail, $emailFromState);
	}

	/**
	 * Test whether an email can be restored from a state object that has been (de)serialised from JSON.
	 * This is mainly to ensure that it can survive the trip through Resque.
	 */
	public function testJsonSerialisation()
	{
		$origEmail = $this->buildEmail();
		$state = $origEmail->getState();
		$encodedState = json_encode($state);
		$decodedState = json_decode($encodedState, true);
		$emailFromState = new \Email\Api($decodedState);

		$this->assertEquals($origEmail, $emailFromState);
	}

	public function testVariableWidthEncoding()
	{
		$origEmail = $this->buildEmail();
		$origEmail->Set("Subject", self::MB_CHAR);
		$state = $origEmail->getState();
		$encodedState = json_encode($state);
		$decodedState = json_decode($encodedState, true);
		$emailFromState = new \Email\Api($decodedState);

		$this->assertEquals(self::MB_CHAR, $emailFromState->Get("Subject"));
	}
}
