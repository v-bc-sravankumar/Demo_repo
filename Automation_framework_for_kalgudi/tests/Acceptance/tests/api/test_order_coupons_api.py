from fixtures.order_coupons import *
from helpers.ui.control_panel.order_class import *


def test_post_order(auth_token, url, username, state):
    """Create Order via API"""
    api = urlparse.urljoin(url, 'api/v2/orders')
    result = basic_auth_post(api, username, auth_token, post_order_payload)
    newdata = json.loads(result.text)
    state['order_id'] = newdata['id']


def test_post_coupon(auth_token, url, username, state):
    """Create Coupon via API"""
    api = urlparse.urljoin(url, 'api/v2/coupons')
    result = basic_auth_post(api, username, auth_token, post_coupon_payload)
    newdata = json.loads(result.text)
    state['coupon_id'] = newdata['id']


def test_edit_order_for_coupon(browser, url, email, password, state):
    """Edit Order for adding coupon in control panel"""
    order = OrderClass(browser)
    order.go_to_admin(browser, url, email, password)
    element = order.wait_until_element_present('Orders', "LINK")
    element.click()
    browser.find_element_by_link_text('View Orders').click()
    order.wait_until_element_present("All Orders", 'LINK')
    browser.execute_script("window.location = $('.dropdown').find('div:contains(View Orders)').find('a:eq(0)').attr('href')")
    order.wait_until_element_present('OrderActionSelect', "ID")
    order.search_order(browser, state['order_id'])
    order.wait_until_element_present("Search", 'LINK')
    browser.find_element_by_xpath("//tr[contains(., '" + str(state['order_id']) + "')]").find_element_by_css_selector('.dropdown-trigger').click()
    element = order.wait_until_element_present("Edit Order", 'LINK')
    element.click()
    browser.find_element_by_xpath('//button[text() = "Next"]').click()
    order.wait_until_element_present('quote-item-search', "ID")
    element = order.wait_until_element_present('//button[text() = "Next"]', "XPATH")
    element.click()
    order.wait_until_element_present('FormField_14', "ID")
    element = order.wait_until_element_present('//button[text() = "Next"]', "XPATH")
    element.click()
    element = order.wait_until_element_present('couponGiftCertificate', "ID")
    element.send_keys(COUPON_CODE)
    browser.execute_script("$('.orderMachineCouponButton').trigger('click')")
    order.select_dropdown_value(browser, 'paymentMethod', 'Manual Payment')
    browser.find_element_by_name('paymentField[custom][custom_name]').send_keys(MANUAL_PAYMENT_NAME)
    try:
        order.wait_until_element_present('orderSaveButton', "CLASS_NAME")
        browser.execute_script("$('.orderSaveButton').trigger('click')")
    except TimeoutException:
        browser.execute_script("$('.orderSaveButton').trigger('click')")
    element = order.wait_until_element_present('//div[@class = "alert alert-success"]/p', "XPATH").text
    assert "has been updated successfully." in element

# JSON Payload


def test_get_order_coupons(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/coupons')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    global ORDER_COUPON_ID
    ORDER_COUPON_ID = newdata[0]['id']
    assert newdata[0]['coupon_id'] == state['coupon_id']
    assert newdata[0]['order_id'] == state['order_id']
    assert newdata[0]['code'] == COUPON_CODE
    assert float(newdata[0]['amount']) == float(65)
    assert float(newdata[0]['discount']) == float(130)


def test_get_order_coupons_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/coupons/' + str(ORDER_COUPON_ID))
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata['coupon_id'] == state['coupon_id']
    assert newdata['order_id'] == state['order_id']
    assert newdata['code'] == COUPON_CODE
    assert float(newdata['amount']) == float(65)
    assert float(newdata['discount']) == float(130)

# Bug raised - Jira Ticket Number : BIG : 6829
# def test_get_order_coupons_count(auth_token, url, username, state):
#     api =urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/coupons/count')
#     result = basic_auth_get(api, username, auth_token)
#     newdata = json.loads(result.text)
#     assert newdata['count'] > 0

# XML Payload


def test_get_order_coupons_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/coupons')
    result = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(result.text)
    state['order_coupon_id_xml'] = newdata[0].find('id').text
    assert newdata[0].find('coupon_id').text == str(state['coupon_id'])
    assert newdata[0].find('order_id').text == str(state['order_id'])
    assert newdata[0].find('code').text == COUPON_CODE
    assert float(newdata[0].find('amount').text) == float(65)
    assert float(newdata[0].find('discount').text) == float(130)


def test_get_order_coupons_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/coupons/' + str(state['order_coupon_id_xml']))
    result = basic_auth_get(api, username, auth_token, payload_format='xml')
    newdata = etree.fromstring(result.text)
    assert newdata.find('coupon_id').text == str(state['coupon_id'])
    assert newdata.find('order_id').text == str(state['order_id'])
    assert newdata.find('code').text == COUPON_CODE
    assert float(newdata.find('amount').text) == float(65)
    assert float(newdata.find('discount').text) == float(130)

# Bug raised - Jira Ticket Number : BIG : 6829
# def test_get_order_coupons_count_xml_payload(auth_token, url, username, state):
#     api =urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']) + '/coupons/count')
#     result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
#     newdata = etree.fromstring(result.text)
#     assert newdata.find('count').text > 0


def test_delete_order_xml_payload(auth_token, url, username, state):
    """Delete Order via API"""
    api = urlparse.urljoin(url, 'api/v2/orders/' + str(state['order_id']))
    basic_auth_delete(api, username, auth_token, payload_format='xml')
    basic_auth_get(api, username, auth_token, 1, payload_format='xml')
