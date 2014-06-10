<?php
class Unit_Lib_Store_Import_Observer_SKUInventoryChange extends Interspire_UnitTest
{

	protected function getImportRecord()
	{
		return array(
			'id' => 5,
			'sku' => 'MB-1',
			'name' => '[Sample Product] iPod Shuffle',
			'productid' => 1,
			'prodcurrentinv' => 5
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

	public function testNoChange()
	{

		$record = $this->getImportRecord();

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_SKUInventoryUpdate(), Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');

	}

	public function testChange()
	{

		$record = $this->getImportRecord();
		$record['prodcurrentinv'] = 3;

		$expected = array(
			'id' => 1,
			'productId' => $record['id'],
			'productName' => $record['name'],
			'SKU' => $record['sku'],
			'before' => 5,
			'after' => 3
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_SKUInventoryUpdate(), Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testChangeZero()
	{

		$record = $this->getImportRecord();
		$record['prodcurrentinv'] = 0;

		$expected = array(
			'id' => 1,
			'productId' => $record['id'],
			'productName' => $record['name'],
			'SKU' => $record['sku'],
			'before' => 5,
			'after' => 0
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_SKUInventoryUpdate(), Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testNoChangeIgnoreBlankFieldOff()
	{

		$record = $this->getImportRecord();

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_SKUInventoryUpdate(), Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');

	}

	public function testChangeIgnoreBlankFieldOff()
	{

		$record = $this->getImportRecord();
		$record['prodcurrentinv'] = 2;

		$expected = array(
			'id' => 1,
			'productId' => $record['id'],
			'productName' => $record['name'],
			'SKU' => $record['sku'],
			'before' => 5,
			'after' => 2
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_SKUInventoryUpdate(), Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings(array('IgnoreBlankFields' => false)));
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

	public function testChangeZeroIgnoreBlankFieldOff()
	{

		$record = $this->getImportRecord();
		$record['prodcurrentinv'] = 0;

		$expected = array(
			'id' => 1,
			'productId' => $record['id'],
			'productName' => $record['name'],
			'SKU' => $record['sku'],
			'before' => 5,
			'after' => 0
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_SKUInventoryUpdate(), Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings(array('IgnoreBlankFields' => false)));
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$actual = $reports[Store_Import_Product_Observer_SKUInventoryUpdate::$reportLabel]->getMessage();

		$this->assertEquals($expected, $actual);

	}

}