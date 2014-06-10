<?php

class Integration_Lib_Theme_Settings_Slide extends Interspire_IntegrationTest
{
	/**
	 * A collection of {@link DataModel_Record}s that should be deleted from the database during {@link tearDown()}.
	 *
	 * @var array
	 */
	private $_recordsCreated = array();

	/**
	 * @var Theme_Settings_Slide
	 */
	private $_slide;

	public function setUp()
	{
		parent::setUp();

		$this->_slide = new Theme_Settings_Slide();
		$this->_slide
			->setTheme('MyTheme')
			->setName('test-slide');
	}

	public function testEmptySlideSavesSuccessfully()
	{
		$success = $this->_slide->save();
		$this->_recordsCreated[] = $this->_slide;
		$this->assertTrue($success);
		$id = $this->_slide->getId();
		$savedSlide = Theme_Settings_Slide::find($id)->first();
		$this->assertSlidePropertiesMatch($this->_slide, $savedSlide);
	}

	public function testSlideWithTextSavesSuccessfully()
	{
		$text = new Theme_Settings_Text('Test', '#FFFFFF');
		$this->_slide->setText($text);
		$success = $this->_slide->save();
		$this->_recordsCreated[] = $this->_slide;
		$id = $this->_slide->getId();
		$this->assertTrue($success);

		$savedSlide = Theme_Settings_Slide::find($id)->first();
		$this->assertSlidePropertiesMatch($this->_slide, $savedSlide);
	}

	public function testSlideWithLinkSavesSuccessfully()
	{
		$link = new Theme_Settings_Link('/');
		$this->_slide->setLink($link);
		$success = $this->_slide->save();
		$this->_recordsCreated[] = $this->_slide;
		$id = $this->_slide->getId();
		$this->assertTrue($success);

		$savedSlide = Theme_Settings_Slide::find($id)->first();
		$this->assertSlidePropertiesMatch($this->_slide, $savedSlide);
	}

	public function testDetachedImagesAreDeleted()
	{
		$image1 = new Theme_Settings_Image();
		$image1->setName('text1');
		$image2 = new Theme_Settings_Image();
		$image2->setName('text2');
		$image3 = new Theme_Settings_Image();
		$image3->setName('text3');

		$this->_slide->setImage($image1);
		$this->_slide->save();
		$this->_recordsCreated[] = $this->_slide;
		$this->_recordsCreated[] = $image1;

		$detachedImageId = $image1->getId();

		$this->_slide->setImage($image2);
		$this->_slide->setImage($image3);

		$this->_slide->save();

		$this->assertFalse(Theme_Settings_Image::find($detachedImageId)->first());
	}

	public function tearDown()
	{
		foreach ($this->_recordsCreated as $record) {
			$record->delete();
		}
	}

	/**
	 * @param Theme_Settings_Slide $expected
	 * @param Theme_Settings_Slide $actual
	 * @param string $message
	 */
	public function assertSlidePropertiesMatch($expected, $actual, $message = '')
	{
		if (empty($message)) {
			$message = 'Failed asserting that slide properties match';
		}
		$this->assertInstanceOf('Theme_Settings_Slide', $actual);
		$this->assertEquals($expected->getName(), $actual->getName(), $message);
		$this->assertEquals($expected->getTheme(), $actual->getTheme(), $message);
		$this->assertEquals($expected->getImageId(), $actual->getImageId());
		$this->assertEquals($expected->getLink(), $actual->getLink());
		$this->assertEquals($expected->getText(), $actual->getText());
	}
}
