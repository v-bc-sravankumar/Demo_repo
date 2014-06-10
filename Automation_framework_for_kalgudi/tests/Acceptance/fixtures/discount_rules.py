from lib.api_lib import *

PRODUCT_NAME = generate_random_string()

post_product_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "availability": "available",
    "option_set_id": 14
}

post_discount_payload = {
    "min": 100,
    "max": 500,
    "type": "price",
    "type_value": 2
}

put_discount_payload = {
    "min": 200,
    "max": 300,
    "type": "fixed",
    "type_value": 10
}

# Invalid Payloads for Discount Rules
without_type_payload = {
    "min": 400,
    "max": 600,
    "type_value": 2
}

without_type_value_payload = {
    "min": 400,
    "max": 600,
    "type": "price"
}

invalid_min_payload = {
    "min": "abcd",
    "max": 600,
    "type": "price",
    "type_value": 2
}

invalid_max_payload = {
    "min": 400,
    "max": "abcd",
    "type": "price",
    "type_value": 2
}

invalid_type_payload = {
    "min": 400,
    "max": 600,
    "type": 1234,
    "type_value": 2
}

invalid_type_value_payload = {
    "min": 400,
    "max": 600,
    "type": "price",
    "type_value": "abcd"
}
