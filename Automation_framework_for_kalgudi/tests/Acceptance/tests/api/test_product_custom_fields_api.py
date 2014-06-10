from lib.api_lib import *
from fixtures.custom_fields import *

# JSON Payload

def test_post_product_for_custom_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_product_payload)
    newdata = json.loads(result.text)
    state['product_id'] = newdata['id']

def test_post_custom_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/customfields')
    result = basic_auth_post(api, username, auth_token, post_custom_payload)
    newdata = json.loads(result.text)
    state['custom_field_id'] = newdata['id']
    assert newdata['product_id'] == state['product_id']
    assert newdata['name'] == "Release Date"
    assert newdata['text'] == "2013-12-25"

def test_required_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/customfields')
    result = basic_auth_post(api, username, auth_token, without_name_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'name' was not supplied."
    assert newdata[0]['status'] == 400
    result = basic_auth_post(api, username, auth_token, without_text_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'text' was not supplied."
    assert newdata[0]['status'] == 400

def test_get_custom_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/customfields')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata[0]['product_id'] == state['product_id']
    assert newdata[0]['id'] == state['custom_field_id']
    assert newdata[0]['name'] == "Release Date"
    assert newdata[0]['text'] == "2013-12-25"

def test_get_custom_fields_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/customfields/' + str(state['custom_field_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['product_id'] == state['product_id']
    assert newdata['id'] == state['custom_field_id']
    assert newdata['name'] == "Release Date"
    assert newdata['text'] == "2013-12-25"

def test_get_custom_fields_count(auth_token, url, username, state):
    api =urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/customfields/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0

def test_put_custom_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/customfields/' + str(state['custom_field_id']))
    result = basic_auth_put(api, username, auth_token, put_custom_payload)
    newdata = json.loads(result.text)
    assert newdata['product_id'] == state['product_id']
    assert newdata['id'] == state['custom_field_id']
    assert newdata['name'] == "Release Date"
    assert newdata['text'] == "2013-12-31"

def test_delete_custom_fields_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/customfields/' + str(state['custom_field_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

def test_delete_custom_fields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML Payload

def test_post_product_for_custom_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_product_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['product_id_xml'] = newdata.find('id').text

def test_post_custom_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/customfields')
    result = basic_auth_post(api, username, auth_token, post_custom_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['custom_field_id_xml'] = newdata.find('id').text
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('name').text == "Release Date"
    assert newdata.find('text').text == "2013-12-25"

def test_required_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/customfields')
    result = basic_auth_post(api, username, auth_token, without_name_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'name' was not supplied."
    assert newdata[0].find('status').text == "400"
    result = basic_auth_post(api, username, auth_token, without_text_payload,1, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'text' was not supplied."
    assert newdata[0].find('status').text == "400"

def test_get_custom_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/customfields')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('product_id').text == state['product_id_xml']
    assert newdata[0].find('id').text == state['custom_field_id_xml']
    assert newdata[0].find('name').text == "Release Date"
    assert newdata[0].find('text').text == "2013-12-25"

def test_get_custom_fields_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/customfields/' + str(state['custom_field_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('id').text == state['custom_field_id_xml']
    assert newdata.find('name').text == "Release Date"
    assert newdata.find('text').text == "2013-12-25"

def test_get_custom_fields_count_xml_payload(auth_token, url, username, state):
    api =urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/customfields/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0

def test_put_custom_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/customfields/' + str(state['custom_field_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_custom_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('id').text == state['custom_field_id_xml']
    assert newdata.find('name').text == "Release Date"
    assert newdata.find('text').text == "2013-12-31"

def test_delete_custom_fields_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/customfields/' + str(state['custom_field_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')

def test_delete_custom_fields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
