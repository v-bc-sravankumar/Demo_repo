from fixtures.customer import *

# JSON Payload


def test_post_customer_group(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customer_groups')
    result = basic_auth_post(api, username, auth_token, post_customer_group_payload)
    newdata = json.loads(result.text)
    state['customer_group_id'] = newdata['id']
    assert newdata['name'] == GROUP_NAME
    assert newdata['category_access']['type'] == "all"
    assert newdata['discount_rules'][0]['type'] == "all"
    assert newdata['discount_rules'][0]['method'] == "percent"
    assert newdata['discount_rules'][0]['amount'] == 2.5
    assert newdata['discount_rules'][1]['type'] == "product"
    assert newdata['discount_rules'][1]['product_id'] == "33"
    assert newdata['discount_rules'][1]['method'] == "percent"
    assert newdata['discount_rules'][1]['amount'] == "5.0000"
    assert newdata['discount_rules'][2]['type'] == "category"
    assert newdata['discount_rules'][2]['category_id'] == "7"
    assert newdata['discount_rules'][2]['method'] == "price"
    assert newdata['discount_rules'][2]['amount'] == "12.0000"


def test_get_customer_group_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customer_groups/' + str(state['customer_group_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['name'] == GROUP_NAME
    assert newdata['category_access']['type'] == "all"
    assert newdata['discount_rules'][0]['type'] == "all"


def test_put_customer_group(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customer_groups/' + str(state['customer_group_id']))
    result = basic_auth_put(api, username, auth_token, put_customer_group_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['customer_group_id']
    assert newdata['name'] == UPDATE_GROUP_NAME
    assert newdata['is_default'] == False
    assert newdata['category_access']['type'] == "none"
    assert newdata['discount_rules'][0]['type'] == "all"
    assert newdata['discount_rules'][0]['method'] == "fixed"
    assert newdata['discount_rules'][0]['amount'] == 10
    assert newdata['discount_rules'][1]['type'] == "product"
    assert newdata['discount_rules'][1]['product_id'] == "33"
    assert newdata['discount_rules'][1]['method'] == "price"
    assert newdata['discount_rules'][1]['amount'] == "5.0000"
    assert newdata['discount_rules'][2]['type'] == "category"
    assert newdata['discount_rules'][2]['category_id'] == "7"
    assert newdata['discount_rules'][2]['method'] == "percent"
    assert newdata['discount_rules'][2]['amount'] == "2.5000"


def test_count_customer_group(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customer_groups/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


def test_delete_customer_group(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/customer_groups/' + str(state['customer_group_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# Validations for  Group


def test_required_customer_group_fields(auth_token, url, username):
    api=urlparse.urljoin(url, 'api/v2/customer_groups')
    result = basic_auth_post(api, username, auth_token,without_companyname_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_customer_isdefault_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'is_default' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_categeoryaccess_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'type' is invalid."
    assert newdata[0]['status'] == 400

# Validations for Discount


def test_required_customer_group_discount_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customer_groups')
    result = basic_auth_post(api, username, auth_token,invalid_discount_type_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'type' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_discount_method_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'method' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_discount_amount_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'amount' is invalid."
    assert newdata[0]['status'] == 400

# Validations for Product


def test_required_customer_group_product_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customer_groups')
    result = basic_auth_post(api, username, auth_token,invalid_discount_product_type_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'type' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_discount_product_id_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'product_id' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_discount_product_method_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'method' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_discount_product_amount_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'amount' is invalid."
    assert newdata[0]['status'] == 400

# Validations for category


def test_required_customer_group_category_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/customer_groups')
    result = basic_auth_post(api, username, auth_token,invalid_discount_category_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'type' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_discount_category_id_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'category_id' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_discount_category_method_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'method' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token,invalid_discount_category_amount_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message']=="The field 'amount' is invalid."
    assert newdata[0]['status'] == 400

# Xml Payload
# There is a bug when creating order using xml payload...
# def test_post_customer_group_xml_payload(auth_token, url, username):
#     api = urlparse.urljoin(url, 'api/v2/customer_groups')
#     result = basic_auth_post(api, username, auth_token, post_customer_group_payload, payload_format = 'xml')
#     print result.text
#     newdata = etree.fromstring(result.text)
#     global CUSTOMER_GROUP_ID_xml
#     CUSTOMER_GROUP_ID_xml = newdata.find('id').text
#     assert newdata.find('id').text == CUSTOMER_GROUP_ID_xml
#     assert newdata.find('name').text == GROUP_NAME
#     print result.text
#     # assert newdata.find('category_access/type').text == "all"
#     assert newdata.find('discount_rules/type').text == "all"

    # assert newdata['category_access']['type'] == "all"
    # assert newdata.find('').text ==
    # assert newdata.find('').text ==
    # assert newdata.find('').text ==
    # assert newdata.find('').text ==
    # assert newdata.find('').text ==
    # assert newdata.find('').text ==
    # assert newdata.find('').text ==
    # assert newdata.find('').text ==

    # assert newdata['name'] == GROUP_NAME
    # assert newdata['category_access']['type'] == "all"
    # assert newdata['discount_rules'][0]['type'] == "all"
    # assert newdata['discount_rules'][0]['method'] == "percent"
    # assert newdata['discount_rules'][0]['amount'] == 2.5
    # assert newdata['discount_rules'][1]['type'] == "product"
    # assert newdata['discount_rules'][1]['product_id'] == "33"
    # assert newdata['discount_rules'][1]['method'] == "percent"
    # assert newdata['discount_rules'][1]['amount'] == "5.0000"
    # assert newdata['discount_rules'][2]['type'] == "category"
    # assert newdata['discount_rules'][2]['category_id'] == "7"
    # assert newdata['discount_rules'][2]['method'] == "price"
    # assert newdata['discount_rules'][2]['amount'] == "12.0000"


# def test_get_customer_group_by_id_xml_payload(auth_token, url, username):
#     api = urlparse.urljoin(url, 'api/v2/customer_groups/' + str(CUSTOMER_GROUP_ID_xml))
#     result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
#     newdata = json.loads(result.text)
#     assert newdata['name'] == GROUP_NAME
#     assert newdata['category_access']['type'] == "all"
#     assert newdata['discount_rules'][0]['type'] == "all"

# def test_put_customer_group_xml_payload(auth_token, url, username):
#     api = urlparse.urljoin(url, 'api/v2/customer_groups/' + str(CUSTOMER_GROUP_ID_xml))
#     result = basic_auth_put(api, username, auth_token, put_customer_group_payload, payload_format = 'xml')
#     newdata = json.loads(result.text)
#     assert newdata['id'] == CUSTOMER_GROUP_ID_xml
#     assert newdata['name'] == UPDATE_GROUP_NAME
#     assert newdata['is_default'] == False
#     assert newdata['category_access']['type'] == "none"
#     assert newdata['discount_rules'][0]['type'] == "all"
#     assert newdata['discount_rules'][0]['method'] == "fixed"
#     assert newdata['discount_rules'][0]['amount'] == 10
#     assert newdata['discount_rules'][1]['type'] == "product"
#     assert newdata['discount_rules'][1]['product_id'] == "33"
#     assert newdata['discount_rules'][1]['method'] == "price"
#     assert newdata['discount_rules'][1]['amount'] == "5.0000"
#     assert newdata['discount_rules'][2]['type'] == "category"
#     assert newdata['discount_rules'][2]['category_id'] == "7"
#     assert newdata['discount_rules'][2]['method'] == "percent"
#     assert newdata['discount_rules'][2]['amount'] == "2.5000"

# def test_count_customer_group_xml_payload(auth_token, url, username):
#     api = urlparse.urljoin(url, 'api/v2/customer_groups/count')
#     result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
#     newdata = json.loads(result.text)
#     assert newdata['count'] > 0

# def test_delete_customer_group_xml_payload(auth_token, url, username):
#     api = urlparse.urljoin(url, 'api/v2/customer_groups/' + str(CUSTOMER_GROUP_ID_xml))
#     basic_auth_delete(api, username, auth_token, payload_format = 'xml')
#     basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
