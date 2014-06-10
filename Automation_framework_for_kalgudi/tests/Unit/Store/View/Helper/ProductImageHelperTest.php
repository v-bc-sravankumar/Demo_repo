<?php

namespace Unit\Store\View\Helper;

use Store\View\Helper\ProductImageHelper;

class ProductImageHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetProductImageFromMixedSourcesWithEmptyImageIdThrowsException()
    {
        ProductImageHelper::getProductImageFromMixedSources(array('imageid' => 0));
    }
}
