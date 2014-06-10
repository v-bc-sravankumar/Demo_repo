<?php
use Store\Product\Sku\Template;
use Store\Product\Sku\Template\Token;
use Store\Product\Sku\Template\Rule\FixedValueRule;
use Store\Product\Sku\Template\Rule\FirstLettersRule;
use Store\Product\Sku\Template\Rule\UniqueValueRule;
use Store\Product\Sku\Template\Rule\OptionMapRule;

class TemplateTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException Store\Product\Sku\Template\Error\InvalidTokenException
     */
    public function testNewInstanceWithEmptyTokens()
    {
        new Template(array());
    }

    /**
     * @expectedException Store\Product\Sku\Template\Error\InvalidTokenException
     */
    public function testNewInstanceWithNotTokens()
    {
        new Template(array(1, 2, 3));
    }

    public function testGenerateWithFixedToken()
    {
        $token = new Token(Token::TYPE_FIXED);
        $token->setRule(new FixedValueRule('abc'));
        $template = new Template(array($token));

        $this->assertEquals('ABC', $template->generate(array(), array()));
    }

    public function testGenerateWithProductName()
    {
        $token = new Token(Token::TYPE_PRODUCT);
        $token->setRule(new FirstLettersRule());
        $template = new Template(array($token));

        $this->assertEquals('TES', $template->generate(array('name' => 'test product'), array()));
    }

    public function testGenerateWithOptionLabel()
    {
        $token = new Token(Token::TYPE_OPTION);
        $token->setRule(new FirstLettersRule());
        $token->setData('option_id');
        $template = new Template(array($token));

        $attributeValue = new Store_Attribute_Value();
        $attributeValue->setLabel('option_value');

        $this->assertEquals('OPT', $template->generate(array(), array('option_id' => $attributeValue)));
    }

    public function testGenerateWithOptionMap()
    {
        $token = new Token(Token::TYPE_OPTION);
        $token->setRule(new OptionMapRule(array('1' => 'mapped_value_1')));
        $token->setData('option_id');
        $template = new Template(array($token));

        $attributeValue = new Store_Attribute_Value();
        $attributeValue->setId('1');

        $this->assertEquals('MAPPED_VALUE_1', $template->generate(array(), array('option_id' => $attributeValue)));
    }

    public function testGenerateWithUniqueValue()
    {
        $token = new Token(Token::TYPE_UNIQUE);
        $token->setRule(new UniqueValueRule());
        $template = new Template(array($token));

        $this->assertNotNull($template->generate(array(), array()));
    }

    public function testGenerateShouldResolveDuplicateSku()
    {
        $token = new Token(Token::TYPE_PRODUCT);
        $token->setRule(new FirstLettersRule());
        $template = new Template(array($token));

        $skus = array();
        $skus[] = $template->generate(array('name' => 'rat'), array());
        $skus[] = $template->generate(array('name' => 'rat'), array());
        $skus[] = $template->generate(array('name' => 'rat'), array());

        $this->assertEquals(array('RAT', 'RAT-1', 'RAT-2'), $skus);
    }
}
