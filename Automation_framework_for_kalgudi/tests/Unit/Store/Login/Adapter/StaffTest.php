<?php

namespace Unit\Store\Login\Adapter;

use Store_Login_Adapter_Staff;
use PHPUnit_Framework_TestCase;

class StaffTest extends PHPUnit_Framework_TestCase
{
  public function staffPrefixes()
  {
    $prefixes = Store_Login_Adapter_Staff::$staffPrefixes;

    return array_map(function($prefix) {
      return array($prefix);
    }, $prefixes);
  }

  /**
   * @dataProvider staffPrefixes
   */
  public function testIsStaffUsernameIsTrueForValidPrefixes($prefix)
  {
    $username = $prefix . Store_Login_Adapter_Staff::$usernameSeparator . 'foo';
    $this->assertTrue(Store_Login_Adapter_Staff::isStaffUsername($username));
  }

  public function testIsStaffUsernameIsFalseForInvalidPrefix()
  {
    $username = 'foo' . Store_Login_Adapter_Staff::$usernameSeparator . 'foo';
    $this->assertFalse(Store_Login_Adapter_Staff::isStaffUsername($username));
  }

  public function testIsStaffUsernameIsFalseForNoPrefix()
  {
    $this->assertFalse(Store_Login_Adapter_Staff::isStaffUsername('foo'));

    $username = Store_Login_Adapter_Staff::$usernameSeparator . 'foo';
    $this->assertFalse(Store_Login_Adapter_Staff::isStaffUsername($username));
  }
}
