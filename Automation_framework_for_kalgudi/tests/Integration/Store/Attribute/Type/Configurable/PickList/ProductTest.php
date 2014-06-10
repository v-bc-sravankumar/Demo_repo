<?php

namespace Integration\Store\Attribute\Type\Configurable\PickList;

use Test\FixtureTest;

/**
 * @group nosample
 */
class ProductTest extends FixtureTest
{
    private $createdEntities = array();

    public function testCreateValueFromCsvDataWithProductId()
    {
        $products = $this->loadFixture('products');
        $product = $products[array_rand($products)];

        $picklist = new \Store_Attribute_Type_Configurable_PickList_Product();

        $attribute = new \Store_Attribute(); 
        $attribute->setName('Test Attribute')->setType($picklist)->save();

        $expectedAttributeValue = new \Store_Attribute_Value(); 
        $expectedAttributeValue->setAttribute($attribute)->setLabel($product->getId())->save();

        $returnedAttributeValue = $picklist->createValueFromCsvData($attribute, $product->getId());
        $this->assertEquals($product->getId(), $returnedAttributeValue->getValueData()->getProductId());

        $attribute->delete();
    }

    public function testCreateValueFromCsvDataWithProductName()
    {
        $products = $this->loadFixture('products');
        $product = $products[array_rand($products)];

        $picklist = new \Store_Attribute_Type_Configurable_PickList_Product();

        $attribute = new \Store_Attribute(); 
        $attribute->setName('Test Attribute')->setType($picklist)->save();

        $expectedAttributeValue = new \Store_Attribute_Value(); 
        $expectedAttributeValue->setAttribute($attribute)->setLabel($product['prodname'])->save();

        $returnedAttributeValue = $picklist->createValueFromCsvData($attribute, $product->getId());
        $this->assertEquals($product->getId(), $returnedAttributeValue->getValueData()->getProductId());

        $attribute->delete();
    }

    /**
     * @expectedException \Store_Exception_ControlPanelSafe
     */
    public function testCreateValueFromCsvDataForProductThatDoesntExist()
    {
        $picklist = new \Store_Attribute_Type_Configurable_PickList_Product();

        $attribute = new \Store_Attribute(); 
        $attribute->setName('Test Attribute')->setType($picklist)->save();

        $attributeValue = new \Store_Attribute_Value(); 
        $attributeValue->setAttribute($attribute)->setLabel('Some Label')->save();

        $this->createdEntities[] = $attribute;

        $picklist->createValueFromCsvData($attribute, uniqid());
    }

    public function tearDown()
    {
        foreach ($this->createdEntities as $entity) {
            $entity->delete();
        }

        parent::tearDown();
    }
}
