<?php

namespace Unit\Quote;

use ISC_QUOTE_ITEM;

class QuoteItemTest extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    \Store\Language::parseLangFile(ISC_BASE_PATH . '/language/en/common.ini');
  }

  /**
   * @expectedException ISC_QUOTE_EXCEPTION
   * @expectedExceptionMessage quantity
   */
  public function testSetQuantityWithZeroThrowsException()
  {
    $item = new ISC_QUOTE_ITEM();
    $item->setQuantity(0);
  }

  /**
   * @expectedException ISC_QUOTE_EXCEPTION
   * @expectedExceptionMessage quantity
   */
  public function testSetQuantityWithNegativeThrowsException()
  {
    $item = new ISC_QUOTE_ITEM();
    $item->setQuantity(-1);
  }

  /**
   * @expectedException ISC_QUOTE_EXCEPTION
   * @expectedExceptionMessage quantity
   */
  public function testSetQuantityWithNonNumericThrowsException()
  {
    $item = new ISC_QUOTE_ITEM();
    $item->setQuantity("foo");
  }

  public function getTypeAsStringDataProvider()
  {
    return array(
      array(PT_PHYSICAL, 'physical'),
      array(PT_DIGITAL, 'digital'),
      array(PT_GIFTCERTIFICATE, 'giftcertificate'),
      array(PT_VIRTUAL, 'virtual'),
      array(0, ''),
    );
  }

  /**
   * @dataProvider getTypeAsStringDataProvider
   */
  public function testGetTypeAsString($type, $string)
  {
    $item = new ISC_QUOTE_ITEM();
    $item->setType($type);

    $this->assertEquals($string, $item->getTypeAsString());
  }
}
