<?php

namespace Tests\Unit\Template;

class MasterSwitchTest extends \PHPUnit_Framework_TestCase
{
	private function getTemplateInstance()
	{
		return new \TEMPLATE('ISC_LANG');
	}

	private function setStandaloneGlobal($flag)
	{
		$GLOBALS['TPL_CFG']['Standalone'] = $flag;
	}

	public function tearDown()
	{
		unset($GLOBALS['TPL_CFG']['Standalone']);
	}

	public function testInheritanceAvailableByDefault()
	{
		$template = $this->getTemplateInstance();

		$this->assertFalse($template->isStandalone());
	}

	public function testStandaloneSwitchTurnsOnInheritance()
	{
		$template = $this->getTemplateInstance();

		$this->setStandaloneGlobal(false);

		$this->assertFalse($template->isStandalone());
	}

	public function testStandaloneSwitchTurnsOffInheritance()
	{
		$template = $this->getTemplateInstance();

		$this->setStandaloneGlobal(true);

		$this->assertTrue($template->isStandalone());
	}
}