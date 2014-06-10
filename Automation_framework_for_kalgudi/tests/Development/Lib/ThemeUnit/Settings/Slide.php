<?php

class Unit_Lib_Theme_Settings_Slide extends Interspire_UnitTest
{
	/**
	 * @var Theme_Settings_Slide
	 */
	private $_slide;

	public function setUp()
	{
		parent::setUp();
		$this->_slide = new Theme_Settings_Slide();
		$this->_slide->setName('test-slide');
	}

	public function testGetHeadingEqualsHeadingSet()
	{
		$heading = new Theme_Settings_Text();
		$this->_slide->setHeading($heading);
		$this->assertTrue($heading->sameValueAs($this->_slide->getHeading()));
	}

	public function testGetTextEqualsTextSet()
	{
		$text = new Theme_Settings_Text('Test', '#444444');
		$this->_slide->setText($text);
		$this->assertTrue($text->sameValueAs($this->_slide->getText()));
	}

	public function testGetLinkEqualsLinkSet()
	{
		$link = new Theme_Settings_Link('/test', Theme_Settings_Link::INTERNAL);
		$this->_slide->setLink($link);
		$this->assertTrue($link->sameValueAs($this->_slide->getLink()));
	}

}
