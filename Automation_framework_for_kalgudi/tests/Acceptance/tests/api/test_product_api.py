from fixtures.product import *

# JSON Payload

def test_post_product(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload)
    newdata = json.loads(result.text)
    state['product_id'] = newdata['id']
    assert newdata['name'] == PRODUCT_NAME
    assert newdata['type'] == "physical"
    assert newdata['price'] == "100.1000"
    assert newdata['weight'] == "1.1100"
    assert newdata['categories'] == [15]
    assert newdata['availability'] == "available"

def test_get_products(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_get(api, username, auth_token)

def test_get_product_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['name'] == PRODUCT_NAME
    assert newdata['type'] == "physical"
    assert newdata['price'] == "100.1000"
    assert newdata['weight'] == "1.1100"
    assert newdata['categories'] == [15]
    assert newdata['availability'] == "available"

def test_put_product(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    result = basic_auth_put(api, username, auth_token, put_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['product_id']
    assert newdata['name'] == UPDATE_PRODUCT_NAME
    assert newdata['price'] == "111.1000"
    assert newdata['weight'] == "5.5500"
    assert newdata['categories'] == [14]
    assert newdata['availability'] == "disabled"

def test_count_product(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0

def test_delete_product(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML payload

def test_post_product_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['product_id_xml'] = newdata.find('id').text
    assert newdata.find('name').text == PRODUCT_NAME
    assert newdata.find('type').text == "physical"
    assert newdata.find('price').text == "100.1000"
    assert newdata.find('weight').text == "1.1100"
    assert newdata.find('categories/value').text == "15"
    assert newdata.find('availability').text == "available"

def test_get_products_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    basic_auth_get(api, username, auth_token, payload_format = 'xml')

def test_get_product_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('name').text == PRODUCT_NAME
    assert newdata.find('type').text == "physical"
    assert newdata.find('price').text == "100.1000"
    assert newdata.find('weight').text == "1.1100"
    assert newdata.find('categories/value').text == "15"
    assert newdata.find('availability').text == "available"

def test_put_product_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['product_id_xml']
    assert newdata.find('name').text == UPDATE_PRODUCT_NAME
    assert newdata.find('price').text == "111.1000"
    assert newdata.find('weight').text == "5.5500"
    assert newdata.find('categories/value').text == "14"
    assert newdata.find('availability').text == "disabled"

def test_count_product_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0

def test_delete_product_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
