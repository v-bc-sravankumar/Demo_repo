<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher;

use Store\Search\Searcher\Storefront\StoreSearcher\ProductFilterMapper;
use Store_Category_Tree;
use Bigcommerce\SearchClient\Document\ProductDocument;

class ProductFilterMapperTest extends \PHPUnit_Framework_TestCase
{
    private function getShopper()
    {
        return $this->getMock('\Store_Shopper', array('getPriceFieldForProductSearching'));
    }

    private function getMapper()
    {
        $shopper = $this->getShopper();
        return new ProductFilterMapper($shopper, new Store_Category_Tree());
    }

    public function testPriceMapsToPriceFieldForShopper()
    {
        $shopper = $this->getShopper();
        $shopper
            ->expects($this->once())
            ->method('getPriceFieldForProductSearching')
            ->will($this->returnValue('prices.calculated'));

        $mapper = new ProductFilterMapper($shopper, new Store_Category_Tree());

        $filters = array(
            'price' => 123.45,
        );

        $mappedFilters = $mapper->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $this->assertEquals('prices.calculated', $filter->getField());
        $this->assertEquals(123.45, $filter->getValue());
    }

    public function emptyNumericOptionDataProvider()
    {
        return array(
            array('featured'),
            array('shipping'),
        );
    }

    /**
     * @dataProvider emptyNumericOptionDataProvider
     */

    public function testNumericFieldWithEmptyIsntMapped($filter)
    {
        $filters = array(
            $filter => 0,
        );

        $mappedFilters = $this->getMapper()->map($filters);

        $this->assertEquals(0, count($mappedFilters));
    }

    public function numericOptionDataProvider()
    {
        return array(
            array(1, true),
            array(2, false),
        );
    }

    /**
     * @dataProvider numericOptionDataProvider
     */
    public function testFeaturedMapsToIsFeatured($featured, $expected)
    {
        $filters = array(
            'featured' => $featured,
        );

        $mappedFilters = $this->getMapper()->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $this->assertEquals('is_featured', $filter->getField());
        $this->assertEquals($expected, $filter->getValue());
    }

    /**
     * @dataProvider numericOptionDataProvider
     */
    public function testShippingMapsToHasFreeShipping($shipping, $expected)
    {
        $filters = array(
            'shipping' => $shipping,
        );

        $mappedFilters = $this->getMapper()->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $this->assertEquals('has_free_shipping', $filter->getField());
        $this->assertEquals($expected, $filter->getValue());
    }

    public function testBrandMapsToBrandId()
    {
        $filters = array(
            'brand' => '4',
        );

        $mappedFilters = $this->getMapper()->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $this->assertEquals('brand_id', $filter->getField());
        $this->assertEquals(4, $filter->getValue());
    }

    public function testEmptyBrandIsntMapped()
    {
        $filters = array(
            'brand' => 0,
        );

        $mappedFilters = $this->getMapper()->map($filters);

        $this->assertEquals(0, count($mappedFilters));
    }

    public function testPriceFromMapsToPriceRange()
    {
        $shopper = $this->getShopper();
        $shopper
            ->expects($this->once())
            ->method('getPriceFieldForProductSearching')
            ->will($this->returnValue('prices.sale'));

        $mapper = new ProductFilterMapper($shopper, new Store_Category_Tree());

        $filters = array(
            'price_from' => '393.12',
        );

        $mappedFilters = $mapper->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $this->assertEquals('prices.sale', $filter->getField());
        $this->assertEquals(393.12, $filter->getMin());
        $this->assertNull($filter->getMax());
    }

    public function testPriceToMapsToPriceRange()
    {
        $shopper = $this->getShopper();
        $shopper
            ->expects($this->once())
            ->method('getPriceFieldForProductSearching')
            ->will($this->returnValue('prices.sale'));

        $mapper = new ProductFilterMapper($shopper, new Store_Category_Tree());

        $filters = array(
            'price_to' => '1131.76',
        );

        $mappedFilters = $mapper->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $this->assertEquals('prices.sale', $filter->getField());
        $this->assertNull($filter->getMin());
        $this->assertEquals(1131.76, $filter->getMax());
    }

    public function testPriceFromAndPriceToMapsToPriceRange()
    {
        $shopper = $this->getShopper();
        $shopper
            ->expects($this->once())
            ->method('getPriceFieldForProductSearching')
            ->will($this->returnValue('prices.price'));

        $mapper = new ProductFilterMapper($shopper, new Store_Category_Tree());

        $filters = array(
            'price_from' => '839.06',
            'price_to' => '1000.32',
        );

        $mappedFilters = $mapper->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $this->assertEquals('prices.price', $filter->getField());
        $this->assertEquals(839.06, $filter->getMin());
        $this->assertEquals(1000.32, $filter->getMax());
    }

    public function testCategoriesMapsToCategoryId()
    {
        $filters = array(
            'categories' => array(3,'11',32,3,'100'),
        );

        $mappedFilters = $this->getMapper()->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $this->assertEquals('category_ids', $filter->getField());
        $this->assertEquals(array(3,11,32,100), array_values($filter->getTerms()));
    }

    public function testCategoriesWithSearchSubCategoriesMapsToCategoryId()
    {
        $shopper = $this->getShopper();

        $categoryTree = $this->getMock('\Store_Category_Tree', array('getDescendants'));
        $categoryTree
            ->expects($this->at(0))
            ->method('getDescendants')
            ->with($this->equalTo(array('categoryid')), $this->equalTo(1))
            ->will($this->returnValue(array(
                array('categoryid' => '5'),
                array('categoryid' => 6),
                array('categoryid' => 7),
            )));

        $categoryTree
            ->expects($this->at(1))
            ->method('getDescendants')
            ->with($this->equalTo(array('categoryid')), $this->equalTo(2))
            ->will($this->returnValue(array(
                array('categoryid' => '1'),
                array('categoryid' => 6),
                array('categoryid' => 8),
            )));

        $categoryTree
            ->expects($this->at(2))
            ->method('getDescendants')
            ->with($this->equalTo(array('categoryid')), $this->equalTo(3))
            ->will($this->returnValue(array(
                array('categoryid' => 4),
                array('categoryid' => '6'),
                array('categoryid' => 9),
            )));

        $mapper = new ProductFilterMapper($shopper, $categoryTree);

        $filters = array(
            'categories' => array(1,2,3),
            'searchsubs' => '1',
        );

        $mappedFilters = $mapper->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $filter = $mappedFilters[0];

        $terms = $filter->getTerms();
        sort($terms);

        $this->assertEquals('category_ids', $filter->getField());
        $this->assertEquals(range(1,9), $terms);
    }

    public function testInStockMapsToInventoryLevel()
    {
        $filters = array(
            'instock' => '1',
        );

        $mappedFilters = $this->getMapper()->map($filters);

        $this->assertEquals(1, count($mappedFilters));

        $expectedFilter = "((inventory_level >= 1 AND NOT inventory_tracking = none) OR inventory_tracking = none)";
        $this->assertEquals($expectedFilter, (string)$mappedFilters[0]);
    }
}
