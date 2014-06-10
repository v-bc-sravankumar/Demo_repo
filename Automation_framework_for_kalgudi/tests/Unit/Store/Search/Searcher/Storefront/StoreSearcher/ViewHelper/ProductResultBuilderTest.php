<?php

namespace Unit\Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper;

use Language\LanguageManager;
use Store\Search\Searcher\Storefront\StoreSearcher\ViewHelper\ProductResultBuilder;
use Utilities\Links;
use Utilities\Pricing;

class ProductResultBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $product
     * @param bool $getCartLink
     * @return Links|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLinks(array $product, $getCartLink = false)
    {
        $links = $this
            ->getMockBuilder('Utilities\Links')
            ->disableOriginalConstructor()
            ->setMethods(array('cartLink'))
            ->getMock();

        if ($getCartLink) {
            $links
                ->expects($this->at(0))
                ->method('cartLink')
                ->with($this->equalTo($product['productid']))
                ->will($this->returnValue('test-cart-link'));
        }

        return $links;
    }

    /**
     * @param bool $load
     * @return LanguageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLanguageManager($load = true)
    {
        $languageManager = $this
            ->getMockBuilder('Language\LanguageManager')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'get'))
            ->getMock();

        if ($load) {
            $languageManager
                ->expects($this->at(0))
                ->method('load')
                ->with($this->equalTo('front_language'));
            $languageManager
                ->expects($this->any())
                ->method('get')
                ->will($this->returnArgument(0));
        }

        return $languageManager;
    }

    /**
     * @param array $product
     * @return Pricing|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPricing(array $product = array())
    {
        if (empty($product)) {
            $product = $this->getProduct();
        }

        $pricing = $this
            ->getMockBuilder('Utilities\Pricing')
            ->disableOriginalConstructor()
            ->setMethods(array('formatProductCatalogPrice'))
            ->getMock();
        $pricing
            ->expects($this->at(0))
            ->method('formatProductCatalogPrice')
            ->with($this->equalTo($product))
            ->will($this->returnValue('test-price'));

        return $pricing;
    }

    /**
     * @return array
     */
    private function getProduct()
    {

        $product = array(
            'productid'             => 123,
            'prodname'              => 'test-name',
            'prodavgrating'         => 3,
            'prodhideprice'         => false,
            'prodconfigfields'      => 'test-fields',
            'prodeventdaterequired' => 1,
            'product_type_id'       => 1,
        );

        return $product;
    }

    /**
     * @param array $rawSettings
     * @return \Store_Settings
     */
    private function getSettings(array $rawSettings = array())
    {
        if (!isset($rawSettings['ShowProductPrice'])) {
            $rawSettings['ShowProductPrice'] = true;
        }

        if (!isset($rawSettings['EnableProductComparisons'])) {
            $rawSettings['EnableProductComparisons'] = 1;
        }

        if (!isset($rawSettings['SearchProductDisplayMode'])) {
            $rawSettings['SearchProductDisplayMode'] = 'grid';
        }

        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy($rawSettings));
        $settings->load();

        return $settings;
    }

    /**
     * @param array $parameters
     * @return \TEMPLATE|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTemplate(array $parameters = array())
    {
        if (!isset($parameters['parity'])) {
            $parameters['parity'] = 'odd';
        }

        if (!isset($parameters['resultCount'])) {
            $parameters['resultCount'] = 1;
        }

        if (!isset($parameters['productCartQuantity'])) {
            $parameters['productCartQuantity'] = '';
        }

        if (!isset($parameters['product'])) {
            $parameters['product'] = $this->getProduct();
        }

        if (!isset($parameters['comparisonsEnabled'])) {
            $parameters['comparisonsEnabled'] = true;
        }

        if (!isset($parameters['productUrl'])) {
            $parameters['productUrl'] = 'test-link';
        }

        $template = $this
            ->getMockBuilder('TEMPLATE')
            ->disableOriginalConstructor()
            ->setMethods(array('Assign', 'GetSnippet'))
            ->getMock();

        $template
            ->expects($this->at(0))
            ->method('Assign')
            ->with(
                $this->equalTo('HideCompareItems'),
                $this->equalTo(($parameters['resultCount'] > 1 && $parameters['comparisonsEnabled']) ? '' : 'none')
            );
        $template
            ->expects($this->at(1))
            ->method('Assign')
            ->with($this->equalTo('AlternateClass'), $this->equalTo($parameters['parity'] == 'odd' ? 'Odd' : 'Even'));
        $template
            ->expects($this->at(2))
            ->method('Assign')
            ->with($this->equalTo('ProductCartQuantity'), $this->equalTo($parameters['productCartQuantity']));
        $template
            ->expects($this->at(3))
            ->method('Assign')
            ->with($this->equalTo('ProductId'), (int) $parameters['product']['productid']);
        $template
            ->expects($this->at(4))
            ->method('Assign')
            ->with($this->equalTo('ProductName'), $parameters['product']['prodname']);
        $template
            ->expects($this->at(5))
            ->method('Assign')
            ->with($this->equalTo('ProductLink'), $this->equalTo('test-link'));
        $template
            ->expects($this->at(6))
            ->method('Assign')
            ->with($this->equalTo('ProductRating', round((float) $parameters['product']['prodavgrating'])));
        $template
            ->expects($this->at(7))
            ->method('Assign')
            ->with($this->equalTo('ProductPrice'), $this->equalTo('test-price'));
        $template
            ->expects($this->at(8))
            ->method('Assign')
            ->with($this->equalTo('ProductThumb'), $this->equalTo(''));
        $template
            ->expects($this->at(9))
            ->method('Assign')
            ->with($this->equalTo('ProductURL'), $this->equalTo($parameters['productUrl']));
        $template
            ->expects($this->at(10))
            ->method('Assign')
            ->with(
                $this->equalTo('ProductAddText'),
                $this->equalTo(
                    $parameters['product']['prodconfigfields'] != '' ||
                    $parameters['product']['prodeventdaterequired'] == 1 ||
                    !empty($parameters['product']['product_type_id'])
                    ? 'ProductChooseOptionLink'
                    : 'ProductAddToCartLink'
                )
            );
        $template
            ->expects($this->at(11))
            ->method('Assign')
            ->with($this->equalTo('HideActionAdd'), $this->equalTo('none'));
        $template
            ->expects($this->at(12))
            ->method('Assign')
            ->with(
                $this->equalTo('CompareOnSubmit'),
                $this->equalTo('onsubmit="return compareProducts(config.CompareLink);"')
            );
        $template
            ->expects($this->at(13))
            ->method('GetSnippet')
            ->with($this->equalTo('SearchResultProductGrid'))
            ->will($this->returnValue('test-html'));

        return $template;
    }

    /**
     * @param array $product
     * @param bool $used
     * @return \Store_UrlGenerator_Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUrlGenerator(array $product = array(), $used = true)
    {
        $urlGenerator = $this
            ->getMockBuilder('Store_UrlGenerator_Product')
            ->disableOriginalConstructor()
            ->setMethods(array('getStoreFrontUrl'))
            ->getMock();

        if ($used) {
            $urlGenerator
                ->expects($this->at(0))
                ->method('getStoreFrontUrl')
                ->with($this->equalTo($product))
                ->will($this->returnValue('test-link'));
        }

        return $urlGenerator;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Must provide a 'parity' argument.
     */
    public function testWithoutParity()
    {
        /** @var \TEMPLATE $template */
        $template = $this->getMockBuilder('TEMPLATE')->disableOriginalConstructor()->getMock();

        /** @var Pricing $pricing */
        $pricing = $this->getMockBuilder('Utilities\Pricing')->disableOriginalConstructor()->getMock();

        /** @var Links $links */
        $links = $this->getMockBuilder('Utilities\Links')->disableOriginalConstructor()->getMock();

        // Assert that an exception is thrown.
        $builder = new ProductResultBuilder(
            $template,
            $this->getSettings(),
            $this->getLanguageManager(false),
            $pricing,
            $links,
            $this->getUrlGenerator(array(), false)
        );
        $builder->buildHtmlResults(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Must provide a 'resultCount' argument.
     */
    public function testWithoutResultCount()
    {
        /** @var \TEMPLATE $template */
        $template = $this->getMockBuilder('TEMPLATE')->disableOriginalConstructor()->getMock();

        /** @var Pricing $pricing */
        $pricing = $this->getMockBuilder('Utilities\Pricing')->disableOriginalConstructor()->getMock();

        /** @var Links $links */
        $links = $this->getMockBuilder('Utilities\Links')->disableOriginalConstructor()->getMock();

        // Assert that an exception is thrown.
        $builder = new ProductResultBuilder(
            $template,
            $this->getSettings(),
            $this->getLanguageManager(false),
            $pricing,
            $links,
            $this->getUrlGenerator(array(), false)
        );
        $builder->buildHtmlResults(array(), array('parity' => 'odd'));
    }

    public function testProductComparisonsEnabled()
    {
        $product = $this->getProduct();

        // Assert that HideCompareItems is set to an empty string when there are multiple results and
        // EnableProductComparison is set to 1.
        $builder = new ProductResultBuilder(
            $this->getTemplate(array('resultCount' => 2, 'comparisonsEnabled' => true)),
            $this->getSettings(array('EnableProductComparisons' => 1)),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 2));
    }

    public function testProductComparisonsDisabledInConfig()
    {
        $product = $this->getProduct();

        // Assert that HideCompareItems is set to none when EnableProductComparisons is set to 0.
        $builder = new ProductResultBuilder(
            $this->getTemplate(array('resultCount' => 2, 'comparisonsEnabled' => false)),
            $this->getSettings(array('EnableProductComparisons' => 0)),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 2));
    }

    public function testProductComparisonsDisabledByResultCount()
    {
        $product = $this->getProduct();

        // Assert that HideCompareItems is set to none when EnableProductComparisons is set to 0.
        $builder = new ProductResultBuilder(
            $this->getTemplate(array('resultCount' => 1, 'comparisonsEnabled' => true)),
            $this->getSettings(array('EnableProductComparisons' => 1)),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 1));
    }

    public function testOdd()
    {
        $product = $this->getProduct();

        // Assert that AlternateClass is set to Odd.
        $builder = new ProductResultBuilder(
            $this->getTemplate(array('parity' => 'odd')),
            $this->getSettings(),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 1));
    }

    public function testEven()
    {
        $product = $this->getProduct();

        // Assert that AlternateClass is set to Even.
        $builder = new ProductResultBuilder(
            $this->getTemplate(array('parity' => 'even')),
            $this->getSettings(),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'even', 'resultCount' => 1));
    }

    public function testCartQuanity()
    {
        $GLOBALS['CartQuantity123'] = 456;

        // Assert that the quantity is set correctly when set in the global.
        $product = $this->getProduct();

        $builder = new ProductResultBuilder(
            $this->getTemplate(array('productCartQuantity' => 456)),
            $this->getSettings(),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 1));

        unset($GLOBALS['CartQuantity123']);
    }

    public function testChooseOptionLinkFromConfiguredFields()
    {
        // Assert that, when prodconfigfields is not empty, the ProdChooseOptionLink is added.
        $product = $this->getProduct();
        $product['prodconfigfields']      = 'not-empty';
        $product['prodeventdaterequired'] = 0;
        $product['product_type_id']       = '';

        $builder = new ProductResultBuilder(
            $this->getTemplate(array('product' => $product)),
            $this->getSettings(),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 1));
    }

    public function testChooseOptionLinkFromEventDateRequired()
    {
        // Assert that, when prodeventdaterequired is set to 1, the ProdChooseOptionLink is added.
        $product = $this->getProduct();
        $product['prodconfigfields']      = '';
        $product['prodeventdaterequired'] = 1;
        $product['product_type_id']       = '';

        $builder = new ProductResultBuilder(
            $this->getTemplate(array('product' => $product)),
            $this->getSettings(),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 1));
    }

    public function testChooseOptionLinkFromTypeId()
    {
        // Assert that, when the product_type_id is set, the ProdChooseOptionLink is added.
        $product = $this->getProduct();
        $product['prodconfigfields']      = '';
        $product['prodeventdaterequired'] = 0;
        $product['product_type_id']       = 1;

        $builder = new ProductResultBuilder(
            $this->getTemplate(array('product' => $product)),
            $this->getSettings(),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 1));
    }

    public function testAddToCartLink()
    {
        // Assert that, when prodconfigfields is empty, prodeventdaterequired is not set to 1 and prod_type_id is not
        // set, thet ProductAddToCartLink is added.
        $product = $this->getProduct();
        $product['prodconfigfields']      = '';
        $product['prodeventdaterequired'] = 0;
        $product['product_type_id']       = '';

        $builder = new ProductResultBuilder(
            $this->getTemplate(array('product' => $product, 'productUrl' => 'test-cart-link')),
            $this->getSettings(),
            $this->getLanguageManager(),
            $this->getPricing($product),
            $this->getLinks($product, true),
            $this->getUrlGenerator($product)
        );

        $builder->buildHtmlResults($product, array('parity' => 'odd', 'resultCount' => 1));
    }
}
