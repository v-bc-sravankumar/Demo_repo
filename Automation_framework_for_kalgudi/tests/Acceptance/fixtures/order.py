from lib.api_lib import *

faker = Factory.create()

BILLING_FIRST_NAME = faker.firstName()
BILLING_LAST_NAME = faker.lastName()
BILLING_COMPANY = faker.company()
BILLING_STREET_ADD1 = faker.buildingNumber().lstrip("0")
BILLING_STREET_ADD2 = faker.streetName()
BILLING_CITY = faker.city()
BILLING_PHONE = faker.phoneNumber()
BILLING_STATE = 'New South Wales'
BILLING_POSTCODE = '2000'
EMAIL = faker.email()
EMAIL = EMAIL.translate(None,",!;#'?$%^&*()-~")

UPDATE_BILLING_FIRST_NAME = faker.firstName()
UPDATE_BILLING_LAST_NAME = faker.lastName()
UPDATE_BILLING_COMPANY = faker.company()
UPDATE_BILLING_STREET_ADD1 = faker.buildingNumber().lstrip("0")
UPDATE_BILLING_STREET_ADD2 = faker.streetName()
UPDATE_BILLING_CITY = faker.city()
UPDATE_BILLING_PHONE = faker.phoneNumber()
UPDATE_BILLING_STATE = 'New South Wales'
UPDATE_BILLING_POSTCODE = '2000'


SHIPPING_FIRST_NAME = faker.firstName()
SHIPPING_LAST_NAME = faker.lastName()
SHIPPING_COMPANY = faker.company()
SHIPPING_STREET_ADD1 = faker.buildingNumber().lstrip("0")
SHIPPING_STREET_ADD2 = faker.streetName()
SHIPPING_CITY = faker.city()
SHIPPING_PHONE = faker.phoneNumber()
SHIPPING_STATE = 'New South Wales'
SHIPPING_POSTCODE = '2222'


UPDATE_FIRST_NAME = faker.firstName()
UPDATE_LAST_NAME = faker.lastName()
UPDATE_COMPANY = faker.company()
UPDATE_STREET_ADD1 = faker.buildingNumber().lstrip("0")
UPDATE_STREET_ADD2 = faker.streetName()
UPDATE_CITY = faker.city()
UPDATE_PHONE = faker.phoneNumber()
UPDATE_STATE = 'Victoria'
UPDATE_POSTCODE = '3000'


post_order_payload = {
    'customer_id': 0,
    'date_created': "Thu, 04 Oct 2012 03:24:40 +0000",
    'base_shipping_cost': "0.0000",
    'shipping_cost_ex_tax': "0.0000",
    'shipping_cost_inc_tax': "0.0000",
    'base_handling_cost': "0.0000",
    'handling_cost_ex_tax': "0.0000",
    'handling_cost_inc_tax': "0.0000",
    'base_wrapping_cost': "0.0000",
    'wrapping_cost_ex_tax': "0.0000",
    'wrapping_cost_inc_tax': "0.0000",
    'items_shipped': 0,
    'refunded_amount': "0.0000",
    'order_is_digital': False,
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 15,
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': BILLING_PHONE,
        'email': EMAIL
    },
    'shipping_addresses': [{
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    }],
    'products': [{
        'product_id': 75,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }, {
        'product_id': 74,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }]
}

put_order_status_payload = {
    'status_id': "2",
    'is_deleted': False
}

put_order_billing_address_payload = {
    'billing_address': {
        'first_name': UPDATE_BILLING_FIRST_NAME,
        'last_name': UPDATE_BILLING_LAST_NAME,
        'company': UPDATE_BILLING_COMPANY,
        'street_1': UPDATE_BILLING_STREET_ADD1,
        'street_2': UPDATE_BILLING_STREET_ADD2,
        'city': UPDATE_BILLING_CITY,
        'state': UPDATE_BILLING_STATE,
        'zip': UPDATE_BILLING_POSTCODE,
        'phone': UPDATE_BILLING_PHONE
    }
}


post_shipment_payload = {
    "tracking_number": "Test 001",
    "comments": "Shipment created using Test Automation script"
}

put_shipment_payload = {
    "tracking_number": "Test Update",
    "comments": "Shipment updated Test Automation script"
}

# Post Order with empty array & verify error returns
product_with_empty_array_payload = {
    'billing_address': {
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    },
    'products': []
}

