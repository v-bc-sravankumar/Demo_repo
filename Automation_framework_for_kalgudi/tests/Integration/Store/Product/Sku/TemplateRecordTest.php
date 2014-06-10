<?php
require_once dirname(__FILE__) . '/../../ModelLike_TestCase.php';

use Store\Product\Sku\TemplateRecord;
use Store\Product\Sku\Template\Rule\AbstractRule;
use Store\Product\Sku\Template\Token;

class TemplateRecordTest extends ModelLike_TestCase
{
    public function tearDown()
    {
        TemplateRecord::find()->deleteAll();
    }

    protected function _getCrudSmokeGetMethod ()
    {
        return 'getTemplate';
    }

    protected function _getCrudSmokeSetMethod ()
    {
        return 'setTemplate';
    }

    protected function _getCrudSmokeValue1 ()
    {
        return \Store\Product\Sku\Template::loadFromArray(array(
            'tokens' => array(
                array(
                    'type' => Token::TYPE_BRAND,
                    'data' => null,
                    'rule' => array(
                        'type' => AbstractRule::TYPE_FIRST,
                        'data' => 4,
                    ),
                ),
            ),
        ));
    }

    protected function _getCrudSmokeValue2 ()
    {
        return \Store\Product\Sku\Template::loadFromArray(array(
            'tokens' => array(
                array(
                    'type' => Token::TYPE_PRODUCT,
                    'data' => null,
                    'rule' => array(
                        'type' => AbstractRule::TYPE_ABBR,
                        'data' => null,
                    ),
                ),
            ),
        ));
    }

    protected function _getCrudSmokeInstance ()
    {
        $model = new TemplateRecord;
        $model->setTemplate($this->_getCrudSmokeValue1());
        return $model;
    }

    public function testFindSmoke ()
    {
        $this->markTestSkipped();
    }

    public function testCloneCorrectlySubClones()
    {
        $this->markTestSkipped();
    }

    public function testGetProductIdIfPresents()
    {
        $record = new TemplateRecord(array(
            'id' => null,
            'product_id' => 1,
            'product_hash' => null,
            'template' => null,
        ));

        $this->assertEquals(1, $record->getProductIdentifier());
    }

    public function testGetProductHashIfPresents()
    {
        $record = new TemplateRecord(array(
            'id' => null,
            'product_id' => 0,
            'product_hash' => 'hash' ,
            'template' => null,
        ));

        $this->assertEquals('hash', $record->getProductIdentifier());
    }

    public function testSetProductIdentifierWithId()
    {
        $record = new TemplateRecord(array(
            'id' => null,
            'product_id' => 0,
            'product_hash' => 'hash' ,
            'template' => null,
        ));

        $this->assertEquals(1, $record->setProductIdentifier(1)->getProductIdentifier());
    }

    public function testSetProductIdentifierWithHash()
    {
        $record = new TemplateRecord(array(
            'id' => null,
            'product_id' => 1,
            'product_hash' => '' ,
            'template' => null,
        ));

        $this->assertEquals('hash', $record->setProductIdentifier('hash')->getProductIdentifier());
    }

    public function testFindByProductIdentiferReturnsEmpty()
    {
        $this->assertEmpty(TemplateRecord::findByProductIdentifier('hash'));
        $this->assertEmpty(TemplateRecord::findByProductIdentifier(1));
    }

    public function testFindByProductId()
    {
        $this->_getCrudSmokeInstance()
            ->setProductIdentifier(1)
            ->save();

        $this->assertInstanceOf('Store\Product\Sku\TemplateRecord', TemplateRecord::findByProductIdentifier(1));
    }

    public function testFindByProductHash()
    {
        $this->_getCrudSmokeInstance()
            ->setProductIdentifier('hash')
            ->save();

        $this->assertInstanceOf('Store\Product\Sku\TemplateRecord', TemplateRecord::findByProductIdentifier('hash'));
    }

    public function testSkuTemplateHookInProductGateway()
    {
        $this->_getCrudSmokeInstance()
            ->setProductIdentifier('hash')
            ->save();

        $data = array(
            'productid' => 0,
            'prodhash' => 'hash',
            'prodname' => 'TEST_OPTIONS',
            'prodcats' => array('2'),
            'prodtype' => '1',
            'prodcode' => '',
            'productVariationExisting' => '',
            'proddesc' => 'TEST_OPTIONS',
            'prodpagetitle' => '',
            'prodsearchkeywords' => '',
            'prodavailability' => '',
            'prodprice' => '5.00',
            'prodcostprice' => '0.00',
            'prodretailprice' => '0.00',
            'prodsaleprice' => '0.00',
            'prodsortorder' => 0,
            'prodistaxable' => 1,
            'prodwrapoptions' => 0,
            'prodvisible' => 1,
            'prodfeatured' => 0,
            'prodvendorfeatured' => 0,
            'prodallowpurchases' => 1,
            'prodhideprice' => 0,
            'prodcallforpricinglabel' => '',
            'prodpreorder' => 0,
            'prodreleasedate' => 0,
            'prodreleasedateremove' => 0,
            'prodpreordermessage' => '',
            'prodrelatedproducts' => -1,
            'prodinvtrack' => 0,
            'prodcurrentinv' => 0,
            'prodlowinv' => 0,
            'prodtags' => '',
            'prodweight' => '5.00',
            'prodwidth' => '5.00',
            'prodheight' => '5.00',
            'proddepth' => '5.00',
            'prodfixedshippingcost' => '0.00',
            'prodwarranty' => '',
            'prodmetakeywords' => '',
            'prodmetadesc' => '',
            'prodfreeshipping' => 0,
            'prodoptionsrequired' => 1,
            'prodbrandid' => 0,
            'prodlayoutfile' => 'product.html',
            'prodeventdaterequired' => 0,
            'prodeventdatefieldname' => '',
            'prodeventdatelimited' => 0,
            'prodeventdatelimitedtype' => 0,
            'prodeventdatelimitedstartdate' => 0,
            'prodeventdatelimitedenddate' => 0,
            'prodvariationid' => 0,
            'prodvendorid' => 0,
            'prodmyobasset' => '',
            'prodmyobincome' => '',
            'prodmyobexpense' => '',
            'prodpeachtreegl' => '',
            'prodcondition' => 'New',
            'prodshowcondition' => 0,
            'product_videos' => array(),
            'product_images' => array(),
            'product_enable_optimizer' => 0,
            'prodminqty' => 0,
            'prodmaxqty' => 0,
        );

        $products = new Store_Product_Gateway();
        $productId = (int)$products->add($data);

        // product should be saved successfully
        $this->assertGreaterThan(0, $productId, $products->getError());

        // template record should be updated to use product id instead of hash
        $this->assertInstanceOf('Store\Product\Sku\TemplateRecord', TemplateRecord::findByProductIdentifier($productId));

        // delete the product
        $this->assertTrue($products->delete($productId), $products->getError());

        // template record should be deleted together with the product
        $this->assertEmpty(TemplateRecord::findByProductIdentifier($productId));
    }
}
