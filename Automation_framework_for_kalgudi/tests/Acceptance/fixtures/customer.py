from lib.api_lib import *

faker = Factory.create()

FIRST_NAME = faker.firstName() + generate_random_string()
LAST_NAME = faker.lastName() + generate_random_string()
COMPANY = faker.company()
PHONE = faker.phoneNumber().strip("0")
EMAIL = faker.email()
EMAIL = EMAIL.translate(None,",!;#'?$%^&*()-~")
STREET_ADD1 = faker.buildingNumber().strip("0")
STREET_ADD2 = faker.streetName()
CITY = faker.city()
STATE = 'New South Wales'
POSTCODE = '2000'
UPDATE_FIRST_NAME = faker.firstName() + generate_random_string()
UPDATE_LAST_NAME = faker.lastName() + generate_random_string()
UPDATE_COMPANY = faker.company()
UPDATE_EMAIL = faker.email()
UPDATE_EMAIL = UPDATE_EMAIL.translate(None,",!;#'?$%^&*()-~")
UPDATE_STREET_ADD1 = faker.buildingNumber().strip("0")
UPDATE_STREET_ADD2 = faker.streetName()
UPDATE_CITY = faker.city()
UPDATE_PHONE = faker.phoneNumber().strip("0")
UPDATE_STATE = 'Victoria'
UPDATE_POSTCODE = '3000'

# Customer Group
GROUP_NAME = faker.word() + generate_random_string()
UPDATE_GROUP_NAME = faker.word() + generate_random_string()

post_customer_payload =  {
    "company": COMPANY,
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "email": EMAIL,
    "phone": PHONE,
    "store_credit": "10",
    "registration_ip_address": "1.1.1.1",
    "customer_group_id": 0,
    "notes": "Automation Testing created this customer",
    "_authentication": {
        "password": "12w69Y217PYR96J",
        "password_confirmation": "12w69Y217PYR96J"
    }
}

put_customer_payload =  {
    "company": UPDATE_COMPANY,
    "first_name": UPDATE_FIRST_NAME,
    "last_name": UPDATE_LAST_NAME,
    "email": UPDATE_EMAIL,
    "phone": UPDATE_PHONE,
    "store_credit": "1",
    "registration_ip_address": "2.2.2.2",
    "customer_group_id": 0,
    "notes": "Automation Testing Updated this customer",
    "_authentication": {
        "password": "22w69Y217PYR96J",
        "password_confirmation": "22w69Y217PYR96J"
    }
}

post_address_payload =  {
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "city": CITY,
    "state": STATE,
    "zip": POSTCODE,
    "country": "Australia",
    "phone": PHONE
}


put_address_payload =  {
    "first_name": UPDATE_FIRST_NAME,
    "last_name": UPDATE_LAST_NAME,
    "company": UPDATE_COMPANY,
    "street_1": UPDATE_STREET_ADD1,
    "street_2": UPDATE_STREET_ADD2,
    "city": UPDATE_CITY,
    "state": UPDATE_STATE,
    "zip": UPDATE_POSTCODE,
    "country": "Australia",
    "phone": UPDATE_PHONE
}

#Customer Group
post_customer_group_payload = {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access":{
        "type":"all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.50
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5.00
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12.00
    }
    ]
}

put_customer_group_payload = {
    "name": UPDATE_GROUP_NAME,
    "is_default": False,
    "category_access":{
        "type":"none"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "fixed",
        "amount": 10
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "price",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "percent",
        "amount": 2.5
    }
    ]
}

#Invalid Payloads for Customer
required_firstName_payload = {
    "company": COMPANY,
    "last_name": LAST_NAME,
    "email": EMAIL,
    "phone": PHONE,
    "store_credit": "10",
    "registration_ip_address": "1.1.1.1",
    "customer_group_id": 0,
    "notes": "Automation Testing created this customer",
    "_authentication": {
        "password": "12w69Y217PYR96J",
        "password_confirmation": "12w69Y217PYR96J"
    }
}

required_lastName_payload = {
    "company": COMPANY,
    "first_name": FIRST_NAME,
    "email": EMAIL,
    "phone": PHONE,
    "store_credit": "10",
    "registration_ip_address": "1.1.1.1",
    "customer_group_id": 0,
    "notes": "Automation Testing created this customer",
    "_authentication": {
        "password": "12w69Y217PYR96J",
        "password_confirmation": "12w69Y217PYR96J"
    }
}

