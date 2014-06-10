<?php
class Unit_Lib_Store_Import_Observer_CategoryChange extends Interspire_UnitTest
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

	public function _testMappingNameToArray()
	{
		$expected[1] = array (
					'productCategoryID' => 1,
					'productCategoryName' => 'Shop Mac',
					'productCategoryPath' => 'Shop Mac',
		);

		$expected[9] = array (
			'productCategoryID' => 9,
			'productCategoryName' => 'Software',
			'productCategoryPath' => 'Shop Mac/Software',
		);

		$actual = Store_Category_Mapper::BuildCategoryListFromPath('Shop Mac/Software');

		$this->assertEquals($expected, $actual, 'Expected did not match actual');


	}

// 	public function testMappingName1()
// 	{
// 		$name = 'Shop iPod/Accessories';
// 		$actual = Store_Category_Mapper::BuildCategoryListFromPath($name);
// 		var_export($actual);
// 	}

// 	public function testMappingName2()
// 	{
// 		$name = 'Shop iPhone/Accessories';
// 		$actual = Store_Category_Mapper::BuildCategoryListFromPath($name);
// 		var_export($actual);
// 	}

	public function testNoChange()
	{
		$record = array(
					'productid' => 10,
					'category' => 'Shop Mac/Software',
		);
		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_CategoryChange, 'categoryChange');
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$this->assertArrayIsEmpty($reports, 'There should be no reports returned as there has been no change');

	}

	public function testAdded()
	{

		$expected = array (
			0 => array (
			    'productCategoryID' => null,
			    'productCategoryName' => 'I am new',
			    'productCategoryPath' => 'I am new',
			),
			1 => array (
			    'productCategoryID' => null,
			    'productCategoryName' => 'I am also new',
			    'productCategoryPath' => 'I am also new',
			),
			2 => array (
			    'productCategoryID' => '7',
			    'productCategoryName' => 'Accessories',
			    'productCategoryPath' => 'Shop iPhone/Accessories',
			)
		);

		$record = array(
						'productid' => 10,
						'category' => 'Shop Mac/Software;I am new;I am also new;Shop iPhone/Accessories',
		);
		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_CategoryChange, 'categoryChange');
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$observer = $importProductRow->getObserver('categoryChange');

		$actual = $observer->getNewCategories();

		$this->assertEquals($expected, $actual);


	}

	public function testRemoved()
	{

		$expected[] = array (
			'productCategoryID' => '8',
			'productCategoryName' => 'Accessories',
			'productCategoryPath' => 'Shop iPod/Accessories',
		);

		// Original: Shop iPhone/Accessories;Shop iPod/Accessories (iPod gets removed)

		$record = array(
			'productid' => 23,
			'category' => 'Shop iPhone/Accessories',
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_CategoryChange, Store_Import_Product_Observer_CategoryChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();

		$reports = $importProductRow->getObservationReports();

		$observer = $importProductRow->getObserver(Store_Import_Product_Observer_CategoryChange::$reportLabel);

		$removed = $observer->getRemovedCategories();

		$this->assertEquals($expected, $removed);


	}

	public function testAddedAndRemoved()
	{

		$expectedRemoved[] = array (
						'productCategoryID' => '7',
						'productCategoryName' => 'Accessories',
						'productCategoryPath' => 'Shop iPhone/Accessories',
		);

		$expectedAdded[] = array (
						'productCategoryID' => 1,
						'productCategoryName' => 'Shop Mac',
						'productCategoryPath' => 'Shop Mac',
		);

		// Original: Shop iPhone/Accessories;Shop iPod/Accessories (iPhone gets removed)

		$record = array(
			'productid' => 23,
			'category' => 'Shop iPod/Accessories;Shop Mac',
		);

		$importProductRow = new Store_Import_Product_Row($record);
		$importProductRow->attachObserver(new Store_Import_Product_Observer_CategoryChange, Store_Import_Product_Observer_CategoryChange::$reportLabel);
		$importProductRow->setImportSettings($this->createSettings());
		$importProductRow->notifyObservers();
		$reports = $importProductRow->getObservationReports();

		$observer = $importProductRow->getObserver(Store_Import_Product_Observer_CategoryChange::$reportLabel);

// 		var_export($observer);

		$removed = $observer->getRemovedCategories();
		$this->assertEquals($expectedRemoved, $removed);

		$added = $observer->getNewCategories();
		$this->assertEquals($expectedAdded, $added);

	}



}