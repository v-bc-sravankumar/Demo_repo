from lib.api_lib import *
from fixtures.product_rule import *

# JSON Payload

def test_post_product_for_rules(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload)
    newdata = json.loads(result.text)
    state['product_id'] = newdata['id']

def test_post_product_rules(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/rules')
    result = basic_auth_post(api, username, auth_token, post_rule_payload)
    newdata = json.loads(result.text)
    state['rule_id'] = newdata['id']
    assert newdata['product_id'] == state['product_id']
    assert newdata['sort_order'] == 0
    assert newdata['is_enabled'] == True
    assert newdata['is_stop'] == False
    assert newdata['is_purchasing_disabled'] == False
    conditions_info = newdata['conditions']
    assert conditions_info[0]['product_option_id'] == 41
    assert conditions_info[0]['option_value_id'] == 69

def test_get_product_rule_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/rules/' + str(state['rule_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['rule_id']
    assert newdata['product_id'] == state['product_id']
    assert newdata['sort_order'] == 0
    assert newdata['is_enabled'] == True
    assert newdata['is_stop'] == False
    assert newdata['is_purchasing_disabled'] == False
    conditions_info = newdata['conditions']
    assert conditions_info[0]['product_option_id'] == 41
    assert conditions_info[0]['option_value_id'] == 69

def test_put_product_rules(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/rules/' + str(state['rule_id']))
    result = basic_auth_put(api, username, auth_token, put_rule_payload)
    newdata = json.loads(result.text)
    assert newdata['product_id'] == state['product_id']
    assert newdata['sort_order'] == 1
    assert newdata['is_enabled'] == False
    assert newdata['is_stop'] == True
    assert newdata['is_purchasing_disabled'] == False
    conditions_info = newdata['conditions']
    assert conditions_info[0]['product_option_id'] == 41
    assert conditions_info[0]['option_value_id'] == 69

def test_count_product_rule(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/rules/count')
    r = basic_auth_get(api, username, auth_token)
    newdata = json.loads(r.text)
    newdata['count'] == 1

#Jira issue raised- https://jira.bigcommerce.com/browse/ISC-5501 (BIG-8062)
@pytest.mark.skipif("True")
def test_delete_product_rule(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/rules/'  + str(state['rule_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

def test_required_product_rules_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/rules')
    result = basic_auth_post(api, username, auth_token, invalid_sortorder_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'sort_order' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_is_enabled_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'is_enabled' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_is_stop_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'is_stop' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_conditions_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'conditions' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_product_option_id_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'product_option_id' is invalid."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, invalid_option_value_id_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'option_value_id' is invalid."
    assert newdata[0]['status'] == 400

def test_delete_product(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML Payload

def test_post_product_for_rules_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['product_id_xml'] = newdata.find('id').text

def test_post_product_rules_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/rules')
    result = basic_auth_post(api, username, auth_token, post_rule_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['rule_id_xml'] = newdata.find('id').text
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('sort_order').text == "0"
    assert newdata.find('is_enabled').text == "true"
    assert newdata.find('is_stop').text == "false"
    assert newdata.find('is_purchasing_disabled').text == "false"
    assert newdata.find('conditions/condition/product_option_id').text == "41"
    assert newdata.find('conditions/condition/option_value_id').text == "69"

def test_get_product_rule_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/rules/' + str(state['rule_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['rule_id_xml']
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('sort_order').text == "0"
    assert newdata.find('is_enabled').text == "true"
    assert newdata.find('is_stop').text == "false"
    assert newdata.find('is_purchasing_disabled').text == "false"
    assert newdata.find('conditions/condition/product_option_id').text == "41"
    assert newdata.find('conditions/condition/option_value_id').text == "69"

def test_put_product_rules_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/rules/' + str(state['rule_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_rule_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('sort_order').text == "1"
    assert newdata.find('is_enabled').text == "false"
    assert newdata.find('is_stop').text == "true"
    assert newdata.find('is_purchasing_disabled').text == "false"
    assert newdata.find('conditions/condition/product_option_id').text == "41"
    assert newdata.find('conditions/condition/option_value_id').text == "69"

def test_count_product_rule_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/rules/count')
    r = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(r.text)
    newdata.find('count').text == "1"

def test_delete_product_rule_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/rules/'  + str(state['rule_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')

def test_required_product_rules_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/rules')
    result = basic_auth_post(api, username, auth_token, invalid_sortorder_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'sort_order' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_is_enabled_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'is_enabled' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_is_stop_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'is_stop' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_conditions_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'conditions' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_product_option_id_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'product_option_id' is invalid."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, invalid_option_value_id_payload, 1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'option_value_id' is invalid."
    assert newdata[0].find('status').text == "400"

def test_delete_product_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
