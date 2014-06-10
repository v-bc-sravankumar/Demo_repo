<?php

class Integration_Lib_Theme_Settings_SlideShow extends Interspire_IntegrationTest
{

	/**
	 * A collection of {@link DataModel_Record}s that should be deleted from the database during {@link tearDown()}.
	 *
	 * @var array
	 */
	private $_recordsCreated = array();

	/**
	 * @var Theme_Settings_SlideShow
	 */
	private $_slideShow;

	public function setUp()
	{
		parent::setUp();
		$this->_slideShow = new Theme_Settings_SlideShow();
		$this->_slideShow
			->setTheme('MyTheme')
			->setSwapFrequency(5)
			->setName('test-slide-show');
	}

	public function testEmptySlideShowSavesSuccessfully()
	{
		$this->_slideShow->save();
		$this->_recordsCreated[] = $this->_slideShow;
		$id = $this->_slideShow->getId();

		$savedSlideShow = Theme_Settings_SlideShow::find($id)->first();

		$this->assertSlideShowPropertiesMatch($this->_slideShow, $savedSlideShow);
	}

	public function testPopulatedSlideShowSavesSuccessfully()
	{
		$slide = new Theme_Settings_Slide();
		$slide->setName('test-slide');
		$this->_slideShow->addSlide($slide);
		$success = $this->_slideShow->save();
		$this->_recordsCreated[] = $this->_slideShow;
		$this->_recordsCreated[] = $slide;
		$id = $this->_slideShow->getId();
		$this->assertTrue($success);

		$savedSlideShow = Theme_Settings_SlideShow::find($id)->first();
		$this->assertSlideShowPropertiesMatch($this->_slideShow, $savedSlideShow);
	}

	public function tearDown()
	{
		foreach ($this->_recordsCreated as $record) {
			$record->delete();
		}
	}

	/**
	 * @param Theme_Settings_SlideShow $expected
	 * @param Theme_Settings_SlideShow $actual
	 * @param string $message
	 */
	public function assertSlideShowPropertiesMatch($expected, $actual, $message = '')
	{
		if (empty($message)) {
			$message = 'Failed asserting that slide show properties match';
		}
		$this->assertInstanceOf('Theme_Settings_SlideShow', $actual);
		$this->assertEquals($expected->getName(), $actual->getName(), $message);
		$this->assertEquals($expected->getTheme(), $actual->getTheme(), $message);
		$this->assertEquals($expected->getSwapFrequency(), $actual->getSwapFrequency(), $message);
		$this->assertEquals(count($expected), count($actual));
	}


}
