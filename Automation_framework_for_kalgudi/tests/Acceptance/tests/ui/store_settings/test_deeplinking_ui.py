from lib.ui_lib import *


# Check the deep linking is responsive and directing to the correct page
# Test Deeplinking the Inventory Settings page
@pytest.mark.skipif("True") # "Skipped because of Bug found, ref: BIG-8068"
def test_deep_links_inventorypg(browser, url, email, password):
    linking = CommonMethods(browser)
    inventory = "admin/settings/inventory"
    browser.get(urlparse.urljoin(url, inventory))
    browser.find_element_by_id('user_email').send_keys(email)
    browser.find_element_by_id('user_password').send_keys(password)
    browser.find_element_by_xpath('//input[@value="Log in"]').click()
    invent = "Inventory settings"
    assert invent in str(linking.wait_until_element_present('#content h1', "CSS_SELECTOR").text)

 # Test Deep linking for the Shipping page
def test_deep_links_shippingpg(browser, url, email, password):
    if 'https://' in url:
       url = url.replace('https://', 'http://')
    linking = CommonMethods(browser)
    shipping = "admin/shipping"
    browser.get(urlparse.urljoin(url, shipping))
    browser.find_element_by_id('user_email').send_keys(email)
    browser.find_element_by_id('user_password').send_keys(password)
    browser.find_element_by_xpath('//input[@value="Log in"]').click()
    linking.wait_until_element_present("shipping-to-heading", "CLASS_NAME")
    ship = "Shipping Manager"
    assert ship in browser.find_element_by_id('content').text

    '''
    With A&A login "Log out" link is replaced with in the email dropdown on CP
    Since then the Headless browser not able to recognise the "Log Out" link, hence 
    the following script will be useless and commented out. but works fine with any browser.

    ''' 
    # browser.find_element_by_link_text(email).click()
    # browser.find_element_by_id("cp-logout-btn").click()
    # linking.wait_until_element_present(browser, 'login-form-label', "CLASS_NAME")
    # #test View products page
    # viewproducts = "admin/index.php?ToDo=viewProducts"
    # browser.get(urlparse.urljoin(url, viewproducts))
    # linking.wait_until_element_present(browser, 'login-form-label', "CLASS_NAME")
    # browser.find_element_by_id('user_email').send_keys(email)
    # browser.find_element_by_id('user_password').send_keys(password)
    # browser.find_element_by_xpath('//input[@value="Log in"]').click()
    # viewproducts = "View Products"
    # assert viewproducts in browser.find_element_by_id('content').text
    # browser.find_element_by_link_text(email).click()
    # browser.find_element_by_id("cp-logout-btn").click()
    # linking.wait_until_element_present(browser, 'login-form-label', "CLASS_NAME")
    # # Store settings page
    # storesettings = "admin/index.php?ToDo=viewsettings"
    # browser.get(urlparse.urljoin(url, storesettings))
    # browser.find_element_by_id('user_email').send_keys(email)
    # browser.find_element_by_id('user_password').send_keys(password)
    # browser.find_element_by_xpath('//input[@value="Log in"]').click()
    # viewstore = "Store Settings"
    # assert viewstore in browser.find_element_by_id('content').text
    # browser.find_element_by_link_text(email).click()
    # browser.find_element_by_id("cp-logout-btn").click()
    # linking.wait_until_element_present(browser, 'login-form-label', "CLASS_NAME")
    # # Negative test - to make sure wrong password throws error.
    # browser.get(urlparse.urljoin(url, inventory))
    # browser.find_element_by_id('user_email').send_keys(email)
    # browser.find_element_by_id('user_password').send_keys("wrongpassword")
    # browser.find_element_by_xpath('//input[@value="Log in"]').click()
    # wrongpw = "The email address or password is incorrect."
    # assert wrongpw in browser.find_element_by_class_name('alert-box').text  
