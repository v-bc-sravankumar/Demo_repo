<?php
use Store\Product\Sku\Template\Rule\AbbreviationRule;

class AbbreviationRuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Store\Product\Sku\Template\Rule\Error\InvalidRuleInputException
     */
    public function testApplyNonStringInput()
    {
        $rule = new AbbreviationRule();
        $rule->apply(1);
    }

    /**
     * @expectedException Store\Product\Sku\Template\Rule\Error\InvalidRuleInputException
     */
    public function testApplyNonAlphaNumericStringInput()
    {
        $rule = new AbbreviationRule();
        $rule->apply("**");
    }

    public function testApplyStringInput()
    {
        $rule = new AbbreviationRule();
        $this->assertEquals("BC", $rule->apply("big commerce"));
    }
}