# Create a product, Set order_quantity_minimum = 2
product_with_quantity_payload = {
    "name": generate_random_string(),
    "type": "physical",
    "price": "100",
    "weight": "1",
    "categories": [15],
    "availability": "available",
    "order_quantity_minimum": 2
}

product_with_2_quantity_payload =       {
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': BILLING_PHONE,
        'email': EMAIL
    }
}

#validations payloads
invalid_customer_id_payload = {
    'customer_id': "abc",
    'date_created': "Thu, 04 Oct 2012 03:24:40 +0000",
    'base_shipping_cost': "0.0000",
    'shipping_cost_ex_tax': "0.0000",
    'shipping_cost_inc_tax': "0.0000",
    'base_handling_cost': "0.0000",
    'handling_cost_ex_tax': "0.0000",
    'handling_cost_inc_tax': "0.0000",
    'base_wrapping_cost': "0.0000",
    'wrapping_cost_ex_tax': "0.0000",
    'wrapping_cost_inc_tax': "0.0000",
    'items_shipped': 0,
    'refunded_amount': "0.0000",
    'order_is_digital': False,
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 15,
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': BILLING_PHONE,
        'email': EMAIL
    },
    'shipping_addresses': [{
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    }],
    'products': [{
        'product_id': 75,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }, {
        'product_id': 74,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }]
}

invalid_date_created_payload = {
    'customer_id': 0,
    'date_created': "abc123",
    'base_shipping_cost': "0.0000",
    'shipping_cost_ex_tax': "0.0000",
    'shipping_cost_inc_tax': "0.0000",
    'base_handling_cost': "0.0000",
    'handling_cost_ex_tax': "0.0000",
    'handling_cost_inc_tax': "0.0000",
    'base_wrapping_cost': "0.0000",
    'wrapping_cost_ex_tax': "0.0000",
    'wrapping_cost_inc_tax': "0.0000",
    'items_shipped': 0,
    'refunded_amount': "0.0000",
    'order_is_digital': False,
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 15,
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': BILLING_PHONE,
        'email': EMAIL
    },
    'shipping_addresses': [{
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    }],
    'products': [{
        'product_id': 75,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }, {
        'product_id': 74,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }]
}

invalid_items_shipped_payload = {
    'customer_id': 0,
    'date_created': "Thu, 04 Oct 2012 03:24:40 +0000",
    'base_shipping_cost': "0.0000",
    'shipping_cost_ex_tax': "0.0000",
    'shipping_cost_inc_tax': "0.0000",
    'base_handling_cost': "0.0000",
    'handling_cost_ex_tax': "0.0000",
    'handling_cost_inc_tax': "0.0000",
    'base_wrapping_cost': "0.0000",
    'wrapping_cost_ex_tax': "0.0000",
    'wrapping_cost_inc_tax': "0.0000",
    'items_shipped': "abc",
    'refunded_amount': "0.0000",
    'order_is_digital': False,
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 15,
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': BILLING_PHONE,
        'email': EMAIL
    },
    'shipping_addresses': [{
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    }],
    'products': [{
        'product_id': 75,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }, {
        'product_id': 74,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }]
}

invalid_order_is_digital_payload = {
    'customer_id': 0,
    'date_created': "Thu, 04 Oct 2012 03:24:40 +0000",
    'base_shipping_cost': "0.0000",
    'shipping_cost_ex_tax': "0.0000",
    'shipping_cost_inc_tax': "0.0000",
    'base_handling_cost': "0.0000",
    'handling_cost_ex_tax': "0.0000",
    'handling_cost_inc_tax': "0.0000",
    'base_wrapping_cost': "0.0000",
    'wrapping_cost_ex_tax': "0.0000",
    'wrapping_cost_inc_tax': "0.0000",
    'items_shipped': 0,
    'refunded_amount': "0.0000",
    'order_is_digital': 123,
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 15,
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': BILLING_PHONE,
        'email': EMAIL
    },
    'shipping_addresses': [{
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    }],
    'products': [{
        'product_id': 75,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }, {
        'product_id': 74,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }]
}

