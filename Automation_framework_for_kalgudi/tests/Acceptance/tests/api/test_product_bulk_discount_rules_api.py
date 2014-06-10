from fixtures.discount_rules import *

# JSON Payload


def test_post_product_for_discount_rules(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_product_payload)
    newdata = json.loads(result.text)
    state['product_id'] = newdata['id']


def test_post_discount_rules(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/discountrules')
    result = basic_auth_post(api, username, auth_token, post_discount_payload)
    newdata = json.loads(result.text)
    state['discount_rule_id'] = newdata['id']
    assert newdata['min'] == 100
    assert newdata['max'] == 500
    assert newdata['type'] == "price"
    assert newdata['type_value'] == 2


def test_required_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/discountrules')
    result = basic_auth_post(api, username, auth_token, without_type_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'type' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_type_value_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'type_value' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_min_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'min' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_max_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'max' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_type_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'type' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_type_value_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'type_value' is invalid."
    assert newdata[0]['status'] == 400


def test_get_discount_rules(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/discountrules')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata[0]['product_id'] == state['product_id']
    assert newdata[0]['id'] == state['discount_rule_id']
    assert newdata[0]['min'] == 100
    assert newdata[0]['max'] == 500
    assert newdata[0]['type'] == "price"
    assert newdata[0]['type_value'] == 2


def test_get_discount_rules_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/discountrules/' + str(state['discount_rule_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['product_id'] == state['product_id']
    assert newdata['id'] == state['discount_rule_id']
    assert newdata['min'] == 100
    assert newdata['max'] == 500
    assert newdata['type'] == "price"
    assert newdata['type_value'] == 2


def test_get_discount_rules_count(auth_token, url, username, state):
    api =urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/discountrules/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


def test_put_discount_rules(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/discountrules/' + str(state['discount_rule_id']))
    result = basic_auth_put(api, username, auth_token, put_discount_payload)
    newdata = json.loads(result.text)
    assert newdata['product_id'] == state['product_id']
    assert newdata['id'] == state['discount_rule_id']
    assert newdata['min'] == 200
    assert newdata['max'] == 300
    assert newdata['type'] == "fixed"
    assert newdata['type_value'] == 10


def test_delete_discount_rule_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/discountrules/' + str(state['discount_rule_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)


def test_delete_discount_rule(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML Payload


def test_post_product_for_discount_rules_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_product_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['product_id_xml'] = newdata.find('id').text


def test_post_discount_rules_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/discountrules')
    result = basic_auth_post(api, username, auth_token, post_discount_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['discount_rule_id_xml'] = newdata.find('id').text
    assert newdata.find('min').text == "100"
    assert newdata.find('max').text == "500"
    assert newdata.find('type').text == "price"
    assert newdata.find('type_value').text == "2"


def test_required_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/discountrules')
    result = basic_auth_post(api, username, auth_token, without_type_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'type' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_type_value_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'type_value' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_min_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'min' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_max_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'max' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_type_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'type' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_type_value_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'type_value' is invalid."
    assert newdata[0].find('status').text == "400"


def test_get_discount_rules_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/discountrules')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('product_id').text == state['product_id_xml']
    assert newdata[0].find('id').text == state['discount_rule_id_xml']
    assert newdata[0].find('min').text == "100"
    assert newdata[0].find('max').text == "500"
    assert newdata[0].find('type').text == "price"
    assert newdata[0].find('type_value').text == "2"


def test_get_discount_rules_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/discountrules/' + str(state['discount_rule_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('id').text == state['discount_rule_id_xml']
    assert newdata.find('min').text == "100"
    assert newdata.find('max').text == "500"
    assert newdata.find('type').text == "price"
    assert newdata.find('type_value').text == "2"


def test_get_discount_rules_count_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/discountrules/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0


def test_put_discount_rules_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/discountrules/' + str(state['discount_rule_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_discount_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('id').text == state['discount_rule_id_xml']
    assert newdata.find('min').text == "200"
    assert newdata.find('max').text == "300"
    assert newdata.find('type').text == "fixed"
    assert newdata.find('type_value').text == "10"


def test_delete_discount_rule_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/discountrules/' + str(state['discount_rule_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')


def test_delete_discount_rule_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
