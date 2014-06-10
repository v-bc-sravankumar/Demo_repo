from lib.api_lib import *
from fixtures.product_options import *

# JSON Payload

def test_post_product_for_product_options(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_product_payload)
    newdata = json.loads(result.text)
    state['product_id'] = newdata['id']

def test_get_product_options(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/options')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    state['option_id'] = newdata[0]['id']
    assert newdata[0]['option_id'] == 18
    assert newdata[0]['display_name'] == "Size"
    assert newdata[0]['sort_order'] == 0
    assert newdata[0]['is_required'] == True

def test_get_product_options_by_ID(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/options/' + str(state['option_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['option_id'] == 18
    assert newdata['display_name'] == "Size"
    assert newdata['sort_order'] == 0
    assert newdata['is_required'] == True

# def test_get_product_options_count(auth_token, url, username, state):
#     api =urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/options/count')
#     result = basic_auth_get(api, username, auth_token)
#     newdata = json.loads(result.text)
#     assert newdata['count'] > 0

def test_delete_product_option(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML Payload

def test_post_product_for_product_options_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_product_payload, payload_format='xml')
    newdata = etree.fromstring(result.text)
    state['product_id_xml'] = newdata.find('id').text

def test_get_product_options_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/options')
    result = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(result.text)
    state['option_id_xml'] = newdata[0].find('id').text
    assert newdata[0].find('option_id').text == "18"
    assert newdata[0].find('display_name').text == "Size"
    assert newdata[0].find('sort_order').text == "0"
    assert newdata[0].find('is_required').text == "true"

def test_get_product_options_by_ID_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/options/' + str(state['option_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('option_id').text == "18"
    assert newdata.find('display_name').text == "Size"
    assert newdata.find('sort_order').text == "0"
    assert newdata.find('is_required').text == "true"

# def test_get_product_options_count_xml_payload(auth_token, url, username, state):
#     api =urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/options/count')
#     result = basic_auth_get(api, username, auth_token, payload_format='xml')
#     newdata = etree.fromstring(result.text)
#     assert newdata.find('count').text > 0

def test_delete_product_option_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format='xml')
    basic_auth_get(api, username, auth_token, 1, payload_format='xml')
