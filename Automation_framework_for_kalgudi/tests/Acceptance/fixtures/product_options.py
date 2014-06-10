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
