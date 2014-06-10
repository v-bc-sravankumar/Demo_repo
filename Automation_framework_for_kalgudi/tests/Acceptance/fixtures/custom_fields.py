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

post_custom_payload = {
    "name": "Release Date",
    "text": "2013-12-25"
}

put_custom_payload = {
    "name": "Release Date",
    "text": "2013-12-31"
}
# Invalid Payloads

without_name_payload = {
    "text": "2013-12-31"
}

without_text_payload = {
    "name": "Release Date"
}
