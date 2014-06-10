<?php
class Unit_Lib_Store_Import_Observer_GPSChange extends Interspire_UnitTest
{

	/*
	 * @todo need to insert some GPS field data fixtures for this product.
	 */

	public function getImportRecord()
	{
		return array (
			'productid' => 9,
			'gpsGlobalTradeItemNumber' => '1',
			'gpsManufacturerPartNumber' => '19',
			'gpsGender' => 'male',
			'gpsAgeGroup' => 'adult',
			'gpsColor' => 'Red',
			'gpsSize' => 'Large',
			'gpsMaterial' => 'Atoms',
			'gpsPattern' => 'Patterny',
			'gpsItemGroupId' => null,
			'gpsCategory' => 'Shop Mac/Video Devices',
			'gpsGooglePsEnabled' => 'Y',
		);
	}

	public function getExpectedReportMessage()
	{
		return array (
			'productId' => 9,
			'productName' => '[Sample Product] Elgato EyeTV 250 Plus Digital TV Recorder',
			'changes' => array (),
		);
	}

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

	public function _testNoChange()
	{

		$record = $this->getImportRecord();

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_GoogleProductSearchChange, Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');
	}

	public function testChangeGtin()
	{

		$record = $this->getImportRecord();
		$record['gpsGlobalTradeItemNumber'] = 999;

		$expected = $this->getExpectedReportMessage();
		$expected['changes'] = array(
			'gpsGlobalTradeItemNumber' => array(
				'before' => 1,
				'after' => 999
			)
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_GoogleProductSearchChange, Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testChangeMpn()
	{

		$record = $this->getImportRecord();
		$record['gpsManufacturerPartNumber'] = 1001;

		$expected = $this->getExpectedReportMessage();
		$expected['changes'] = array(
			'gpsManufacturerPartNumber' => array(
				'before' => 19,
				'after' => 1001
			)
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_GoogleProductSearchChange, Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testChangeGender()
	{

		$record = $this->getImportRecord();
		$record['gpsGender'] = 'female';

		$expected = $this->getExpectedReportMessage();
		$expected['changes'] = array(
			'gpsGender' => array (
				'before' => 'male',
				'after' => 'female',
			)
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_GoogleProductSearchChange, Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testChangeAgeGroup()
	{

		$record = $this->getImportRecord();
		$record['gpsAgeGroup'] = '';

		$expected = $this->getExpectedReportMessage();
		$expected['changes'] = array(
			'gpsAgeGroup' => array (
				'before' => 'adult',
				'after' => '',
		)
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_GoogleProductSearchChange, Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings(array('IgnoreBlankFields' => false)));
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testChangeCategory()
	{

		$record = $this->getImportRecord();
		$record['gpsCategory'] = 'Shop Mac/Software'; // Change the category string to something different.

		$expected = $this->getExpectedReportMessage();
		$expected['changes'] = array(
			'gpsCategory' => array (
				'before' => 'Shop Mac/Video Devices',
				'after' => 'Shop Mac/Software',
			)
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_GoogleProductSearchChange, Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);
	}

	public function testNewCategory()
	{

		$record = $this->getImportRecord();
		$record['gpsCategory'] = 'New category'; // Change the category string to something different.

		$expected = $this->getExpectedReportMessage();
		$expected['changes'] = array(
				'gpsCategory' => array (
					'before' => 'Shop Mac/Video Devices',
					'after' => 'New category',
		)
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_GoogleProductSearchChange, Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_GoogleProductSearchChange::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}


}