invalid_billing_address_email_payload = {
    'customer_id': 0,
    'date_created': "Thu, 04 Oct 2012 03:24:40 +0000",
    'base_shipping_cost': "0.0000",
    'shipping_cost_ex_tax': "0.0000",
    'shipping_cost_inc_tax': "0.0000",
    'base_handling_cost': "0.0000",
    'handling_cost_ex_tax': "0.0000",
    'handling_cost_inc_tax': "0.0000",
    'base_wrapping_cost': "0.0000",
    'wrapping_cost_ex_tax': "0.0000",
    'wrapping_cost_inc_tax': "0.0000",
    'items_shipped': 0,
    'refunded_amount': "0.0000",
    'order_is_digital': False,
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 15,
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': BILLING_PHONE,
        'email': "abc123.com"
    },
    'shipping_addresses': [{
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    }],
    'products': [{
        'product_id': 75,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }, {
        'product_id': 74,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }]
}

without_country_billing_address_payload = {
    'customer_id': 0,
    'date_created': "Thu, 04 Oct 2012 03:24:40 +0000",
    'base_shipping_cost': "0.0000",
    'shipping_cost_ex_tax': "0.0000",
    'shipping_cost_inc_tax': "0.0000",
    'base_handling_cost': "0.0000",
    'handling_cost_ex_tax': "0.0000",
    'handling_cost_inc_tax': "0.0000",
    'base_wrapping_cost': "0.0000",
    'wrapping_cost_ex_tax': "0.0000",
    'wrapping_cost_inc_tax': "0.0000",
    'items_shipped': 0,
    'refunded_amount': "0.0000",
    'order_is_digital': False,
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 15,
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'phone': BILLING_PHONE,
        'email': EMAIL
    },
    'shipping_addresses': [{
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    }],
    'products': [{
        'product_id': 75,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }, {
        'product_id': 74,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }]
}

invalid_billing_address_country_payload = {
    'customer_id': 0,
    'date_created': "Thu, 04 Oct 2012 03:24:40 +0000",
    'base_shipping_cost': "0.0000",
    'shipping_cost_ex_tax': "0.0000",
    'shipping_cost_inc_tax': "0.0000",
    'base_handling_cost': "0.0000",
    'handling_cost_ex_tax': "0.0000",
    'handling_cost_inc_tax': "0.0000",
    'base_wrapping_cost': "0.0000",
    'wrapping_cost_ex_tax': "0.0000",
    'wrapping_cost_inc_tax': "0.0000",
    'items_shipped': 0,
    'refunded_amount': "0.0000",
    'order_is_digital': False,
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 15,
    'billing_address': {
        'first_name': BILLING_FIRST_NAME,
        'last_name': BILLING_LAST_NAME,
        'company': BILLING_COMPANY,
        'street_1': BILLING_STREET_ADD1,
        'street_2': BILLING_STREET_ADD2,
        'city': BILLING_CITY,
        'state': BILLING_STATE,
        'zip': BILLING_POSTCODE,
        'country': 123,
        'country_iso2': "AU",
        'phone': BILLING_PHONE,
        'email': EMAIL
    },
    'shipping_addresses': [{
        'first_name': SHIPPING_FIRST_NAME,
        'last_name': SHIPPING_LAST_NAME,
        'company': SHIPPING_COMPANY,
        'street_1': SHIPPING_STREET_ADD1,
        'street_2': SHIPPING_STREET_ADD2,
        'city': SHIPPING_CITY,
        'state': SHIPPING_STATE,
        'zip': SHIPPING_POSTCODE,
        'country': "Australia",
        'country_iso2': "AU",
        'phone': SHIPPING_PHONE,
        'email': EMAIL
    }],
    'products': [{
        'product_id': 75,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }, {
        'product_id': 74,
        'quantity': 1,
        'price_inc_tax': 10,
        'price_ex_tax': 10
    }]
}

#shippping Address vallidations

