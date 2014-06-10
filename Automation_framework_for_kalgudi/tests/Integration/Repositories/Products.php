<?php

class Integration_Repositories_Products extends PHPUnit_Framework_TestCase
{

    private $repo = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Interspire_DataFixtures::getInstance()
            ->loadData('product_attribute_combinations', 'product_attribute_combination_values', 'product_attributes', 'attribute_values');
    }

    public function setUp()
    {
        $this->repo = new \Repository\Products();
    }

    public function tearDown()
    {
//        Interspire_DataFixtures::getInstance()
//            ->removeData('product_attribute_combinations', 'product_attribute_combination_values', 'product_attributes', 'attribute_values');
    }

    public function testGetProductCombinationRaw()
    {
        $productsRepository = new ReflectionClass('\Repository\Products');

        $method = $productsRepository->getMethod("getProductCombinationRaw");
        $method->setAccessible(true);
        $data = $method->invoke($this->repo, array(1000, 1001));
        $expectedData = array(
            1025 => array(
                'product_id' => 1000,
                'lazy_fetch' => 1
            ),
            1100 => array(
                'product_id' => 1001,
                'id' => 1100,
                'inventory_warning_level' => 0,
                'inventory_level' => 0,
                'options' => array(
                    array('option' => array('display_name' => 'Attribute 1'), 'value' => array('label' => 'value 1')),
                    array('option' => array('display_name' => 'Attribute 2'), 'value' => array('label' => 'value 2')),
                )
            )
        );
        $this->assertEquals($expectedData, $data);
    }

}
