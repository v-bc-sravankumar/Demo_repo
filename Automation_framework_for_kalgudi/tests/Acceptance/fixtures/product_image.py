from lib.api_lib import *

PRODUCT_NAME = generate_random_string()
UPDATE_PRODUCT_NAME = generate_random_string()


post_payload = {
                  'name': PRODUCT_NAME,
                  'type': "physical",
                  'price': "100.10",
                  'weight': "1.11",
                  'categories': [15],
                  'availability': "available"
                }

post_image_payload = {
                        'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/800px-England_vs_South_Africa.jpg",
                        'is_thumbnail': False,
                        'sort_order': 0,
                        'description': "Uploaded Image using API Automation Script"
                      }

put_payload =  {
                  'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/7/7a/Pollock_to_Hussey.jpg/250px-Pollock_to_Hussey.jpg",
                  'is_thumbnail': False,
                  'sort_order': 1,
                  'description': "Update API Automation Script"
                }
