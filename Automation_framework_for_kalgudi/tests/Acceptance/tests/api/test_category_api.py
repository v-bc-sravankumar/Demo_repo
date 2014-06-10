from fixtures.category import *

# JSON Payload

def test_get_category(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/categories')
    basic_auth_get(api, username, auth_token)


def test_post_category(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/categories')
    result = basic_auth_post(api, username, auth_token, post_payload)
    newdata = json.loads(result.text)
    state['category_id'] = newdata['id']
    assert newdata['name'] == CATAGORY_NAME
    assert newdata['description'] == CATEGORY_DESCRIPTION
    assert newdata['sort_order'] == 1
    assert newdata['page_title'] == PAGE_TITLE
    assert newdata['meta_keywords'] == META_KEYWORD
    assert newdata['meta_description'] == META_DESC
    assert newdata['layout_file'] == "category.html"
    assert newdata['is_visible'] == True
    assert newdata['search_keywords'] == SEARCH_KEYWORD

def test_get_category_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/categories/' + str(state['category_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['category_id']
    assert newdata['name'] == CATAGORY_NAME
    assert newdata['description'] == CATEGORY_DESCRIPTION
    assert newdata['sort_order'] == 1
    assert newdata['page_title'] == PAGE_TITLE
    assert newdata['meta_keywords'] == META_KEYWORD
    assert newdata['meta_description'] == META_DESC
    assert newdata['layout_file'] == "category.html"
    assert newdata['is_visible'] == True
    assert newdata['search_keywords'] == SEARCH_KEYWORD

def test_put_category(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/categories/' + str(state['category_id']))
    result  = basic_auth_put(api, username, auth_token, put_payload)
    newdata = json.loads(result.text)
    assert newdata['id'] == state['category_id']
    assert newdata['name'] == UPDATED_CATAGORY_NAME
    assert newdata['description'] == UPDATED_CATEGORY_DESCRIPTION
    assert newdata['sort_order'] == 2
    assert newdata['page_title'] == UPDATED_PAGE_TITLE
    assert newdata['meta_keywords'] == UPDATED_META_KEYWORD
    assert newdata['meta_description'] == UPDATED_META_DESC
    assert newdata['is_visible'] == False
    assert newdata['search_keywords'] == UPDATED_SEARCH_KEYWORD

def test_count_category(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/categories/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0

def test_delete_category(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/categories/' + str(state['category_id']))
    basic_auth_delete(api, username, auth_token)
    basic_auth_get(api, username, auth_token, 1)

def test_required_fields(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/categories')
    #validating 'name' field
    result = basic_auth_post(api, username, auth_token, required_name_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The required field 'name' was not supplied."
    assert newdata[0]['status'] == 400
    #validating 'sort order field'
    result = basic_auth_post(api, username, auth_token, invalid_sortorder_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'sort_order' is invalid."
    assert newdata[0]['status'] == 400
     #validating 'is_visible' field
    result = basic_auth_post(api, username, auth_token, invalid_isvisible_payload, 1)
    newdata = json.loads(result.text)
    assert newdata[0]['message'] == "The field 'is_visible' is invalid."
    assert newdata[0]['status'] == 400


# XML Payload

def test_get_category_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/categories')
    basic_auth_get(api, username, auth_token, payload_format='xml')

def test_post_category_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/categories')
    result = basic_auth_post(api, username, auth_token, post_payload, payload_format='xml')
    newdata = etree.fromstring(result.text)
    state['category_id_xml'] = newdata.find('id').text
    assert newdata.find('name').text == CATAGORY_NAME
    assert newdata.find('description').text == CATEGORY_DESCRIPTION
    assert newdata.find('sort_order').text == "1"
    assert newdata.find('page_title').text == PAGE_TITLE
    assert newdata.find('meta_keywords').text == META_KEYWORD
    assert newdata.find('meta_description').text == META_DESC
    assert newdata.find('layout_file').text == "category.html"
    assert newdata.find('is_visible').text == "true"
    assert newdata.find('search_keywords').text == SEARCH_KEYWORD

def test_get_category_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/categories/' + str(state['category_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['category_id_xml']
    assert newdata.find('name').text == CATAGORY_NAME
    assert newdata.find('description').text == CATEGORY_DESCRIPTION
    assert newdata.find('sort_order').text == "1"
    assert newdata.find('page_title').text == PAGE_TITLE
    assert newdata.find('meta_keywords').text == META_KEYWORD
    assert newdata.find('meta_description').text == META_DESC
    assert newdata.find('layout_file').text == "category.html"
    assert newdata.find('is_visible').text == "true"
    assert newdata.find('search_keywords').text == SEARCH_KEYWORD

def test_put_category_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/categories/' + str(state['category_id_xml']))
    result  = basic_auth_put(api, username, auth_token, put_payload, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('id').text == state['category_id_xml']
    assert newdata.find('name').text == UPDATED_CATAGORY_NAME
    assert newdata.find('description').text == UPDATED_CATEGORY_DESCRIPTION
    assert newdata.find('sort_order').text == "2"
    assert newdata.find('page_title').text == UPDATED_PAGE_TITLE
    assert newdata.find('meta_keywords').text == UPDATED_META_KEYWORD
    assert newdata.find('meta_description').text == UPDATED_META_DESC
    assert newdata.find('layout_file').text == "category.html"
    assert newdata.find('is_visible').text == "false"
    assert newdata.find('search_keywords').text == UPDATED_SEARCH_KEYWORD


def test_count_category_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/categories/count')
    result = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0

def test_delete_category_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/categories/' + str(state['category_id_xml']))
    basic_auth_delete(api, username, auth_token, payload_format='xml')
    basic_auth_get(api, username, auth_token, 1, payload_format='xml')

def test_required_fields_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/categories')
    #validating 'name' field
    result = basic_auth_post(api, username, auth_token, required_name_payload, 1, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The required field 'name' was not supplied."
    assert newdata[0].find('status').text == "400"
    #validating 'sort order field'
    result = basic_auth_post(api, username, auth_token, invalid_sortorder_payload, 1, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'sort_order' is invalid."
    assert newdata[0].find('status').text == "400"
     #validating 'is_visible' field
    result = basic_auth_post(api, username, auth_token, invalid_isvisible_payload, 1, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('message').text == "The field 'is_visible' is invalid."
    assert newdata[0].find('status').text == "400"
