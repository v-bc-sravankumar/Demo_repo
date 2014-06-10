from lib.ui_lib import *
from lib.api_lib import *
from helpers.ui.control_panel.payment_class import *

def test_create_payment_method_in_control_panel(browser, url, email, password):
    payment = PaymentClass(browser)
    payment.go_to_admin(browser, url, email, password)
    browser.find_element_by_link_text('Setup & Tools').click()
    browser.find_element_by_link_text('Payments').click()
    payment.wait_until_element_present('tab0', "ID")
    payment.set_cash_on_delivery_payments(browser)
    payment.wait_until_element_present('tab0', "ID")
    payment.set_securepay_payments(browser)

# Json Payload
def test_get_payment_methods(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/payments/methods')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata[0]['code'] is not None
    assert newdata[0]['name'] is not None
    assert newdata[0]['test_mode'] is not None

# XML Payload
def test_get_payment_methods_xml_payload(auth_token, url, username):
    api = urlparse.urljoin(url, 'api/v2/payments/methods')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('code').text is not None
    assert newdata[0].find('name').text is not None
    assert newdata[0].find('test_mode').text is not None
