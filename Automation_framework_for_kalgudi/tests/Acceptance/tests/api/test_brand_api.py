from fixtures.brand import *


# JSON Payload
def test_get_brand(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/brands')
    basic_auth_get(api, username, auth_token)


def test_post_brand(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands')
    result = basic_auth_post(api, username, auth_token, post_payload)
    newdata = json.loads(result.text)
    state['brand_id'] = newdata['id']
    assert newdata['name'] == BRAND_NAME
    assert newdata['page_title'] == PAGE_TITLE
    assert newdata['meta_keywords'] == META_KEYWORD
    assert newdata['meta_description'] == META_DESC
    assert newdata['search_keywords'] == SEARCH_KEYWORD


def test_get_brand_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands/' + str(state['brand_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['brand_id']
    assert newdata['name'] == BRAND_NAME
    assert newdata['page_title'] == PAGE_TITLE
    assert newdata['meta_keywords'] == META_KEYWORD
    assert newdata['meta_description'] == META_DESC
    assert newdata['search_keywords'] == SEARCH_KEYWORD


def test_put_brand(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands/' + str(state['brand_id']))
    result  = basic_auth_put(api, username, auth_token, put_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['brand_id']
    assert newdata['name'] == UPDATED_BRAND_NAME
    assert newdata['page_title'] == UPDATED_PAGE_TITLE
    assert newdata['meta_keywords'] == UPDATED_META_KEYWORD
    assert newdata['meta_description'] == UPDATED_META_DESC
    assert newdata['search_keywords'] == UPDATED_SEARCH_KEYWORD


def test_count_brand(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0


def test_delete_brand(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands/' + str(state['brand_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)


def test_required_fields(auth_token, url, username, state):
    #validating 'name' field
    api = urlparse.urljoin(url, 'api/v2/brands')
    result = basic_auth_post(api, username, auth_token, invalid_name_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'name' was not supplied."
    assert newdata[0]['status'] == 400
    #validating 'image' field
    api = urlparse.urljoin(url, 'api/v2/brands')
    result = basic_auth_post(api, username, auth_token, invalid_image_payload,1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'image_file' is invalid."
    assert newdata[0]['status'] == 400

# # XML Payload


def test_get_brand_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/brands')
    basic_auth_get(api, username, auth_token, payload_format='xml')


def test_post_brand_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands')
    result = basic_auth_post(api, username, auth_token, post_payload, payload_format='xml')
    newdata = etree.fromstring(result.text)

    state['brand_id_xml'] = newdata.find('id').text
    assert newdata.find('name').text == BRAND_NAME
    assert newdata.find('page_title').text == PAGE_TITLE
    assert newdata.find('meta_keywords').text == META_KEYWORD
    assert newdata.find('meta_description').text == META_DESC
    assert newdata.find('search_keywords').text == SEARCH_KEYWORD


def test_get_brand_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands/' + str(state['brand_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['brand_id_xml']
    assert newdata.find('name').text == BRAND_NAME
    assert newdata.find('page_title').text == PAGE_TITLE
    assert newdata.find('meta_keywords').text == META_KEYWORD
    assert newdata.find('meta_description').text == META_DESC
    assert newdata.find('search_keywords').text == SEARCH_KEYWORD


def test_put_brand_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands/' + str(state['brand_id_xml']))
    result  = basic_auth_put(api, username, auth_token, put_payload, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['brand_id_xml']
    assert newdata.find('name').text == UPDATED_BRAND_NAME
    assert newdata.find('page_title').text == UPDATED_PAGE_TITLE
    assert newdata.find('meta_keywords').text == UPDATED_META_KEYWORD
    assert newdata.find('meta_description').text == UPDATED_META_DESC
    assert newdata.find('search_keywords').text == UPDATED_SEARCH_KEYWORD


def test_count_brand_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/brands/count')
    result = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0


def test_delete_brand_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/brands/' + str(state['brand_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format='xml')
    basic_auth_get(api, username, auth_token, 1, payload_format='xml')


def test_required_fields_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/brands')
    #validating 'name' field
    result = basic_auth_post(api, username, auth_token, invalid_name_payload,1, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'name' was not supplied."
    assert newdata[0].find('status').text == "400"
    #validating 'image' field
    result = basic_auth_post(api, username, auth_token, invalid_image_payload,1, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'image_file' is invalid."
    assert newdata[0].find('status').text == "400"