without_shipping_country_payload={
    "customer_id": 0,
    "date_created": "Thu, 04 Oct 2012 03:24:40 +0000",
    "base_shipping_cost": "0.0000",
    "shipping_cost_ex_tax": "0.0000",
    "shipping_cost_inc_tax": "0.0000",
    "base_handling_cost": "0.0000",
    "handling_cost_ex_tax": "0.0000",
    "handling_cost_inc_tax": "0.0000",
    "base_wrapping_cost": "0.0000",
    "wrapping_cost_ex_tax": "0.0000",
    "wrapping_cost_inc_tax": "0.0000",
    "items_shipped": 0,
    "refunded_amount": "0.0000",

    "staff_notes": "",
    "customer_message": "",
    "discount_amount": 15,
    "billing_address": {
        "first_name": "BILLING_FIRST_NAME",
        "last_name": "BILLING_LAST_NAME",
        "company": "BILLING_COMPANY",
        "street_1": "BILLING_STREET_ADD1",
        "street_2": "BILLING_STREET_ADD2",
        "city": "BILLING_CITY",
        "state": "BILLING_STATE",
        "zip": "BILLING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "BILLING_PHONE",
        "email": "siri@gmail.com"
    },
    "shipping_addresses": [
    {
        "first_name": "SHIPPING_FIRST_NAME",
        "last_name": "SHIPPING_LAST_NAME",
        "company": "SHIPPING_COMPANY",
        "street_1": "SHIPPING_STREET_ADD1",
        "street_2": "SHIPPING_STREET_ADD2",
        "city": "SHIPPING_CITY",
        "state": "SHIPPING_STATE",
        "zip": "SHIPPING_POSTCODE",
        "country": "",
        "country_iso2": "AU",
        "phone": "SHIPPING_PHONE",
        "email": "siri@gmail.com"
    }
    ],
        "products": [
        {
            "product_id": 75,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        },
        {
            "product_id": 74,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        }
    ]
}

without_shipping_country_iso2_payload={
    "customer_id": 0,
    "date_created": "Thu, 04 Oct 2012 03:24:40 +0000",
    "base_shipping_cost": "0.0000",
    "shipping_cost_ex_tax": "0.0000",
    "shipping_cost_inc_tax": "0.0000",
    "base_handling_cost": "0.0000",
    "handling_cost_ex_tax": "0.0000",
    "handling_cost_inc_tax": "0.0000",
    "base_wrapping_cost": "0.0000",
    "wrapping_cost_ex_tax": "0.0000",
    "wrapping_cost_inc_tax": "0.0000",
    "items_shipped": 0,
    "refunded_amount": "0.0000",

    "staff_notes": "",
    "customer_message": "",
    "discount_amount": 15,
    "billing_address": {
        "first_name": "BILLING_FIRST_NAME",
        "last_name": "BILLING_LAST_NAME",
        "company": "BILLING_COMPANY",
        "street_1": "BILLING_STREET_ADD1",
        "street_2": "BILLING_STREET_ADD2",
        "city": "BILLING_CITY",
        "state": "BILLING_STATE",
        "zip": "BILLING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "BILLING_PHONE",
        "email": "siri@gmail.com"
    },
    "shipping_addresses": [
    {
        "first_name": "SHIPPING_FIRST_NAME",
        "last_name": "SHIPPING_LAST_NAME",
        "company": "SHIPPING_COMPANY",
        "street_1": "SHIPPING_STREET_ADD1",
        "street_2": "SHIPPING_STREET_ADD2",
        "city": "SHIPPING_CITY",
        "state": "SHIPPING_STATE",
        "zip": "SHIPPING_POSTCODE",
        "country": "Australia",
        "country_iso2": "",
        "phone": "SHIPPING_PHONE",
        "email": "siri@gmail.com"
    }
    ],
        "products": [
        {
            "product_id": 75,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        },
        {
            "product_id": 74,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        }
    ]
}

invalid_shipping_email_payload= {
    "customer_id": 0,
    "date_created": "Thu, 04 Oct 2012 03:24:40 +0000",
    "base_shipping_cost": "0.0000",
    "shipping_cost_ex_tax": "0.0000",
    "shipping_cost_inc_tax": "0.0000",
    "base_handling_cost": "0.0000",
    "handling_cost_ex_tax": "0.0000",
    "handling_cost_inc_tax": "0.0000",
    "base_wrapping_cost": "0.0000",
    "wrapping_cost_ex_tax": "0.0000",
    "wrapping_cost_inc_tax": "0.0000",
    "items_shipped": 0,
    "refunded_amount": "0.0000",

    "staff_notes": "",
    "customer_message": "",
    "discount_amount": 15,
    "billing_address": {
        "first_name": "BILLING_FIRST_NAME",
        "last_name": "BILLING_LAST_NAME",
        "company": "BILLING_COMPANY",
        "street_1": "BILLING_STREET_ADD1",
        "street_2": "BILLING_STREET_ADD2",
        "city": "BILLING_CITY",
        "state": "BILLING_STATE",
        "zip": "BILLING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "BILLING_PHONE",
        "email": "siri@gmail.com"
    },
    "shipping_addresses": [
    {
        "first_name": "SHIPPING_FIRST_NAME",
        "last_name": "SHIPPING_LAST_NAME",
        "company": "SHIPPING_COMPANY",
        "street_1": "SHIPPING_STREET_ADD1",
        "street_2": "SHIPPING_STREET_ADD2",
        "city": "SHIPPING_CITY",
        "state": "SHIPPING_STATE",
        "zip": "SHIPPING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "SHIPPING_PHONE",
        "email": "sirigmail.com"
    }
    ],
        "products": [
        {
            "product_id": 75,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        },
        {
            "product_id": 74,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        }
    ]
}

