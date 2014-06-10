from lib.api_lib import *

PRODUCT_NAME = generate_random_string()
UPDATE_PRODUCT_NAME = generate_random_string()

post_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "availability": "available"
}

put_payload =  {
    "name": UPDATE_PRODUCT_NAME,
    "price": "111.10",
    "weight": "5.55",
    "categories": [14],
    "availability": "disabled"
}
