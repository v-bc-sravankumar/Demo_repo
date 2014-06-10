from lib.api_lib import *

faker = Factory.create()

COUPON_NAME = generate_random_string()
COUPON_CODE = generate_random_string()
COUPON_TYPE = "per_item_discount"
UPDATE_COUPON_NAME = generate_random_string()
UPDATE_COUPON_CODE = generate_random_string()
UPDATE_COUPON_TYPE = "free_shipping"

post_payload = {
            "name": COUPON_NAME,
            "code": COUPON_CODE,
            "type": COUPON_TYPE,
            "amount": 5,
            "min_purchase": 10,
            "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
            "enabled": True,
            "applies_to": {
                "entity": "categories",
                "ids": [0]
            },
            "max_uses": 100,
            "max_uses_per_customer": 1,
            "restricted_to": {"countries":["AU"]}
}

put_payload = {
            "name": UPDATE_COUPON_NAME,
            "code": UPDATE_COUPON_CODE,
            "type": UPDATE_COUPON_TYPE,
            "amount": 15,
            "min_purchase": 101,
            "enabled": False,
            "applies_to": {
                "entity": "categories",
                "ids": [0]
            },
            "max_uses": 10,
            "max_uses_per_customer": 2,
            "restricted_to": {"countries":["AU"]}
}

without_name_payload = {
                    "code": COUPON_CODE,
                    "type": COUPON_TYPE,
                    "amount": 5,
                    "min_purchase": 10,
                    "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                    "enabled": True,
                    "applies_to": {
                        "entity": "categories",
                        "ids": [0]
                    },
                    "max_uses": 100,
                    "max_uses_per_customer": 1,
                    "restricted_to": {"countries":["AU"]}
}

without_code_payload = {
                    "name": COUPON_NAME,
                    "type": COUPON_TYPE,
                    "amount": 5,
                    "min_purchase": 10,
                    "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                    "enabled": True,
                    "applies_to": {
                        "entity": "categories",
                        "ids": [0]
                    },
                    "max_uses": 100,
                    "max_uses_per_customer": 1,
                    "restricted_to": {"countries":["AU"]}
}

invalid_type_payload = {
                    "name": COUPON_NAME,
                    "code": COUPON_CODE,
                    "type": "abc123",
                    "amount": 5,
                    "min_purchase": 10,
                    "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                    "enabled": True,
                    "applies_to": {
                        "entity": "categories",
                        "ids": [0]
                    },
                    "max_uses": 100,
                    "max_uses_per_customer": 1,
                    "restricted_to": {"countries":["AU"]}
}

without_type_payload = {
                    "name": COUPON_NAME,
                    "code": COUPON_CODE,
                    "amount": 5,
                    "min_purchase": 10,
                    "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                    "enabled": True,
                    "applies_to": {
                        "entity": "categories",
                        "ids": [0]
                    },
                    "max_uses": 100,
                    "max_uses_per_customer": 1,
                    "restricted_to": {"countries":["AU"]}
}

invalid_amount_payload = {
                      "name": COUPON_NAME,
                      "code": COUPON_CODE,
                      "type": COUPON_TYPE,
                      "amount": "abc123",
                      "min_purchase": 10,
                      "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                      "enabled": True,
                      "applies_to": {
                        "entity": "categories",
                        "ids": [0]
                      },
                      "max_uses": 100,
                      "max_uses_per_customer": 1,
                      "restricted_to": {"countries":["AU"]}
}

without_amount_payload = {
                      "name": COUPON_NAME,
                      "code": COUPON_CODE,
                      "type": COUPON_TYPE,
                      "min_purchase": 10,
                      "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                      "enabled": True,
                      "applies_to": {
                        "entity": "categories",
                        "ids": [0]
                      },
                      "max_uses": 100,
                      "max_uses_per_customer": 1,
                      "restricted_to": {"countries":["AU"]}
}

invalid_min_purchase_payload = {
                            "name": COUPON_NAME,
                            "code": COUPON_CODE,
                            "type": COUPON_TYPE,
                            "amount": 5,
                            "min_purchase": "abcd",
                            "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                            "enabled": True,
                            "applies_to": {
                                "entity": "categories",
                                "ids": [0]
                            },
                            "max_uses": 100,
                            "max_uses_per_customer": 1,
                            "restricted_to": {"countries":["AU"]}
}

