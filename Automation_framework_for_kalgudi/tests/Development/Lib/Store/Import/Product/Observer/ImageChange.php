<?php
class Unit_Lib_Store_Import_Observer_ImageChange extends Interspire_UnitTest
{

	public function createSettings($importSession = array())
	{

		$defaultImportSession = array(
			'OverrideDuplicates' => true,
			'IsBulkEdit' => true,
			'DeleteImages' => true,
			'DeleteDownloads' => true,
			'IgnoreBlankFields' => true,
			'AutoCategory' => true,
			'CategoryId' => 0

		);
		// merge in the importSession to the default
		foreach ($importSession as $key => $value) {
			$defaultImportSession[$key] = $value;
		}

		return new Store_Import_Product_Settings($defaultImportSession);

	}

	public function testNoChange()
	{

		$record = array (
			'productid' => 9,
	  		'prodimageid1' => '66',
	  		'prodimagefile1' => 'sample_images/eye250__84327.jpg',
	  		'prodimagedescription1' => '',
	  		'prodimageisthumb1' => 'Y',
	  		'prodimagesort1' => '0',
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_ImageChange, Store_Import_Product_Observer_ImageChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');

	}

	public function testRemovedImageFile()
	{

		$record = array (
				'productid' => 9,
		  		'prodimageid1' => '66',
		  		'prodimagefile1' => '', // sample_images/eye250__84327.jpg
		  		'prodimagedescription1' => 'desc',
		  		'prodimageisthumb1' => 'Y',
		  		'prodimagesort1' => '0',
		);

		$expected =array (
			'productId' => 9,
			'productName' => '[Sample Product] Elgato EyeTV 250 Plus Digital TV Recorder',
			'changes' => array (
				'66' => array(
					'prodimagefile' => array (
						'before' => 'sample_images/eye250__84327.jpg',
						'after' => '',
					),
					'prodimagedescription' => array (
						'before' => '',
						'after' => 'desc',
					),
				),
			),
		);


		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_ImageChange, Store_Import_Product_Observer_ImageChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings(array('IgnoreBlankFields' => false)));
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_ImageChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testChangedImageFile()
	{

		$record = array (
					'productid' => 9,
			  		'prodimageid1' => '66',
			  		'prodimagefile1' => 'sample_images/new-image.jpg',
			  		'prodimagedescription1' => '',
			  		'prodimageisthumb1' => 'Y',
			  		'prodimagesort1' => '0',
		);

		$expected =array (
				'productId' => 9,
				'productName' => '[Sample Product] Elgato EyeTV 250 Plus Digital TV Recorder',
				'changes' => array (
					'66' => array(
						'prodimagefile' => array (
							'before' => 'sample_images/eye250__84327.jpg',
							'after' => 'sample_images/new-image.jpg',
						),
					)
				),
		);


		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_ImageChange, Store_Import_Product_Observer_ImageChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_ImageChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testAddedDescription()
	{

		$record = array (
					'productid' => 9,
			  		'prodimageid1' => '66',
			  		'prodimagefile1' => 'sample_images/eye250__84327.jpg',
			  		'prodimagedescription1' => 'desc',
			  		'prodimageisthumb1' => 'Y',
			  		'prodimagesort1' => '0',
		);

		$expected =array (
				'productId' => 9,
				'productName' => '[Sample Product] Elgato EyeTV 250 Plus Digital TV Recorder',
				'changes' => array(
					'66' => array (
						'prodimagedescription' => array (
							'before' => '',
							'after' => 'desc',
						),
					),
				),
		);


		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_ImageChange, Store_Import_Product_Observer_ImageChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_ImageChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testChangedImageSort()
	{

		$record = array (
						'productid' => 9,
				  		'prodimageid1' => '66',
				  		'prodimagefile1' => 'sample_images/eye250__84327.jpg',
				  		'prodimagedescription1' => '',
				  		'prodimageisthumb1' => 'Y',
				  		'prodimagesort1' => '8',
		);

		$expected = array (
					'productId' => 9,
					'productName' => '[Sample Product] Elgato EyeTV 250 Plus Digital TV Recorder',
					'changes' => array (
						'66' => array(
							'prodimagesort' => array (
								'before' => '0',
								'after' => '8',
							),
						),
					),
		);


		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_ImageChange, Store_Import_Product_Observer_ImageChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_ImageChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

}