<?php
use Store\Product\Sku\Template\Rule\FixedValueRule;

class FixedValueRuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Store\Product\Sku\Template\Rule\Error\InvalidRuleException
     */
    public function testNonScalarInput()
    {
        $rule = new FixedValueRule(array());
    }

    public function testApplyShouldReturnAlphaNumericValue()
    {
        $rule = new FixedValueRule("  *_*  big commerce  ");
        $this->assertEquals("BIGCOMMERCE", $rule->apply(null));
    }
}
