<?php

namespace Integration\Store\Product;

use Store_Product_Gateway;
use Test\FixtureTest;

/**
 * @group nosample
 */
class GatewayTest extends FixtureTest
{
    public function tearDown()
    {
        $this->getDb()->Query('TRUNCATE product_tax_pricing');
        parent::tearDown();
    }

    private function getMinimalProduct()
    {
        $categories = array(
            $this->getFaker()->randomNumber(1),
            $this->getFaker()->randomNumber(1),
            $this->getFaker()->randomNumber(1),
            $this->getFaker()->randomNumber(1),
        );

        // these the minimum required fields for creating a product via the API
        return array(
            'prodname'           => $this->getFaker()->unique()->sentence(),
            'prodprice'          => $this->getFaker()->randomFloat(0,4),
            'prodcats'           => $categories,
            'prodtype'           => $this->getFaker()->randomNumber(1,2),
            'prodallowpurchases' => $this->getFaker()->randomNumber(0,1),
            'prodweight'         => $this->getFaker()->randomFloat(0,4),
        );
    }

    public function testAddMinimalProductSetsDefaults()
    {
        $product = $this->getMinimalProduct();

        $expected = array(
            'prodname'                       => $product['prodname'],
            'prodprice'                      => $product['prodprice'],
            'prodcatids'                     => implode(',', $product['prodcats']),
            'prodtype'                       => $product['prodtype'],
            'prodallowpurchases'             => $product['prodallowpurchases'],
            'prodweight'                     => $product['prodweight'],
            'prodcode'                       => '',
            'proddesc'                       => '',
            'prodsearchkeywords'             => '',
            'prodcallforpricinglabel'        => '',
            'prodcostprice'                  => 0,
            'prodretailprice'                => 0,
            'prodsaleprice'                  => 0,
            'prodcalculatedprice'            => $product['prodprice'],
            'prodnumsold'                    => 0,
            'average_rating'                 => 0,
            'prodvariationid'                => 0,
            'prodhideprice'                  => 0,
            'prodreleasedateremove'          => 0,
            'prodbrandid'                    => 0,
            'prodcurrentinv'                 => 0,
            'prodlowinv'                     => 0,
            'prodinvtrack'                   => 0,
            'prodvisible'                    => 0,
            'prodfeatured'                   => 0,
            'prodfreeshipping'               => 0,
            'prodsortorder'                  => 0,
            'prodpreorder'                   => 0,
            'prodreleasedate'                => 0,
            'prodvendorid'                   => 0,
            'prodhastags'                    => 0,
            'prodvendorfeatured'             => 0,
            'prodwrapoptions'                => 0,
            'prodeventdaterequired'          => 0,
            'prodeventdatefieldname'         => '',
            'prodeventdatelimited'           => 0,
            'prodeventdatelimitedtype'       => 0,
            'prodeventdatelimitedstartdate'  => 0,
            'prodeventdatelimitedenddate'    => 0,
            'tax_class_id'                   => 0,
            'opengraph_type'                 => 'product',
            'opengraph_use_product_name'     => 1,
            'opengraph_title'                => '',
            'opengraph_use_meta_description' => 1,
            'opengraph_description'          => '',
            'opengraph_use_image'            => 1,
            'upc'                            => '',
            'disable_google_checkout'        => 0,
            'last_import'                    => 0,
            'product_type_id'                => NULL,
            'prodlayoutfile'                 => 'product.html',
            'prodcondition'                  => 'New',
            'prodconfigfields'               => '',
        );

        $gateway = new Store_Product_Gateway();
        $id = $gateway->add($product);

        $this->assertNotEmpty($id, 'failed to save product');

        $savedData = $gateway->getLastSavedData();

        $gateway->delete($id);

        $this->assertArrayHasKey('proddateadded', $savedData);
        $this->assertNotEmpty($savedData['proddateadded']);
        $this->assertArrayHasKey('prodlastmodified', $savedData);
        $this->assertNotEmpty($savedData['prodlastmodified']);

        unset($savedData['proddateadded']);
        unset($savedData['prodlastmodified']);

        $this->assertEquals($expected, $savedData);
    }

    public function testAddMinimalProductHasRequiredFieldsForIndex()
    {
        $product = $this->getMinimalProduct();

        $expected = array(
            'prodname',
            'prodcode',
            'upc',
            'proddesc',
            'prodsearchkeywords',
            'product_type_id',
            'prodprice',
            'prodcalculatedprice',
            'prodretailprice',
            'prodsaleprice',
            'prodhideprice',
            'tax_class_id',
            'prodbrandid',
            'prodcatids',
            'prodcurrentinv',
            'prodlowinv',
            'prodinvtrack',
            'prodnumsold',
            'prodfeatured',
            'prodvisible',
            'prodfreeshipping',
            'prodconfigfields',
            'prodeventdaterequired',
            'prodtype',
            'prodpreorder',
            'prodallowpurchases',
            'average_rating',
            'prodsortorder',
            'prodcondition',
            'proddateadded',
            'prodlastmodified',
        );

        $gateway = new Store_Product_Gateway();
        $id = $gateway->add($product);

        $this->assertNotEmpty($id, 'failed to save product');

        $savedKeys = array_keys($gateway->getLastSavedData());

        $gateway->delete($id);

        $this->assertEquals($expected, array_intersect($expected, $savedKeys));
    }

    public function testAssignBrandToProductsIncludesBrandName()
    {
        $brands = $this->loadFixture('brands');
        $brand = reset($brands);

        $eventData = array();
        \Interspire_Event::bind(
            \Store_Event::EVENT_PRODUCT_BULK_CHANGED_BRAND,
            function (\Interspire_Event $event) use (&$eventData) {
                $eventData = $event->data;
            }
        );

        $productIds = array(1,2,3);

        $gateway = new Store_Product_Gateway();
        $gateway->assignBrandToProducts($productIds, $brand->getId());

        $this->assertNotEmpty($eventData);
        $this->assertEquals($productIds, $eventData['ids']);
        $this->assertNull($eventData['before']);

        $afterData = $eventData['after'];
        $this->assertEquals($brand->getId(), $afterData['prodbrandid']);
        $this->assertEquals($brand->getName(), $afterData['brandname']);
    }
}
