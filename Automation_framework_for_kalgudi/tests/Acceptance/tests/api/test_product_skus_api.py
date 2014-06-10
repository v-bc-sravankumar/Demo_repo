from fixtures.product_skus import *

# ****************************************************************************
#                                 JSON Payload
# ****************************************************************************


def test_post_product_for_skus(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload)
    newdata = json.loads(result.text)
    state['product_id'] = newdata['id']

# Validation for Mandatory Fields and Invalid Data for product


def test_required_fields_product(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, without_name_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_type_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'type' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_price_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'price' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_weight_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'weight' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_categories_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'categories' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_availability_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'availability' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_type_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'type' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_categories_string_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'categories' is invalid."
    a = newdata[0]['details']
    assert a['invalid_reason'] == "The value 'abc' in the provided array is not a valid id."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_categories_empty_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'categories' is invalid."
    b = newdata[0]['details']
    assert b['invalid_reason'] == "The provided value is not a valid array."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_availability_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'availability' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_option_set_id_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'option_set_id' is invalid."
    assert newdata[0]['status'] == 400

# Validation for Mandatory Fields and Invalid Data for skus


def test_required_fields_skus(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/skus')
    result = basic_auth_post(api, username, auth_token, without_sku_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'sku' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_options_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'options' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_product_option_id_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'product_option_id' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_option_value_id_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'option_value_id' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_inventory_level_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'inventory_level' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_inventory_warning_level_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'inventory_warning_level' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_product_option_id_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'product_option_id' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_option_value_id_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'option_value_id' is invalid."
    assert newdata[0]['status'] == 400


def test_post_skus(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/skus')
    result = basic_auth_post(api, username, auth_token, post_skus_payload)
    newdata = json.loads(result.text)
    state['sku_id'] = newdata['id']
    assert newdata['sku'] == SKU
    assert newdata['upc'] == "AutoTestUPC"
    assert newdata['bin_picking_number'] == "Bin_Pick_Number_001"
    assert newdata['cost_price'] == "100.1000"
    assert newdata['inventory_level'] == 150
    assert newdata['inventory_warning_level'] == 10
    options_info = newdata['options']
    assert options_info[0]['product_option_id'] == 41
    assert options_info[0]['option_value_id'] == 69


def test_get_skus_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/skus/' + str(state['sku_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['sku_id']
    assert newdata['sku'] == SKU


def test_get_all_skus(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/skus')
    basic_auth_get(api, username, auth_token)


def test_put_skus(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/skus/' + str(state['sku_id']))
    result = basic_auth_put(api, username, auth_token, put_skus_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['sku_id']
    assert newdata['sku'] == UPDATE_SKU
    assert newdata['upc'] == "UPDATE_AutoTestUPC"
    assert newdata['bin_picking_number'] == "UPDATE_Bin_Pick_Number_001"
    assert newdata['cost_price'] == "10.1000"
    assert newdata['inventory_level'] == 15
    assert newdata['inventory_warning_level'] == 1
    options_info = newdata['options']
    assert options_info[0]['product_option_id'] == 41
    assert options_info[0]['option_value_id'] == 69


def test_count_product_skus(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/skus/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    newdata['count'] == 1


def test_delete_product_skus(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/skus/' + str(state['sku_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)


def test_delete_product_created_for_skus(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# ****************************************************************************
#                                 XML Payload
# ****************************************************************************


def test_post_product_for_skus_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['product_id_xml'] = newdata.find('id').text

# Validation for Mandatory Fields and Invalid Data for product


def test_required_fields_product_xml_paylaod(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, without_name_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'name' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_type_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'type' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_price_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'price' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_weight_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'weight' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_categories_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'categories' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_availability_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'availability' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_type_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'type' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_categories_string_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'categories' is invalid."
    assert newdata[0].find('details/invalid_reason').text == "The value 'abc' in the provided array is not a valid id."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_categories_empty_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'categories' is invalid."
    assert newdata[0].find('details/invalid_reason').text == "The provided value is not a valid array."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_availability_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'availability' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_option_set_id_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'option_set_id' is invalid."
    assert newdata[0].find('status').text == "400"

# Validation for Mandatory Fields and Invalid Data for skus


def test_required_fields_skus_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/skus')
    result = basic_auth_post(api, username, auth_token, without_sku_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'sku' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_options_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'options' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_product_option_id_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'product_option_id' was not supplied."
    assert newdata[0].find('status').text == "400"
    # result = basic_auth_post(api, username, auth_token, without_option_value_id_payload,1, payload_format = 'xml')
    # newdata = etree.fromstring(result.text)
    # assert newdata[0].find('message').text == "The required field 'option_value_id' was not supplied."
    # assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_inventory_level_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'inventory_level' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_inventory_warning_level_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'inventory_warning_level' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_product_option_id_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'product_option_id' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_option_value_id_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'option_value_id' is invalid."
    assert newdata[0].find('status').text == "400"


def test_post_skus_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/skus')
    result = basic_auth_post(api, username, auth_token, post_skus_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['sku_id_xml'] = newdata.find('id').text
    assert newdata.find('sku').text == SKU
    assert newdata.find('upc').text == "AutoTestUPC"
    assert newdata.find('bin_picking_number').text == "Bin_Pick_Number_001"
    assert newdata.find('cost_price').text == "100.1000"
    assert newdata.find('inventory_level').text == "150"
    assert newdata.find('inventory_warning_level').text == "10"
    assert newdata.find('options/option/product_option_id').text == "41"
    assert newdata.find('options/option/option_value_id').text == "69"


def test_get_skus_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/skus/' + str(state['sku_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['sku_id_xml']
    assert newdata.find('sku').text == SKU


def test_get_all_skus_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/skus')
    basic_auth_get(api, username, auth_token, payload_format = 'xml')


def test_put_skus_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/skus/' + str(state['sku_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_skus_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['sku_id_xml']
    assert newdata.find('sku').text == UPDATE_SKU
    assert newdata.find('upc').text == "UPDATE_AutoTestUPC"
    assert newdata.find('bin_picking_number').text == "UPDATE_Bin_Pick_Number_001"
    assert newdata.find('cost_price').text == "10.1000"
    assert newdata.find('inventory_level').text == "15"
    assert newdata.find('inventory_warning_level').text == "1"
    assert newdata.find('options/option/product_option_id').text == "41"
    assert newdata.find('options/option/option_value_id').text == "69"


def test_count_product_skus_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/skus/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    newdata.find('count').text == "1"


def test_delete_product_skus_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/skus/' + str(state['sku_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')


def test_delete_product_created_for_skus_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
