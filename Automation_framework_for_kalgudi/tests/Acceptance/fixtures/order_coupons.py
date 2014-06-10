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

SHIPPING_FIRST_NAME = faker.firstName()
SHIPPING_LAST_NAME = faker.lastName()
SHIPPING_COMPANY = faker.company()
SHIPPING_STREET_ADD1 = faker.buildingNumber().lstrip("0")
SHIPPING_STREET_ADD2 = faker.streetName()
SHIPPING_CITY = faker.city()
SHIPPING_PHONE = faker.phoneNumber()
SHIPPING_STATE = 'New South Wales'
SHIPPING_POSTCODE = '2222'

COUPON_NAME = generate_random_string()
COUPON_CODE = generate_random_string()
COUPON_TYPE = "per_item_discount"

MANUAL_PAYMENT_NAME = faker.name()

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
    'staff_notes': "",
    'customer_message': "",
    'discount_amount': 5,
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

post_coupon_payload = {
    "name": COUPON_NAME,
    "code": COUPON_CODE,
    "type": COUPON_TYPE,
    "amount": 65,
    "min_purchase": 0,
    "enabled": True,
    "applies_to": {
        "entity": "categories",
        "ids": [0]
    },
    "max_uses": 100,
    "max_uses_per_customer": 1,
    "restricted_to": {"countries":["AU"]}
}