required_email_payload = {
    "company": COMPANY,
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "phone": PHONE,
    "store_credit": "10",
    "registration_ip_address": "1.1.1.1",
    "customer_group_id": 0,
    "notes": "Automation Testing created this customer",
    "_authentication": {
        "password": "12w69Y217PYR96J",
        "password_confirmation": "12w69Y217PYR96J"
    }
}

invalid_email_payload = {
    "company": COMPANY,
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "email": "abcd",
    "phone": PHONE,
    "store_credit": "10",
    "registration_ip_address": "1.1.1.1",
    "customer_group_id": 0,
    "notes": "Automation Testing created this customer",
    "_authentication": {
        "password": "12w69Y217PYR96J",
        "password_confirmation": "12w69Y217PYR96J"
    }
}

invalid_password_confirmation_payload = {
    "company": COMPANY,
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "email": "joe@example.com",
    "phone": PHONE,
    "store_credit": "10",
    "registration_ip_address": "1.1.1.1",
    "customer_group_id": 0,
    "notes": "Automation Testing created this customer",
    "_authentication": {
        "password_confirmation": "12w69Y217PYR96J"
    }
}

invalid_customer_group_id_payload = {
    "company": COMPANY,
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "email": EMAIL,
    "phone": PHONE,
    "store_credit": "10",
    "registration_ip_address": "1.1.1.1",
    "customer_group_id": "group id",
    "notes": "Automation Testing created this customer",
    "_authentication": {
        "password": "12w69Y217PYR96J",
        "password_confirmation": "12w69Y217PYR96J"
    }
}

#Invalid Payloads for Customer Address
required_firstName_payload_address = {
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "city": CITY,
    "state": STATE,
    "zip": POSTCODE,
    "country": "Australia",
    "phone": PHONE
}

required_lastName_payload_address = {
    "first_name": FIRST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "city": CITY,
    "state": STATE,
    "zip": POSTCODE,
    "country": "Australia",
    "phone": PHONE
}

required_street1_payload_address = {
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_2": STREET_ADD2,
    "city": CITY,
    "state": STATE,
    "zip": POSTCODE,
    "country": "Australia",
    "phone": PHONE
}

required_city_payload_address = {
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "state": STATE,
    "zip": POSTCODE,
    "country": "Australia",
    "phone": PHONE
}

required_state_payload_address = {
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "city": CITY,
    "zip": POSTCODE,
    "country": "Australia",
    "phone": PHONE
}

required_zip_payload_address = {
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "city": CITY,
    "state": STATE,
    "country": "Australia",
    "phone": PHONE
}

required_country_payload_address = {
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "city": CITY,
    "state": STATE,
    "zip": POSTCODE,
    "phone": PHONE
}

required_phone_payload_address = {
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "city": CITY,
    "state": STATE,
    "zip": POSTCODE,
    "country": "Australia"
}

invalid_country_payload_address = {
    "first_name": FIRST_NAME,
    "last_name": LAST_NAME,
    "company": COMPANY,
    "street_1": STREET_ADD1,
    "street_2": STREET_ADD2,
    "city": CITY,
    "state": STATE,
    "zip": POSTCODE,
    "country": "abcd",
    "phone": PHONE
}

# Invalid Payloads for customer Group
without_companyname_payload = {
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_customer_isdefault_payload =  {
    "name": GROUP_NAME,
    "is_default": "one",
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_categeoryaccess_payload =  {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": 1
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_type_payload =  {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "1",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_method_payload = {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "122",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_amount_payload =  {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": "ABC"
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_product_type_payload = {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "Other",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_product_id_payload =  {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": "one",
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_product_method_payload =  {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "One",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_product_amount_payload =  {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": "one"
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_category_payload =  {
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "Other",
        "category_id": 7,
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_category_id_payload ={
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": "one",
        "method": "price",
        "amount": 12
    }
    ]
}

invalid_discount_category_method_payload ={
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "One",
        "amount": 12
    }
    ]
}

invalid_discount_category_amount_payload ={
    "name": GROUP_NAME,
    "is_default": True,
    "category_access": {
        "type": "all"
    },
    "discount_rules": [
    {
        "type": "all",
        "method": "percent",
        "amount": 2.5
    },
    {
        "type": "product",
        "product_id": 33,
        "method": "percent",
        "amount": 5
    },
    {
        "type": "category",
        "category_id": 7,
        "method": "price",
        "amount": "One"
    }
    ]
}
