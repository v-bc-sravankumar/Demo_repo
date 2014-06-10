<?php

namespace Integration\DataModel;

use DataModel_ApiFinder;
use Store_Customer;
use Store_Coupon;
use PHPUnit_Framework_TestCase;

class ApiFinderTest extends PHPUnit_Framework_TestCase
{
    protected function createCustomer()
    {
        $data = array(
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
        );

        return DataModel_ApiFinder::createObject('Customers', $data);
    }

    protected function deleteCustomer($id)
    {
        $customer = new Store_Customer();
        if (!$customer->load($id)) {
            $this->fail('Could not load customer ' . $id);
        }
        $customer->delete();
    }

    public function testCreateObjectSucceeds()
    {
        $result = $this->createCustomer();

        $this->assertEquals('John', $result['first_name']);
        $this->assertEquals('Smith', $result['last_name']);
        $this->assertEquals('john.smith@example.com', $result['email']);

        $this->deleteCustomer($result['id']);
    }

    public function testCreateObjectSucceedsWhenWritingToReadOnlyField()
    {
        $data = array(
            'name' => 'Create object coupon',
            'code' => 'TESTCOUPON',
            'type' => 'per_total_discount',
            'amount' => 12,
            'applies_to' => array(
                'entity' => 'categories',
                'ids' => array(0),
            ),
            'num_uses' => 5, // read only field
        );

        $result = DataModel_ApiFinder::createObject('Coupons', $data);

        foreach ($data as $field => $value) {
            $this->assertEquals($value, $result[$field], "Mismatched value for $field (expected $value, was " . $result[$field] . ").");
        }

        $coupon = new Store_Coupon();
        if (!$coupon->load($result['id'])) {
            $this->fail('Could not load coupon ' . $result['id']);
        }
        $coupon->delete();
    }

    public function testUpdateObjectSucceeds()
    {
        $result = $this->createCustomer();
        $id = $result['id'];

        $data = $result;

        unset($data['id']);
        unset($data['date_created']);
        unset($data['date_modified']);
        unset($data['addresses']);

        $data['first_name'] = 'Bob';

        $result = DataModel_ApiFinder::updateObject('Customers', $id, $data);

        $this->assertEquals($data['first_name'], $result['first_name']);

        $this->deleteCustomer($id);
    }

    public function testDeleteObjectSucceeds()
    {
        $customer = $this->createCustomer();
        $id = $customer['id'];

        $result = DataModel_ApiFinder::deleteObject('Customers', $id);

        $this->assertEquals($customer, $result);

        $this->assertEquals(0, Store_Customer::find($id)->count());
    }
}

