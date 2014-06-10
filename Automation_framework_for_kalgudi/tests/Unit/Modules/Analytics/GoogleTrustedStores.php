<?php
use Orders\Order;

class Unit_Modules_Analytics_GoogleTrustedStores extends PHPUnit_Framework_TestCase
{

    public function testGetTrackingCode() {

    	$country = $this->getMock('Store_Country', array('getCountryIso3'));
    	$country
    		->expects($this->any())
    		->method('getCountryIso2')
    		->will($this->returnValue('XY'));

        $gts = $this->getMock('ANALYTICS_GOOGLETRUSTEDSTORES', array(
            'GetValue',
        	'lookupCountryByName',
        ));

        $gts->expects($this->any())->method('GetValue')->with('gts_account_id')->will($this->returnValue(9999));
        $gts->expects($this->any())->method('lookupCountryByName')->will($this->returnValue($country));

        $this->assertEquals(9999, $gts->GetValue('gts_account_id'));

        $expected = '<!-- BEGIN: Google Trusted Stores -->
<script type="text/javascript">
var gts = gts || [];
gts.push(["id", "9999"]);
(function()
{ var scheme = (("https:" == document.location.protocol) ? "https://" : "http://"); var gts = document.createElement("script"); gts.type = "text/javascript"; gts.async = true; gts.src = scheme + "www.googlecommerce.com/trustedstores/gtmp_compiled.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(gts, s); }
)();
</script>
<!-- END: Google Trusted Stores -->';

        $actual = $gts->GetTrackingCode();

        $this->assertEquals($expected, $actual);

    }

    public function testConverstionCodeForNonDigitalOrders() {

    	$country = $this->getMock('Store_Country', array('getCountryIso2'));
    	$country
    		->expects($this->any())
    		->method('getCountryIso2')
    		->will($this->returnValue('XY'));

        $order = $this->getDummyOrder();
        $order->expects($this->any())->method('hasPreOrderProducts')->will($this->returnValue(false));

        $now = new DateTime();
        $now->setTimestamp($order->getOrddate());
        $eta = 7;
        $estimated = $now->modify('+'.$eta.' days');
        $estimatedShippingDate = $estimated->format('Y-m-d');
        Store_Config::schedule('ShopPath', 'http://www.example.com', true);

        $expected = '<!-- START Google Trusted Stores Order -->
<div id="gts-order" style="display:none;">
<span id="gts-o-id">888</span>
<span id="gts-o-domain">www.example.com</span>
<span id="gts-o-email">unit@test.com</span>
<span id="gts-o-country">XY</span>
<span id="gts-o-currency">XYZ</span>
<span id="gts-o-total">99.95</span>
<span id="gts-o-discounts">-100.99</span>
<span id="gts-o-shipping-total">9.95</span>
<span id="gts-o-tax-total">5.55</span>
<span id="gts-o-est-ship-date">'.$estimatedShippingDate.'</span>
<span id="gts-o-has-preorder">N</span>
<span id="gts-o-has-digital">N</span>
<span class="gts-item">
<span class="gts-i-name">Product Name</span>
<span class="gts-i-price">99.95</span>
<span class="gts-i-quantity">100</span>
<span class="gts-i-prodsearch-id">999</span>
</span>
</div>
<!-- END Google Trusted Stores Order -->';

        $gts = $this->getMock('ANALYTICS_GOOGLETRUSTEDSTORES', array('lookupCountryByName', 'GetOrders', 'getEstimatedShippingDate', 'GetValue'));
        $gts->expects($this->any())->method('lookupCountryByName')->will($this->returnValue($country));
        $gts->expects($this->any())->method('GetOrders')->will($this->returnValue(array($order)));
        $gts->expects($this->any())->method('GetValue')->with('estimated_shipping_days')->will($this->returnValue($eta));
        $gts->expects($this->any())->method('getEstimatedShippingDate')->will($this->returnValue($estimatedShippingDate));

        $actual = $gts->GetConversionCode();

        $this->assertEquals($expected, $actual);

    }

    public function testConversionCodeForDigitalOrdersAndPreOrders()
    {
    	$order = $this->getDummyOrder();
    	$order->expects($this->any())->method('hasPreOrderProducts')->will($this->returnValue(true));

    	/*
    	 * Mock a digital product
    	 */
    	$product = $this->getMock('Store_Order_Product', array('getName', 'getType', 'getPriceIncludingTax', 'getQuantity', 'getId'));
    	$product->expects($this->any())->method('getName')->will($this->returnValue('Digital Product Name'));
    	$product->expects($this->any())->method('getType')->will($this->returnValue('digital'));
    	$product->expects($this->any())->method('getPriceIncludingTax')->will($this->returnValue('1.1100'));
    	$product->expects($this->any())->method('getQuantity')->will($this->returnValue(200));
    	$product->expects($this->any())->method('getId')->will($this->returnValue('111'));
    	$order->setProduct($product);

    	$now = new DateTime();
    	$now->setTimestamp($order->getOrddate());
    	$eta = 7;
    	$estimated = $now->modify('+'.$eta.' days');
    	$estimatedShippingDate = $estimated->format('Y-m-d');
    	Store_Config::schedule('ShopPath', 'http://www.example.com', true);

    	$expected = '<!-- START Google Trusted Stores Order -->
<div id="gts-order" style="display:none;">
<span id="gts-o-id">888</span>
<span id="gts-o-domain">www.example.com</span>
<span id="gts-o-email">unit@test.com</span>
<span id="gts-o-country">XY</span>
<span id="gts-o-currency">XYZ</span>
<span id="gts-o-total">99.95</span>
<span id="gts-o-discounts">-100.99</span>
<span id="gts-o-shipping-total">9.95</span>
<span id="gts-o-tax-total">5.55</span>
<span id="gts-o-est-ship-date">'.$estimatedShippingDate.'</span>
<span id="gts-o-has-preorder">Y</span>
<span id="gts-o-has-digital">Y</span>
<span class="gts-item">
<span class="gts-i-name">Product Name</span>
<span class="gts-i-price">99.95</span>
<span class="gts-i-quantity">100</span>
<span class="gts-i-prodsearch-id">999</span>
</span>
<span class="gts-item">
<span class="gts-i-name">Digital Product Name</span>
<span class="gts-i-price">1.11</span>
<span class="gts-i-quantity">200</span>
<span class="gts-i-prodsearch-id">111</span>
</span>
</div>
<!-- END Google Trusted Stores Order -->';

    	$country = $this->getMock('Store_Country', array('getCountryIso2'));
    	$country
    		->expects($this->any())
    		->method('getCountryIso2')
    		->will($this->returnValue('XY'));

    	$gts = $this->getMock('ANALYTICS_GOOGLETRUSTEDSTORES', array('lookupCountryByName', 'GetOrders', 'getEstimatedShippingDate', 'GetValue'));
    	$gts->expects($this->any())->method('lookupCountryByName')->will($this->returnValue($country));
    	$gts->expects($this->any())->method('GetOrders')->will($this->returnValue(array($order)));
    	$gts->expects($this->any())->method('GetValue')->with('estimated_shipping_days')->will($this->returnValue($eta));
    	$gts->expects($this->any())->method('getEstimatedShippingDate')->will($this->returnValue($estimatedShippingDate));

    	$actual = $gts->GetConversionCode();

    	$this->assertEquals($expected, $actual);
    }

    public function testGuestEmailForDigitalOrdersAndPreOrders()
    {
    	$order = $this->getDummyOrder();
    	$order->getBillingAddress()->email = 'guest@unit.com';
    	$order->setCustomer(null);
    	$order->expects($this->any())->method('hasPreOrderProducts')->will($this->returnValue(true));

    	$now = new DateTime();
    	$now->setTimestamp($order->getOrddate());
    	$eta = 7;
    	$estimated = $now->modify('+'.$eta.' days');
    	$estimatedShippingDate = $estimated->format('Y-m-d');
    	Store_Config::schedule('ShopPath', 'http://www.example.com', true);

    	$expected = '<!-- START Google Trusted Stores Order -->
<div id="gts-order" style="display:none;">
<span id="gts-o-id">888</span>
<span id="gts-o-domain">www.example.com</span>
<span id="gts-o-email">guest@unit.com</span>
<span id="gts-o-country">XY</span>
<span id="gts-o-currency">XYZ</span>
<span id="gts-o-total">99.95</span>
<span id="gts-o-discounts">-100.99</span>
<span id="gts-o-shipping-total">9.95</span>
<span id="gts-o-tax-total">5.55</span>
<span id="gts-o-est-ship-date">'.$estimatedShippingDate.'</span>
<span id="gts-o-has-preorder">Y</span>
<span id="gts-o-has-digital">N</span>
<span class="gts-item">
<span class="gts-i-name">Product Name</span>
<span class="gts-i-price">99.95</span>
<span class="gts-i-quantity">100</span>
<span class="gts-i-prodsearch-id">999</span>
</span>
</div>
<!-- END Google Trusted Stores Order -->';

    	$country = $this->getMock('Store_Country', array('getCountryIso2'));
    	$country
    		->expects($this->any())
    		->method('getCountryIso2')
    		->will($this->returnValue('XY'));

    	$gts = $this->getMock('ANALYTICS_GOOGLETRUSTEDSTORES', array('lookupCountryByName', 'GetOrders', 'getEstimatedShippingDate', 'GetValue'));
    	$gts->expects($this->any())->method('lookupCountryByName')->will($this->returnValue($country));
    	$gts->expects($this->any())->method('GetOrders')->will($this->returnValue(array($order)));
    	$gts->expects($this->any())->method('GetValue')->with('estimated_shipping_days')->will($this->returnValue($eta));
    	$gts->expects($this->any())->method('getEstimatedShippingDate')->will($this->returnValue($estimatedShippingDate));

    	$actual = $gts->GetConversionCode();

    	$this->assertEquals($expected, $actual);
    }

    protected function getDummyOrder() {

        /*
         * Mock out the billing address
         */
        $billing = new stdClass();
        $billing->country = 'Australia';

        /*
         * Mock out the currency
         */
        $currency = new \Store_Currency();
        $currency->setCode('XYZ');

        /*
         * Mock out the order
         */
        $order = $this->getMock('Orders\Order', array('getId', 'getBillingAddress', 'getCurrency', 'getTotalIncTax', 'getShippingCostIncTax', 'getTotalTax', 'getOrddate', 'getDiscountAmount', 'hasPreOrderProducts'));
        $order->expects($this->any())->method('getId')->will($this->returnValue(888));
        $order->expects($this->any())->method('getOrddate')->will($this->returnValue(time()));
        $order->expects($this->any())->method('getBillingAddress')->will($this->returnValue($billing));
        $order->expects($this->any())->method('getCurrency')->will($this->returnValue($currency));
        $order->expects($this->any())->method('getTotalIncTax')->will($this->returnValue('99.9500'));
        $order->expects($this->any())->method('getShippingCostIncTax')->will($this->returnValue('9.9500'));
        $order->expects($this->any())->method('getTotalTax')->will($this->returnValue('5.5500'));
        $order->expects($this->any())->method('getDiscountAmount')->will($this->returnValue('100.9900'));

        /*
         * Mock out the Customer
         */
        $customer = $this->getMock('Store_Customer', array('getEmail'));
        $customer->expects($this->any())->method('getEmail')->will($this->returnValue('unit@test.com'));
        $order->setCustomer($customer);

        /*
         * Mock out a physical product.
         */
        $product = $this->getMock('Store_Order_Product', array('getName', 'getType', 'getPriceIncludingTax', 'getQuantity', 'getId'));
        $product->expects($this->any())->method('getName')->will($this->returnValue('Product Name'));
        $product->expects($this->any())->method('getType')->will($this->returnValue('physical'));
        $product->expects($this->any())->method('getPriceIncludingTax')->will($this->returnValue('99.9500'));
        $product->expects($this->any())->method('getQuantity')->will($this->returnValue(100));
        $product->expects($this->any())->method('getId')->will($this->returnValue('999'));
        $order->setProduct($product);

        return $order;

    }
}