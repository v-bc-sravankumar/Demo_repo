from lib.api_lib import *

PRODUCT_NAME = generate_random_string()

post_payload = {
    'name': PRODUCT_NAME,
    'type': "physical",
    'price': "100.10",
    'weight': "1.11",
    'categories': [15],
    'availability': "available"
}

post_rule_payload = {
    'sort_order': 0,
    'is_enabled': True,
    'is_stop': False,
    'price_adjuster': "null",
    'weight_adjuster': "null",
    'is_purchasing_disabled': False,
    'purchasing_disabled_message': "",
    'is_purchasing_hidden': False,
    'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/800px-England_vs_South_Africa.jpg",
    'conditions':[
    {
        'product_option_id': 41,
        'option_value_id': 69
    }
    ]
}

put_rule_payload = {
    'sort_order': 1,
    'is_enabled': False,
    'is_stop': True,
    'price_adjuster': "null",
    'weight_adjuster': "null",
    'is_purchasing_disabled': False,
    'purchasing_disabled_message': "",
    'is_purchasing_hidden': False,
    'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/7/7a/Pollock_to_Hussey.jpg/250px-Pollock_to_Hussey.jpg",
    'conditions':[
    {
        'product_option_id': 41,
        'option_value_id': 69
    }
    ]
}

#invalid payloads
invalid_sortorder_payload = {
    'sort_order': "abc",
    'is_enabled': True,
    'is_stop': False,
    'price_adjuster': "null",
    'weight_adjuster': "null",
    'is_purchasing_disabled': False,
    'purchasing_disabled_message': "",
    'is_purchasing_hidden': False,
    'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/800px-England_vs_South_Africa.jpg",
    'conditions':[
    {
        'product_option_id': 41,
        'option_value_id': 69
    }
    ]
}

invalid_is_enabled_payload = {
    'sort_order': 0,
    'is_enabled': "abc123",
    'is_stop': False,
    'price_adjuster': "null",
    'weight_adjuster': "null",
    'is_purchasing_disabled': False,
    'purchasing_disabled_message': "",
    'is_purchasing_hidden': False,
    'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/800px-England_vs_South_Africa.jpg",
    'conditions':[
    {
        'product_option_id': 41,
        'option_value_id': 69
    }
    ]
}

invalid_is_stop_payload = {
    'sort_order': 0,
    'is_enabled': True,
    'is_stop': "abc123",
    'price_adjuster': "null",
    'weight_adjuster': "null",
    'is_purchasing_disabled': False,
    'purchasing_disabled_message': "",
    'is_purchasing_hidden': False,
    'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/800px-England_vs_South_Africa.jpg",
    'conditions':[
    {
        'product_option_id': 41,
        'option_value_id': 69
    }
    ]
}

without_conditions_payload = {
    'sort_order': 0,
    'is_enabled': True,
    'is_stop': "abc123",
    'price_adjuster': "null",
    'weight_adjuster': "null",
    'is_purchasing_disabled': False,
    'purchasing_disabled_message': "",
    'is_purchasing_hidden': False,
    'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/800px-England_vs_South_Africa.jpg"
}

invalid_product_option_id_payload = {
    'sort_order': 0,
    'is_enabled': True,
    'is_stop': False,
    'price_adjuster': "null",
    'weight_adjuster': "null",
    'is_purchasing_disabled': False,
    'purchasing_disabled_message': "",
    'is_purchasing_hidden': False,
    'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/800px-England_vs_South_Africa.jpg",
    'conditions':[
    {
        'product_option_id': "abc",
        'option_value_id': 69
    }
    ]
}

invalid_option_value_id_payload = {
    'sort_order': 0,
    'is_enabled': True,
    'is_stop': False,
    'price_adjuster': "null",
    'weight_adjuster': "null",
    'is_purchasing_disabled': False,
    'purchasing_disabled_message': "",
    'is_purchasing_hidden': False,
    'image_file': "http://upload.wikimedia.org/wikipedia/commons/thumb/1/13/England_vs_South_Africa.jpg/800px-England_vs_South_Africa.jpg",
    'conditions':[
    {
        'product_option_id': 41,
        'option_value_id': "abc"
    }
    ]
}