without_shipping_product_id_payload={
    "customer_id": 0,
    "date_created": "Thu, 04 Oct 2012 03:24:40 +0000",
    "base_shipping_cost": "0.0000",
    "shipping_cost_ex_tax": "0.0000",
    "shipping_cost_inc_tax": "0.0000",
    "base_handling_cost": "0.0000",
    "handling_cost_ex_tax": "0.0000",
    "handling_cost_inc_tax": "0.0000",
    "base_wrapping_cost": "0.0000",
    "wrapping_cost_ex_tax": "0.0000",
    "wrapping_cost_inc_tax": "0.0000",
    "items_shipped": 0,
    "refunded_amount": "0.0000",

    "staff_notes": "",
    "customer_message": "",
    "discount_amount": 15,
    "billing_address": {
        "first_name": "BILLING_FIRST_NAME",
        "last_name": "BILLING_LAST_NAME",
        "company": "BILLING_COMPANY",
        "street_1": "BILLING_STREET_ADD1",
        "street_2": "BILLING_STREET_ADD2",
        "city": "BILLING_CITY",
        "state": "BILLING_STATE",
        "zip": "BILLING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "BILLING_PHONE",
        "email": "siri@gmail.com"
    },
    "shipping_addresses": [
    {
        "first_name": "SHIPPING_FIRST_NAME",
        "last_name": "SHIPPING_LAST_NAME",
        "company": "SHIPPING_COMPANY",
        "street_1": "SHIPPING_STREET_ADD1",
        "street_2": "SHIPPING_STREET_ADD2",
        "city": "SHIPPING_CITY",
        "state": "SHIPPING_STATE",
        "zip": "SHIPPING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "SHIPPING_PHONE",
        "email": "siri@gmail.com"
    }
    ],
        "products": [
        {

            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        },
        {
            "product_id": 74,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        }
    ]
}

without_shipping_product_quantity_payload = {
    "customer_id": 0,
    "date_created": "Thu, 04 Oct 2012 03:24:40 +0000",
    "base_shipping_cost": "0.0000",
    "shipping_cost_ex_tax": "0.0000",
    "shipping_cost_inc_tax": "0.0000",
    "base_handling_cost": "0.0000",
    "handling_cost_ex_tax": "0.0000",
    "handling_cost_inc_tax": "0.0000",
    "base_wrapping_cost": "0.0000",
    "wrapping_cost_ex_tax": "0.0000",
    "wrapping_cost_inc_tax": "0.0000",
    "items_shipped": 0,
    "refunded_amount": "0.0000",

    "staff_notes": "",
    "customer_message": "",
    "discount_amount": 15,
    "billing_address": {
        "first_name": "BILLING_FIRST_NAME",
        "last_name": "BILLING_LAST_NAME",
        "company": "BILLING_COMPANY",
        "street_1": "BILLING_STREET_ADD1",
        "street_2": "BILLING_STREET_ADD2",
        "city": "BILLING_CITY",
        "state": "BILLING_STATE",
        "zip": "BILLING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "BILLING_PHONE",
        "email": "siri@gmail.com"
    },
    "shipping_addresses": [
    {
        "first_name": "SHIPPING_FIRST_NAME",
        "last_name": "SHIPPING_LAST_NAME",
        "company": "SHIPPING_COMPANY",
        "street_1": "SHIPPING_STREET_ADD1",
        "street_2": "SHIPPING_STREET_ADD2",
        "city": "SHIPPING_CITY",
        "state": "SHIPPING_STATE",
        "zip": "SHIPPING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "SHIPPING_PHONE",
        "email": "siri@gmail.com"
    }
    ],
        "products": [
        {
            "product_id": 75,
            "quantity": "",
            "price_inc_tax": 10,
            "price_ex_tax": 10
        },
        {
            "product_id": 74,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        }
    ]
}

