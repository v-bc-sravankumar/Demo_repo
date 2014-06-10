from lib.api_lib import *
from fixtures.product_image import *


# JSON Payload

def test_post_product_for_image(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload)
    newdata = json.loads(result.text)
    state['product_id'] = newdata['id']

def test_post_product_image(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/images/')
    result = basic_auth_post(api, username, auth_token, post_image_payload)
    newdata = json.loads(result.text)
    state['image_id'] = newdata['id']
    assert newdata['product_id'] == state['product_id']
    assert newdata['is_thumbnail'] == False
    assert newdata['sort_order'] == 0
    assert newdata['description'] == "Uploaded Image using API Automation Script"
    assert "England_vs_South_Africa" in newdata['image_file']

def test_get_image_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/images/' + str(state['image_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['product_id'] == state['product_id']
    assert newdata['is_thumbnail'] == False
    assert newdata['sort_order'] == 0
    assert newdata['description'] == "Uploaded Image using API Automation Script"
    assert "England_vs_South_Africa" in newdata['image_file']

def test_get_all_product_images(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/images/')
    basic_auth_get(api, username, auth_token)

def test_put_product_image(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/images/' + str(state['image_id']))
    result = basic_auth_put(api, username, auth_token, put_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['image_id']
    assert newdata['product_id'] == state['product_id']
    assert newdata['is_thumbnail'] == False
    assert newdata['sort_order'] == 1
    assert newdata['description'] == "Update API Automation Script"
    assert ".jpg" in newdata['image_file']

def test_count_product_images(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/images/count' )
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    newdata['count'] == 1

def test_delete_product_image(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/images/'  + str(state['image_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

def test_delete_product(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

# XML Payload

def test_post_product_for_image_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['product_id_xml'] = newdata.find('id').text

def test_post_product_image_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/images/')
    result = basic_auth_post(api, username, auth_token, post_image_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['image_id_xml'] = newdata.find('id').text
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('is_thumbnail').text == "false"
    assert newdata.find('sort_order').text == "0"
    assert newdata.find('description').text == "Uploaded Image using API Automation Script"
    assert "England_vs_South_Africa" in newdata.find('image_file').text

def test_get_image_by_id_xml_payload_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/images/' + str(state['image_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('is_thumbnail').text == "false"
    assert newdata.find('sort_order').text == "0"
    assert newdata.find('description').text == "Uploaded Image using API Automation Script"
    assert "England_vs_South_Africa" in newdata.find('image_file').text

def test_get_all_product_images_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/images/')
    basic_auth_get(api, username, auth_token, payload_format = 'xml')

def test_put_product_image_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/images/' + str(state['image_id_xml']))
    result = basic_auth_put(api, username, auth_token, put_payload, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['image_id_xml']
    assert newdata.find('product_id').text == state['product_id_xml']
    assert newdata.find('is_thumbnail').text == "false"
    assert newdata.find('sort_order').text == "1"
    assert newdata.find('description').text == "Update API Automation Script"
    assert ".jpg" in newdata.find('image_file').text

def test_count_product_images_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/images/count' )
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    newdata.find('count').text == "1"

def test_delete_product_image_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']) + '/images/'  + str(state['image_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')

def test_delete_product_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
