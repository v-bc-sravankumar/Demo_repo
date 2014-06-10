from lib.api_lib import *

OPTION_NAME = generate_random_string()
OPTION_DISPLAY_NAME = generate_random_string()
TYPE = "C"
LABEL_TEXT = "white"
LABEL_VALUE = "#FFFFFF"
UPDATE_OPTION_NAME = generate_random_string()
UPDATE_OPTION_DISPLAY_NAME = generate_random_string()
UPDATE_TYPE = "CS"
UPDATE_LABEL_TEXT = "black"
UPDATE_LABEL_VALUE = "#261326"

# Option Set
OPTION_SET_NAME = generate_random_string()
UPDATE_OPTION_SET_NAME = generate_random_string()
OPTION_NAME = generate_random_string()
OPTION_DISPLAY_NAME = generate_random_string()
TYPE = "CS"


post_option_payload =  {
    "name": OPTION_NAME,
    "display_name": OPTION_DISPLAY_NAME,
    "type": TYPE
}

put_option_payload =  {
    "name": UPDATE_OPTION_NAME,
    "display_name": UPDATE_OPTION_DISPLAY_NAME,
    "type": UPDATE_TYPE
}


# Below payload works only for the Swatch option (CS)
post_option_value_payload =  {
    "label": LABEL_TEXT,
    "value": LABEL_VALUE
}

# Below payload works only for the Swatch option (CS)
put_option_value_payload =  {
    "label": UPDATE_LABEL_TEXT,
    "value": UPDATE_LABEL_VALUE
}

without_name_payload =  {
    "display_name": OPTION_DISPLAY_NAME,
    "type": TYPE
}

invalid_type_payload =  {
    "name": OPTION_NAME,
    "display_name": OPTION_DISPLAY_NAME,
    "type": "123"
}

without_type_payload =  {
    "name": OPTION_NAME,
    "display_name": OPTION_DISPLAY_NAME,
}

without_label_payload =  {
    "value": LABEL_VALUE
}

# Option Set payload
post_option_set_payload =  {
    "name": OPTION_SET_NAME
}

put_option_set_payload =  {
    "name": UPDATE_OPTION_SET_NAME
}
post_options_for_optionset_payload =  {
    "display_name": "automation appearance",
    "sort_order": 0,
    "is_required": True
}

put_options_for_optionset_payload =  {
    "display_name": "Updated automation appearance",
    "sort_order": 1,
    "is_required": False
}
post_options_for_delete_all_scenario_payload =  {
    "display_name": "delete all options scenario",
    "sort_order": 0,
    "is_required": True
}

invalid_name_option_set_payload =  {
    "name": ""
}

invalid_sort_order_payload = {
    "display_name": "automation appearance",
    "sort_order": "abc",
    "is_required": True
}

invalid_is_required_payload =  {
    "display_name": "automation appearance",
    "sort_order": 0,
    "is_required": 123
}
