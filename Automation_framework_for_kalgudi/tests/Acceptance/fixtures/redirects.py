from lib.api_lib import *

post_payload = {
    "path": "/mens_clothing",
    "forward": {
        "type": "category",
        "ref": 3
    }
}

put_payload =  {
    "path": "/mens_clothing",
    "forward": {
        "type": "page",
        "ref": 2
    }
}

required_path_payload = {
    "forward": {
        "type": "category",
        "ref": 3
    }
}

forward_not_suppied_payload =  {
    "path": "/mens_clothing"
}

invalid_category_reference_payload =  {
    "path": "/mens_clothing",
    "forward": {
        "type": "category"
    }
}

invalid_type_payload =  {
    "path": "/mens_clothing",
    "forward": {
        "ref": 2
    }
}

invalid_manual_reference_payload =  {
    "path": "/mens_clothing",
    "forward": {
        "type": "manual",
        "ref": 1
    }
}

post_redirect_forward_type_manual_payload = {
    "path": "/redirect_path1",
    "forward": {
        "type": "manual",
        "ref": "http://www.bigcommerce.com"
    }
}

post_redirect_forward_type_brand_payload =  {
    "path": "/brand_test",
    "forward": {
        "type": "brand",
        "ref": 29
    }
}

post_redirect_forward_type_product_payload = {
    "path": "/product_test",
    "forward": {
        "type": "product",
        "ref": 34
    }
}

post_redirect_forward_type_page_payload =  {
    "path": "/page_test",
    "forward": {
        "type": "page",
        "ref": 1
    }
}
