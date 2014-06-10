<?php

namespace Integration\Test;

use Test\FixtureTest;

/**
 * @group nosample
 */
class FixtureHelperTest extends FixtureTest
{
    public function testLoadDataModelFixture()
    {
        $brands = $this->loadFixture('brands');
        $this->assertEquals(20, count($brands));

        foreach ($brands as $brand) {
            $this->assertInstanceOf('\Store_Brand', $brand);
        }
    }

    public function testLoadTableRowFixture()
    {
        $products = $this->loadFixture('products');
        $this->assertEquals(30, count($products));

        foreach ($products as $product) {
            $this->assertInstanceOf('\Test\RowFixture', $product);
        }
    }
}
