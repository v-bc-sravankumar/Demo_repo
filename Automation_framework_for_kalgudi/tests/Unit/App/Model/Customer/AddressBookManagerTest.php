<?php
namespace Unit\App\Model\Customer;

class AddressBookManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testPopulateAddressFromOrderExists()
    {
        $this->assertTrue(method_exists('\Store\Customer\AddressBookManager', 'populateAddressBookFromOrder'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid Customer
     */
    public function testPopulateFromAddressBookFailsWithNoCustomerId()
    {
        $order = new \Orders\Order();
        $customer = new \Store_Customer();
        $manager = new \Store\Customer\AddressBookManager($customer);
        $manager->populateAddressBookFromOrder($order);
    }

    /**
     * $order->getShippingAddresses() returns \Orders\Address objects
     */
    protected function getFakeShippingAddress()
    {
        $orderAddressRow = array(
            'id' => 24,
            'order_id' => 123,
            'first_name' => 'First',
            'last_name' => 'Last',
            'company' => 'Company',
            'address_1' => 'Line One',
            'address_2' => 'Line Two',
            'city' => 'City',
            'zip' => '2095',
            'country' => 'Australia',
            'country_iso2' => 'AU',
            'country_id' => 13,
            'state' => 'New South Wales',
            'state_id' => 209,
            'email' => 'foo@bar.com',
            'phone' => '555 12345',
            'form_session_id' => 0,
            'total_items' => 1,
        );
        $shippingAddress = new \Orders\Address($orderAddressRow);

        return $shippingAddress;

    }

    public function testMapOrderShippingAddressToCustomerAddressMapsProperly()
    {

        $customer = new \Store_Customer();
        $customer->setId(999);

        $manager = new \Store\Customer\AddressBookManager($customer);

        $reflection = new \ReflectionClass(get_class($manager));
        $method = $reflection->getMethod('mapOrderShippingAddressToCustomerAddress');
        $method->setAccessible(true);

        $shippingAddress = $this->getFakeShippingAddress();

        $customerAddress = $this->getMockCustomerAddress();

        $mappedAddress = $method->invokeArgs($manager, array($shippingAddress, $customerAddress));

        $this->assertInstanceOf('\Store_Customer_Address', $mappedAddress);
        $this->assertEquals(999, $mappedAddress->getCustomerId());
        $this->assertEquals('First', $mappedAddress->getFirstName());
        $this->assertEquals('Last', $mappedAddress->getLastName());
        $this->assertEquals('Line One', $mappedAddress->getAddressLine1());
        $this->assertEquals('Line Two', $mappedAddress->getAddressLine2());
        $this->assertEquals('New South Wales', $mappedAddress->getState());
        $this->assertEquals(209, $mappedAddress->getStateId());
        $this->assertEquals('2095', $mappedAddress->getZip());
        $this->assertEquals('City', $mappedAddress->getCity());
        $this->assertEquals('Australia', $mappedAddress->getCountryName());
        $this->assertEquals(13, $mappedAddress->getCountryId());
        $this->assertEquals('555 12345', $mappedAddress->getPhone());

    }

    protected function getFakeBillingAddress()
    {
        $billingAddress = new \StdClass;
        $billingAddress->first_name = 'First';
        $billingAddress->last_name = 'Last';
        $billingAddress->street_1 = 'Line One';
        $billingAddress->street_2 = 'Line Two';
        $billingAddress->city = 'City';
        $billingAddress->state = 'New South Wales';
        $billingAddress->zip = 'Zip';
        $billingAddress->country = 'Australia';
        $billingAddress->phone = '555 12345';
        return $billingAddress;
    }

    protected function getMockCustomerAddress()
    {
        $customerAddress = $this->getMock('\Store_Customer_Address', array('lookupCountryId', 'lookupCountryName', 'lookupStateId', 'lookupStateName'));
        $customerAddress->expects($this->any())->method('lookupCountryId')->will($this->returnValue(13));
        $customerAddress->expects($this->any())->method('lookupCountryName')->will($this->returnValue('Australia'));
        $customerAddress->expects($this->any())->method('lookupStateId')->will($this->returnValue(array('stateid' => 209)));
        $customerAddress->expects($this->any())->method('lookupStateName')->will($this->returnValue('New South Wales'));
        return $customerAddress;
    }

    public function testMapOrderBillingAddressToCustomerAddressMapsProperly()
    {

        $customer = new \Store_Customer();
        $customer->setId(999);

        $manager = new \Store\Customer\AddressBookManager($customer);

        $reflection = new \ReflectionClass(get_class($manager));
        $method = $reflection->getMethod('mapOrderBillingAddressToCustomerAddress');
        $method->setAccessible(true);

        $billingAddress = $this->getFakeBillingAddress();

        $customerAddress = $this->getMockCustomerAddress();

        $mappedAddress = $method->invokeArgs($manager, array($billingAddress, $customerAddress));

        $this->assertInstanceOf('\Store_Customer_Address', $mappedAddress);

        $this->assertEquals(999, $mappedAddress->getCustomerId());

        $this->assertEquals('First', $mappedAddress->getFirstName());
        $this->assertEquals('Last', $mappedAddress->getLastName());
        $this->assertEquals('Line One', $mappedAddress->getAddressLine1());
        $this->assertEquals('Line Two', $mappedAddress->getAddressLine2());
        $this->assertEquals('New South Wales', $mappedAddress->getState());
        $this->assertEquals(209, $mappedAddress->getStateId());
        $this->assertEquals('Zip', $mappedAddress->getZip());
        $this->assertEquals('City', $mappedAddress->getCity());
        $this->assertEquals('Australia', $mappedAddress->getCountryName());
        $this->assertEquals(13, $mappedAddress->getCountryId());
        $this->assertEquals('555 12345', $mappedAddress->getPhone());

    }

    public function testSaveAddressBookFromOrderWorks()
    {
        $customer = new \Store_Customer();
        $customer->setId(999);

        $mappedShippingAddress = $this->getMock('\Store_Customer_Address', array('save'));
        $mappedBillingAddress = $this->getMock('\Store_Customer_Address', array('save'));

        $shippingAddress = $this->getFakeShippingAddress();
        $billingAddress = $this->getFakeBillingAddress();

        $order = $this->getMock('\Orders\Order', array('getBillingAddress', 'getShippingAddresses'));
        $order->expects($this->any())->method('getBillingAddress')->will($this->returnValue($billingAddress));
        $order->expects($this->any())->method('getShippingAddresses')->will($this->returnValue(array($shippingAddress)));

        $manager = $this->getMock('\Store\Customer\AddressBookManager', array('mapOrderShippingAddressToCustomerAddress', 'mapOrderBillingAddressToCustomerAddress', 'saveToAddressBook'), array($customer));
        $manager->expects($this->once())->method('mapOrderShippingAddressToCustomerAddress')->with($shippingAddress)->will($this->returnValue($mappedShippingAddress));
        $manager->expects($this->once())->method('mapOrderBillingAddressToCustomerAddress')->with($billingAddress)->will($this->returnValue($mappedBillingAddress));
        $manager->expects($this->any())->method('saveToAddressBook')->with($mappedShippingAddress);
        $manager->expects($this->any())->method('saveToAddressBook')->with($mappedBillingAddress);

        $manager->populateAddressBookFromOrder($order);

    }

}