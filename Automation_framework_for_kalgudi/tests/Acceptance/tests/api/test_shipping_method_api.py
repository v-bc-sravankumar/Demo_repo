from lib.api_lib import *
from helpers.ui.control_panel.shipping_class import *


AUSPOST_AUTH_KEY = "bzfac4efaf7e7e51a4b1dbd7cc76cb31"

@pytest.mark.skipif("True")
def ttest_disable_shipping_reboot(browser, url, email, password):
    shipping = ShippingClass(browser)
    shipping.go_to_admin(browser, url, email, password)
    shipping.set_feature_flag(browser, 'disable', 'ShippingReboot')


def test_create_australian_post_in_control_panel(browser, url, email, password):
    shipping = ShippingClass(browser)
    shipping.go_to_admin(browser, url, email, password)
    shipping.navigate_to_shipping()
    shipping.skip_shipping_intro()
    if not shipping.is_new_ui(browser):
        shipping.setup_store_location_new(shipping.au_store_location)
        shipping.add_country_zone(shipping.us_country_zone)
        shipping.open_country_zone("United States")
        shipping.setup_australia_post()
        return
    pytest.skip("Not new UI")
    shipping.setup_store_location(browser, "Australia","New South Wales","2000")
    browser.find_element_by_id('tab1').click()
    browser.execute_script("$('#div1 .dropdown-trigger:first').click()")
    WebDriverWait(browser, 30).until(lambda s: s.find_element_by_link_text('Edit Methods').is_displayed() and s.find_element_by_link_text('Edit Methods'))
    browser.find_element_by_link_text('Edit Methods').click()
    shipping.disable_the_shipping_method(browser)
    WebDriverWait(browser, 30).until(lambda s: s.find_element_by_xpath('//input[@value="Add a Shipping Method..."]').is_displayed() and s.find_element_by_xpath('//input[@value="Add a Shipping Method..."]'))
    browser.find_element_by_xpath('//input[@value="Add a Shipping Method..."]').click()
    browser.find_element_by_xpath('//span[text()="Australia Post"]').click()
    element= shipping.wait_until_element_present('shipping_australiapost_auth_key', "ID")
    element.send_keys(AUSPOST_AUTH_KEY)
    element = shipping.wait_until_element_present('Select All', 'LINK')
    element.click()
    browser.find_element_by_name('SubmitButton1').click()
    shipping.verify_and_assert_success_message(browser, "The shipping method has been created successfully.", ".alert-success")

#JSON Payload


def test_get_shipping_methods(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/shipping/methods')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    state['shipping_method_id'] = newdata[0]['id']
    assert newdata[0]['id'] > 0
    for item in newdata:
        try:
            assert newdata[item].find('name').text == "Australia Post"
            assert newdata[item].find('method_name').text == "shipping_australiapost"
            return
        except:
            pass
    return False


def test_get_shipping_methods_by_id(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/shipping/methods/' + str(state['shipping_method_id']) + '')
    result = basic_auth_get(api, username, auth_token)
    newdata = json.loads(result.text)
    assert newdata[0]['id'] > 0
    for item in newdata:
        try:
            assert newdata[item].find('name').text == "Australia Post"
            assert newdata[item].find('method_name').text == "shipping_australiapost"
            return
        except:
            pass
    return False

# XML Payload


def test_get_shipping_methods_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/shipping/methods')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    state['shipping_method_id_xml'] = newdata[0].find('id').text
    assert newdata[0].find('id').text > 0
    for item in newdata:
        try:
            assert newdata[item].find('name').text == "Australia Post"
            assert newdata[item].find('method_name').text == "shipping_australiapost"
            return
        except:
            pass
    return False


def test_get_shipping_methods_by_id_xml_payload(auth_token, url, username, state):
    api = urlparse.urljoin(url, 'api/v2/shipping/methods/' + str(state['shipping_method_id_xml']) + '')
    result = basic_auth_get(api, username, auth_token, payload_format = 'xml')
    newdata = etree.fromstring(result.text)
    assert newdata[0].find('id').text > 0
    for item in newdata:
        try:
            assert newdata[item].find('name').text == "Australia Post"
            assert newdata[item].find('method_name').text == "shipping_australiapost"
            return
        except:
            pass
    return False
    
    

@pytest.mark.skipif("True")
def ttest_delete_australian_post_in_control_panel(browser, url, email, password):
    shipping = ShippingClass(browser)
    shipping.go_to_admin(browser, url, email, password)
    browser.find_element_by_link_text("Setup & Tools").click()
    browser.find_element_by_link_text('Shipping').click()
    browser.find_element_by_id('tab1').click()
    browser.execute_script("$('#div1 .dropdown-trigger:first').click()")
    WebDriverWait(browser, 30).until(lambda s: s.find_element_by_link_text('Edit Methods').is_displayed() and s.find_element_by_link_text('Edit Methods'))
    browser.find_element_by_link_text('Edit Methods').click()
    browser.execute_script("$('.GridRow').find('td:contains(Australia Post)').parent('tr').children('td:eq(0)').find('input').attr('checked','checked')")
    browser.find_element_by_xpath('//input[@value="Delete Selected"]').click()
    try:
        alert = browser.switch_to_alert()
        alert.accept()
    except WebDriverException:
        browser.execute_script("window.confirm = function(){return true;}");
        browser.find_element_by_xpath('//input[@value="Delete Selected"]').click()
    shipping.verify_and_assert_success_message(browser, "The selected shipping methods have been deleted successfully.", ".alert-success")
