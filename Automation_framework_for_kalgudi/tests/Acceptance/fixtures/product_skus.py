from lib.api_lib import *

PRODUCT_NAME = generate_random_string()
UPDATE_PRODUCT_NAME = generate_random_string()
SKU = generate_random_string()
UPDATE_SKU = generate_random_string()

post_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "availability": "available",
    "option_set_id": 14
}

post_skus_payload =  {
    "sku": SKU,
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": 150,
    "inventory_warning_level": 10,
    "options":[
    {
        "product_option_id":41,
        "option_value_id":69
    }
    ]
}

put_skus_payload =  {
    "sku": UPDATE_SKU,
    "upc": "UPDATE_AutoTestUPC",
    "bin_picking_number": "UPDATE_Bin_Pick_Number_001",
    "cost_price": "10.10",
    "inventory_level": 15,
    "inventory_warning_level": 1,
    "options":[
    {
        "product_option_id":41,
        "option_value_id":69
    }
    ]
}

# Invalid Payloads for Product
without_name_payload = {
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "availability": "available",
    "option_set_id": 14
}

without_type_payload = {
    "name": PRODUCT_NAME,
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "availability": "available",
    "option_set_id": 14
}

without_price_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "weight": "1.11",
    "categories": [15],
    "availability": "available",
    "option_set_id": 14
}

without_weight_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "categories": [15],
    "availability": "available",
    "option_set_id": 14
}

without_categories_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "availability": "available",
    "option_set_id": 14
}

without_availability_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "option_set_id": 14
}

invalid_type_payload = {
    "name": PRODUCT_NAME,
    "type": 123,
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "availability": "available",
    "option_set_id": 14
}

invalid_categories_string_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": ["abc"],
    "availability": "available",
    "option_set_id": 14
}

invalid_categories_empty_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": "[]",
    "availability": "available",
    "option_set_id": 14
}

invalid_availability_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "availability": 1234,
    "option_set_id": 14
}

invalid_option_set_id_payload = {
    "name": PRODUCT_NAME,
    "type": "physical",
    "price": "100.10",
    "weight": "1.11",
    "categories": [15],
    "availability": "available",
    "option_set_id": "abcd"

}

# Invalid Payloads for skus
without_sku_payload = {
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": 150,
    "inventory_warning_level": 10,
    "options": [
    {
        "product_option_id": 41,
        "option_value_id": 69
    }
    ]
}

without_options_payload = {
    "sku": SKU,
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": 150,
    "inventory_warning_level": 10
}

without_product_option_id_payload = {
    "sku": SKU,
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": 150,
    "inventory_warning_level": 10,
    "options": [
    {
        "option_value_id": 69
    }
    ]
}

without_option_value_id_payload = {
    "sku": SKU,
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": 150,
    "inventory_warning_level": 10,
    "options": [
    {
        "product_option_id": 41
    }
    ]
}

invalid_inventory_level_payload = {
    "sku": SKU,
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": "abcd",
    "inventory_warning_level": 10,
    "options": [
    {
        "product_option_id": 41,
        "option_value_id": 69
    }
    ]
}

invalid_inventory_warning_level_payload = {
    "sku": SKU,
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": 150,
    "inventory_warning_level": "abcd",
    "options": [
    {
        "product_option_id": 41,
        "option_value_id": 69
    }
    ]
}

invalid_product_option_id_payload =     {
    "sku": SKU,
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": 150,
    "inventory_warning_level": 10,
    "options": [
    {
        "product_option_id": "xyz",
        "option_value_id": 69
    }
    ]
}

invalid_option_value_id_payload = {
    "sku": SKU,
    "upc": "AutoTestUPC",
    "bin_picking_number": "Bin_Pick_Number_001",
    "cost_price": "100.10",
    "inventory_level": 150,
    "inventory_warning_level": 10,
    "options": [
    {
        "product_option_id": 41,
        "option_value_id": "abcd"
    }
    ]
}