invalid_expires_payload = {
                       "name": COUPON_NAME,
                       "code": COUPON_CODE,
                       "type": COUPON_TYPE,
                       "amount": 5,
                       "min_purchase": 10,
                       "expires": "1234",
                       "enabled": True,
                       "applies_to": {
                           "entity": "categories",
                           "ids": [0]
                       },
                       "max_uses": 100,
                       "max_uses_per_customer": 1,
                       "restricted_to": {"countries":["AU"]}
}

invalid_enabled_payload = {
                       "name": COUPON_NAME,
                       "code": COUPON_CODE,
                       "type": COUPON_TYPE,
                       "amount": 5,
                       "min_purchase": 10,
                       "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                       "enabled": "False123",
                       "applies_to": {
                           "entity": "categories",
                           "ids": [0]
                       },
                       "max_uses": 100,
                       "max_uses_per_customer": 1,
                       "restricted_to": {"countries":["AU"]}
}

invalid_entity_payload = {
                      "name": COUPON_NAME,
                      "code": COUPON_CODE,
                      "type": COUPON_TYPE,
                      "amount": 5,
                      "min_purchase": 10,
                      "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                      "enabled": True,
                      "applies_to": {
                          "entity": "1234",
                          "ids": [0]
                      },
                      "max_uses": 100,
                      "max_uses_per_customer": 1,
                      "restricted_to": {"countries":["AU"]}
}

without_entity_payload = {
                      "name": COUPON_NAME,
                      "code": COUPON_CODE,
                      "type": COUPON_TYPE,
                      "amount": 5,
                      "min_purchase": 10,
                      "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                      "enabled": True,
                      "applies_to": {
                          "ids": [0]
                      },
                      "max_uses": 100,
                      "max_uses_per_customer": 1,
                      "restricted_to": {"countries":["AU"]}
}

invalid_id_payload = {
                  "name": COUPON_NAME,
                  "code": COUPON_CODE,
                  "type": COUPON_TYPE,
                  "amount": 5,
                  "min_purchase": 10,
                  "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                  "enabled": True,
                  "applies_to": {
                      "entity": "categories",
                      "ids": [100]
                  },
                  "max_uses": 100,
                  "max_uses_per_customer": 1,
                  "restricted_to": {"countries":["AU"]}
}

invalid_ids_payload = {
                  "name": COUPON_NAME,
                  "code": COUPON_CODE,
                  "type": COUPON_TYPE,
                  "amount": 5,
                  "min_purchase": 10,
                  "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                  "enabled": True,
                  "applies_to": {
                      "entity": "categories",
                      "ids": "123abc"
                  },
                  "max_uses": 100,
                  "max_uses_per_customer": 1,
                  "restricted_to": {"countries":["AU"]}
}

invalid_max_uses_payload = {
                        "name": COUPON_NAME,
                        "code": COUPON_CODE,
                        "type": COUPON_TYPE,
                        "amount": 5,
                        "min_purchase": 10,
                        "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                        "enabled": True,
                        "applies_to": {
                            "entity": "categories",
                            "ids": [0]
                        },
                        "max_uses": "abc",
                        "max_uses_per_customer": 1,
                        "restricted_to": {"countries":["AU"]}
}

invalid_max_uses_per_customer_payload = {
                                     "name": COUPON_NAME,
                                     "code": COUPON_CODE,
                                     "type": COUPON_TYPE,
                                     "amount": 5,
                                     "min_purchase": 10,
                                     "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                                     "enabled": True,
                                     "applies_to": {
                                         "entity": "categories",
                                         "ids": [0]
                                     },
                                     "max_uses": 100,
                                     "max_uses_per_customer": "abc",
                                     "restricted_to": {"countries":["AU"]}
}

invalid_countries_payload = {
                         "name": COUPON_NAME,
                         "code": COUPON_CODE,
                         "type": COUPON_TYPE,
                         "amount": 5,
                         "min_purchase": 10,
                         "expires": "Thu, 04 Oct 2012 03:24:40 +0000",
                         "enabled": True,
                         "applies_to": {
                             "entity": "categories",
                             "ids": [0]
                         },
                         "max_uses": 100,
                         "max_uses_per_customer": 1,
                         "restricted_to": {"countries":["123"]}
}
