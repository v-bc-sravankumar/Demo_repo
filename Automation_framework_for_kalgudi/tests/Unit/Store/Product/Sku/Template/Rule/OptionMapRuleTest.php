<?php
use Store\Product\Sku\Template\Rule\OptionMapRule;

class OptionMapRuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Store\Product\Sku\Template\Rule\Error\InvalidRuleInputException
     */
    public function testApplyNonStoreAttributeValueInput()
    {
        $rule = new OptionMapRule(array());
        $rule->apply(1);
    }

    /**
     * @expectedException Store\Product\Sku\Template\Rule\Error\InvalidRuleInputException
     */
    public function testApplyStoreAttributeValueInputNotFound()
    {
        $rule = new OptionMapRule(array());

        $input = new Store_Attribute_Value();
        $input->setId(1);

        $rule->apply($input);
    }

    public function testApplyShouldReturnMappedValue()
    {
        $rule = new OptionMapRule(array("1" => "red"));

        $input = new Store_Attribute_Value();
        $input->setId(1);

        $this->assertEquals("RED", $rule->apply($input));
    }
}