without_shipping_product_price_inc_tax_payload= {
    "customer_id": 0,
    "date_created": "Thu, 04 Oct 2012 03:24:40 +0000",
    "base_shipping_cost": "0.0000",
    "shipping_cost_ex_tax": "0.0000",
    "shipping_cost_inc_tax": "0.0000",
    "base_handling_cost": "0.0000",
    "handling_cost_ex_tax": "0.0000",
    "handling_cost_inc_tax": "0.0000",
    "base_wrapping_cost": "0.0000",
    "wrapping_cost_ex_tax": "0.0000",
    "wrapping_cost_inc_tax": "0.0000",
    "items_shipped": 0,
    "refunded_amount": "0.0000",

    "staff_notes": "",
    "customer_message": "",
    "discount_amount": 15,
    "billing_address": {
        "first_name": "BILLING_FIRST_NAME",
        "last_name": "BILLING_LAST_NAME",
        "company": "BILLING_COMPANY",
        "street_1": "BILLING_STREET_ADD1",
        "street_2": "BILLING_STREET_ADD2",
        "city": "BILLING_CITY",
        "state": "BILLING_STATE",
        "zip": "BILLING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "BILLING_PHONE",
        "email": "siri@gmail.com"
    },
    "shipping_addresses": [
    {
        "first_name": "SHIPPING_FIRST_NAME",
        "last_name": "SHIPPING_LAST_NAME",
        "company": "SHIPPING_COMPANY",
        "street_1": "SHIPPING_STREET_ADD1",
        "street_2": "SHIPPING_STREET_ADD2",
        "city": "SHIPPING_CITY",
        "state": "SHIPPING_STATE",
        "zip": "SHIPPING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "SHIPPING_PHONE",
        "email": "siri@gmail.com"
    }
    ],
        "products": [
        {
            "product_id": 75,
            "quantity": 1,

            "price_ex_tax": 10
        },
        {
            "product_id": 74,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        }
    ]
}

without_shipping_product_price_ex_tax_payload = {
    "customer_id": 0,
    "date_created": "Thu, 04 Oct 2012 03:24:40 +0000",
    "base_shipping_cost": "0.0000",
    "shipping_cost_ex_tax": "0.0000",
    "shipping_cost_inc_tax": "0.0000",
    "base_handling_cost": "0.0000",
    "handling_cost_ex_tax": "0.0000",
    "handling_cost_inc_tax": "0.0000",
    "base_wrapping_cost": "0.0000",
    "wrapping_cost_ex_tax": "0.0000",
    "wrapping_cost_inc_tax": "0.0000",
    "items_shipped": 0,
    "refunded_amount": "0.0000",

    "staff_notes": "",
    "customer_message": "",
    "discount_amount": 15,
    "billing_address": {
        "first_name": "BILLING_FIRST_NAME",
        "last_name": "BILLING_LAST_NAME",
        "company": "BILLING_COMPANY",
        "street_1": "BILLING_STREET_ADD1",
        "street_2": "BILLING_STREET_ADD2",
        "city": "BILLING_CITY",
        "state": "BILLING_STATE",
        "zip": "BILLING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "BILLING_PHONE",
        "email": "siri@gmail.com"
    },
    "shipping_addresses": [
    {
        "first_name": "SHIPPING_FIRST_NAME",
        "last_name": "SHIPPING_LAST_NAME",
        "company": "SHIPPING_COMPANY",
        "street_1": "SHIPPING_STREET_ADD1",
        "street_2": "SHIPPING_STREET_ADD2",
        "city": "SHIPPING_CITY",
        "state": "SHIPPING_STATE",
        "zip": "SHIPPING_POSTCODE",
        "country": "Australia",
        "country_iso2": "AU",
        "phone": "SHIPPING_PHONE",
        "email": "siri@gmail.com"
    }
    ],
        "products": [
        {
            "product_id": 75,
            "quantity": 1,
            "price_inc_tax": 10

        },
        {
            "product_id": 74,
            "quantity": 1,
            "price_inc_tax": 10,
            "price_ex_tax": 10
        }
    ]
}
