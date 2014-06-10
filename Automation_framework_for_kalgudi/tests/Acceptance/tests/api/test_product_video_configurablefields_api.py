from lib.ui_lib import *
from fixtures.product import *

PRODUCT_VIDEO_URL="http://www.youtube.com/watch?v=RY09M9wg1is"

# JSON Payload
# Create product via API
def test_post_product(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products')
    result = basic_auth_post(api, username, auth_token, post_payload)
    newdata = json.loads(result.text)
    state['product_id'] = newdata['id']
    state['product_name'] = newdata['name']

# Update product via control panel
def test_edit_product(browser, url, email, password, state):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    # Navigate direct to edit product link
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('View Products').click()
    browser.get(urlparse.urljoin(url, 'admin/index.php?ToDo=editProduct&productId=' + str(state['product_id'])))
    #Add a video to product
    common.wait_until_element_present('Images & Videos', 'LINK').click()
    browser.find_element_by_id('product-videos-search-query').send_keys(PRODUCT_VIDEO_URL)
    element= common.wait_until_element_present('product-videos-search', 'ID')
    element.click()
    element= common.wait_until_element_present('//span[text() = "Select video"]', 'XPATH')
    element.click()
    #Add custom fields to product
    browser.find_element_by_link_text('Custom Fields').click()
    common.select_dropdown_value(browser, "configurable-field-0-type", "Short Text")
    browser.find_element_by_id("configurable-field-0-name").send_keys("Manufacturing Country")
    browser.find_element_by_css_selector('.button-group .dropdown-trigger').click()
    time.sleep(1)
    browser.find_element_by_id("save-exit-product-button").click()
    browser.find_element_by_xpath('//button[text()="Continue"]').click()

#Json payload
# Get product video details via API
def test_get_product_videos(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/videos')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata[0]['id'] == "9yHl24QynOM"
    assert newdata[0]['product_id'] == state['product_id']
    assert newdata[0]['name'] == "traktor racing volvo terror"
    assert newdata[0]['sort_order'] == 1


def test_count_product_videos(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/videos/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0

# Get product configurablefields details via API
def test_get_product_configurablefields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/configurablefields')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    state['configurable_field_id'] = newdata[0]['id']
    assert newdata[0]['id'] > 0
    assert newdata[0]['name'] == "Manufacturing Country"
    assert newdata[0]['type'] == "T"
    assert newdata[0]['is_required'] == False
    assert newdata[0]['sort_order'] == 1


def test_get_product_configurablefields_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/configurablefields/' + str(state['configurable_field_id']))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['name'] == "Manufacturing Country"
    assert newdata['type'] == "T"
    assert newdata['is_required'] == False
    assert newdata['sort_order'] == 1


def test_count_product_configurablefields(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/configurablefields/count')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['count'] > 0

# XML payload
# Get product video details via API
def test_get_product_videos_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/videos')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('id').text == "9yHl24QynOM"
    assert newdata[0].find('product_id').text == str(state['product_id'])
    assert newdata[0].find('name').text == "traktor racing volvo terror"
    assert newdata[0].find('sort_order').text == "1"


def test_count_product_videos_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/videos/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0

# Get product configurablefields details via API
def test_get_product_configurablefields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/configurablefields')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['configurable_field_id_xml'] = newdata[0].find('id').text
    assert newdata[0].find('id').text > 0
    assert newdata[0].find('name').text == "Manufacturing Country"
    assert newdata[0].find('type').text == "T"
    assert newdata[0].find('is_required').text == "false"
    assert newdata[0].find('sort_order').text == "1"


def test_get_product_configurablefields_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/configurablefields/' + str(state['configurable_field_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('name').text == "Manufacturing Country"
    assert newdata.find('type').text == "T"
    assert newdata.find('is_required').text == "false"
    assert newdata.find('sort_order').text == "1"


def test_count_product_configurablefields_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']) + '/configurablefields/count')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('count').text > 0


def test_delete_product_created_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/products/' + str(state['product_id']))
    basic_auth_delete(api, username, auth_token, payload_format = 'xml')
    basic_auth_get(api, username, auth_token, 1, payload_format = 'xml')
