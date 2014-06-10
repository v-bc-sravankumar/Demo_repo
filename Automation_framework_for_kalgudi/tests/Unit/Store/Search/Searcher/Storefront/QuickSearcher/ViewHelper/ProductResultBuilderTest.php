<?php

namespace Unit\Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper;

use Language\LanguageManager;
use Utilities\General;
use Utilities\Pricing;
use Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\ProductResultBuilder;
use Store\View\Helper\ProductImageHelper;

class ProductResultBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return General|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getGeneral()
    {
        $general = $this
            ->getMockBuilder('Utilities\General')
            ->disableOriginalConstructor()
            ->setMethods(array('getProductReviewsEnabled'))
            ->getMock();

        return $general;
    }

    /**
     * @return LanguageManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getLanguageManager()
    {
        $languageManager = $this
            ->getMockBuilder('Language\LanguageManager')
            ->disableOriginalConstructor()
            ->setMethods(array('load', 'get'))
            ->getMock();

        return $languageManager;
    }

    /**
     * @return Pricing|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getPricing()
    {
        $pricing = $this
            ->getMockBuilder('Utilities\Pricing')
            ->disableOriginalConstructor()
            ->setMethods(array('formatProductCatalogPrice'))
            ->getMock();

        return $pricing;
    }

    /**
     * @param $hidePrice
     * @param int|null $imageId
     * @return array
     */
    public function getProduct($hidePrice, $imageId = null)
    {
        $product = array(
            'prodname'            => 'test-name',
            'prodhideprice'       => $hidePrice,
            'prodavgrating'       => 3.123,
            'prodcalculatedprice' => 123.45,
            'imageid'             => $imageId,
        );

        return $product;
    }

    /**
     * @param bool $productPriceEnabled
     * @return \Store_Settings
     */
    private function getSettings($productPriceEnabled = false)
    {
        $settings = new \Store_Settings(new \Store_Settings_Driver_Dummy(array(
            'ShowProductPrice' => $productPriceEnabled,
        )));
        $settings->load();

        return $settings;
    }

    /**
     * @return \TEMPLATE|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getTemplate()
    {
        $template = $this
            ->getMockBuilder('TEMPLATE')
            ->disableOriginalConstructor()
            ->setMethods(array('Assign', 'GetSnippet'))
            ->getMock();

        // Assert that the product name is assigned correctly.
        $template
            ->expects($this->at(0))
            ->method('Assign')
            ->with($this->equalTo('ProductName'), $this->equalTo('test-name'));

        // Assert that the product URL is assigned correctly.
        $template
            ->expects($this->at(1))
            ->method('Assign')
            ->with($this->equalTo('ProductURL'), $this->equalTo('http://foo.com'));

        // Assert that the snippet is loaded correctly.
        $template
            ->expects($this->once())
            ->method('GetSnippet')
            ->with($this->equalTo('SearchResultAJAXProduct'))
            ->will($this->returnValue('test-html'));

        return $template;
    }

    /**
     * @param array $product
     * @return \Store_UrlGenerator_Product
     */
    private function getUrlGenerator(array $product)
    {
        $urlGenerator = $this
            ->getMockBuilder('Store_UrlGenerator_Product')
            ->setMethods(array('getStoreFrontUrl'))
            ->getMock();

        // Assert that the product URL is requested from the URL generator.
        $urlGenerator
            ->expects($this->once())
            ->method('getStoreFrontUrl')
            ->with($this->equalTo($product))
            ->will($this->returnValue('http://foo.com'));

        return $urlGenerator;
    }

    public function testProductPriceEnabled()
    {
        $general         = $this->getGeneral();
        $languageManager = $this->getLanguageManager();
        $pricing         = $this->getPricing();
        $product         = $this->getProduct(false); // prodhideprice => false
        $settings        = $this->getSettings(true); // productPriceEnabled => true
        $template        = $this->getTemplate();
        $urlGenerator    = $this->getUrlGenerator($product);

        // Assert that the product price is sent through the correct formatting method.
        $pricing
            ->expects($this->at(0))
            ->method('formatProductCatalogPrice')
            ->with($this->equalTo($product))
            ->will($this->returnValue('test-price-formatted'));

        // Assert that, when product prices are enabled, the product price is correctly set.
        $template
            ->expects($this->at(2))
            ->method('Assign')
            ->with($this->equalTo('ProductPrice'), $this->equalTo('test-price-formatted'));

        $builder = new ProductResultBuilder(
            $template,
            $settings,
            $languageManager,
            $pricing,
            $general,
            $urlGenerator,
            'test-path'
        );
        $builder->buildHtmlResults($product);
    }

    public function testProductPriceDisabledOnProduct()
    {
        $general         = $this->getGeneral();
        $languageManager = $this->getLanguageManager();
        $pricing         = $this->getPricing();
        $product         = $this->getProduct(true);  // prodhideprice => true
        $settings        = $this->getSettings(true); // productPriceEnabled => true
        $template        = $this->getTemplate();
        $urlGenerator    = $this->getUrlGenerator($product);

        // Assert that the formatter is never called.
        $pricing
            ->expects($this->never())
            ->method('formatProductCatalogPrice');

        // Assert that, when product prices are disabled (with prodhideprice => true), the product price is set to an
        // empty string.
        $template
            ->expects($this->at(2))
            ->method('Assign')
            ->with($this->equalTo('ProductPrice'), $this->equalTo(''));

        $builder = new ProductResultBuilder(
            $template,
            $settings,
            $languageManager,
            $pricing,
            $general,
            $urlGenerator,
            'test-path'
        );
        $builder->buildHtmlResults($product);
    }

    public function testProductPriceDisabledInConfig()
    {
        $general         = $this->getGeneral();
        $languagemanager = $this->getlanguagemanager();
        $pricing         = $this->getpricing();
        $product         = $this->getproduct(false); // pridhideprice => false
        $settings        = $this->getsettings(false); // productpriceenabled => false
        $template        = $this->gettemplate();
        $urlgenerator    = $this->geturlgenerator($product);

        // assert that the formatter is never called.
        $pricing
            ->expects($this->never())
            ->method('formatproductcatalogprice');

        // assert that, when product prices are disabled (with productpriceenabled => false), the product price is set
        // to an empty string.
        $template
            ->expects($this->at(2))
            ->method('assign')
            ->with($this->equalTo('ProductPrice'), $this->equalTo(''));

        $builder = new productresultbuilder(
            $template,
            $settings,
            $languagemanager,
            $pricing,
            $general,
            $urlgenerator,
            'test-path'
        );
        $builder->buildhtmlresults($product);
    }

    public function testProductReviewsEnabled()
    {
        $general         = $this->getGeneral();
        $languageManager = $this->getLanguageManager();
        $pricing         = $this->getPricing();
        $product         = $this->getProduct(false);  // pridhideprice => false
        $settings        = $this->getSettings();
        $template        = $this->getTemplate();
        $urlGenerator    = $this->getUrlGenerator($product);

        // Assert that getProductReviewsEnabled() is called.
        $general
            ->expects($this->at(0))
            ->method('getProductReviewsEnabled')
            ->will($this->returnValue(true));

        // Assert that, when reviews are enabled, ProductRatingImage is Assign()ed with a non-empty value.
        $template
            ->expects($this->at(3))
            ->method('Assign')
            ->with($this->equalTo('ProductRatingImage'), $this->logicalNot($this->equalTo('')));

        $builder = new ProductResultBuilder(
            $template,
            $settings,
            $languageManager,
            $pricing,
            $general,
            $urlGenerator,
            'test-path'
        );
        $builder->buildHtmlResults($product);
    }

    public function testProductReviewsDisabled()
    {
        $general         = $this->getGeneral();
        $languageManager = $this->getLanguageManager();
        $pricing         = $this->getPricing();
        $product         = $this->getProduct(false);  // pridhideprice => false
        $settings        = $this->getSettings();
        $template        = $this->getTemplate();
        $urlGenerator    = $this->getUrlGenerator($product);

        // Assert that getProductReviewsEnabled() is called.
        $general
            ->expects($this->at(0))
            ->method('getProductReviewsEnabled')
            ->will($this->returnValue(false));

        // Assert that, when product reviews are disabled, the product rating image is not set.
        $template
            ->expects($this->at(3))
            ->method('Assign')
            ->with($this->equalTo('ProductRatingImage'), $this->equalTo(''));

        $builder = new ProductResultBuilder(
            $template,
            $settings,
            $languageManager,
            $pricing,
            $general,
            $urlGenerator,
            'test-path'
        );
        $builder->buildHtmlResults($product);
    }

    public function testProductImageNotEmpty()
    {
        $general         = $this->getGeneral();
        $languageManager = $this->getLanguageManager();
        $pricing         = $this->getPricing();
        $product         = $this->getProduct(false, 123);  // pridhideprice => false, imageid => 123
        $settings        = $this->getSettings();
        $template        = $this->getTemplate();
        $urlGenerator    = $this->getUrlGenerator($product);

        // Assert that getProductReviewsEnabled() is called.
        $general
            ->expects($this->at(0))
            ->method('getProductReviewsEnabled')
            ->will($this->returnValue(false));

        // Assert that, when a product image is provided, the product image is correctly set.
        $template
            ->expects($this->at(4))
            ->method('Assign')
            ->with($this->equalTo('ProductNoImageClassName'), $this->equalTo(''));
        $template
            ->expects($this->at(5))
            ->method('Assign')
            ->with($this->equalTo('ProductImage'), $this->equalTo('test-image-html'));

        /** @var ProductResultBuilder|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this
            ->getMockBuilder('Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\ProductResultBuilder')
            ->setConstructorArgs(array(
                $template,
                $settings,
                $languageManager,
                $pricing,
                $general,
                $urlGenerator,
                'test-path'
            ))
            ->setMethods(array('getImageHtml'))
            ->getMock();
        $builder
            ->expects($this->at(0))
            ->method('getImageHtml')
            ->with($this->equalTo($product))
            ->will($this->returnValue('test-image-html'));

        $builder->buildHtmlResults($product);
    }

    public function testProductImageEmpty()
    {
        $general         = $this->getGeneral();
        $languageManager = $this->getLanguageManager();
        $pricing         = $this->getPricing();
        $product         = $this->getProduct(false); // pridhideprice => false, imageid => 123
        $settings        = $this->getSettings();
        $template        = $this->getTemplate();
        $urlGenerator    = $this->getUrlGenerator($product);

        // Assert that getProductReviewsEnabled() is called.
        $general
            ->expects($this->at(0))
            ->method('getProductReviewsEnabled')
            ->will($this->returnValue(false));

        // Assert that, when a product image is not provided, the product image is correctly set.
        $template
            ->expects($this->at(4))
            ->method('Assign')
            ->with($this->equalTo('ProductNoImageClassName'), $this->equalTo('QuickSearchResultNoImage'));
        $template
            ->expects($this->at(5))
            ->method('Assign')
            ->with($this->equalTo('ProductImage'), $this->equalTo('<span>QuickSearchNoImage</span>'));

        // Assert that the language manager is loaded and the property is accessed.
        $languageManager
            ->expects($this->at(0))
            ->method('load')
            ->with($this->equalTo('front_language'));
        $languageManager
            ->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('QuickSearchNoImage'))
            ->will($this->returnValue('QuickSearchNoImage'));

        /** @var ProductResultBuilder|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this
            ->getMockBuilder('Store\Search\Searcher\Storefront\QuickSearcher\ViewHelper\ProductResultBuilder')
            ->setConstructorArgs(array(
                $template,
                $settings,
                $languageManager,
                $pricing,
                $general,
                $urlGenerator,
                'test-path'
            ))
            ->setMethods(array('getImageHtml'))
            ->getMock();

        // Assert that, when a product image is not provided, getImageHtml is never called.
        $builder
            ->expects($this->never())
            ->method('getImageHtml');

        $builder->buildHtmlResults($product);
    }
}
