<?php

class Integration_Lib_Theme_Settings_Image extends Interspire_IntegrationTest
{

	/**
	 * A collection of {@link DataModel_Record}s that should be deleted from the database during {@link tearDown()}.
	 *
	 * @var array
	 */
	private $_recordsCreated = array();

	public function testNewImageSavesSuccessfully()
	{
		$image = new Theme_Settings_Image();
		$result = $image
			->setFilePath('images/test')
			->setName('test-image')
			->setTheme('MyTheme')
			->setAlternateText('Alternate text')
			->setTemporary(true)
			->save();
		$this->assertTrue($result);
		$this->_recordsCreated[] = $image;
		$id = $image->getId();
		$savedImage = Theme_Settings_Image::find($id)->first();
		$this->assertInstanceOf('Theme_Settings_Image', $savedImage);

	}

	public function tearDown()
	{
		foreach ($this->_recordsCreated as $record) {
			$record->delete();
		}
	}
}
