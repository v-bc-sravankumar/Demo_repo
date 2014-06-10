from lib.ui_lib import *

faker = Factory.create()
EMAIL = faker.email()
STORE_NAME = CommonMethods.generate_random_string() + " Store"
ADDRESS = faker.buildingNumber() + faker.streetName() + faker.city()
PHONE_NUMBER = faker.phoneNumber()

#*********************************************************************
# Description:Verify Pofile Page CRUD in control Panel and Store Front
#*********************************************************************
def test_profile_settings(browser, url, email, password):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    browser.find_element_by_link_text('Setup & Tools').click()
    browser.find_element_by_link_text('Profile').click()
    common.wait_until_element_present('name', "ID")
    element = browser.find_element_by_id('name')
    element.clear()
    element.send_keys(STORE_NAME)
    element = browser.find_element_by_id('StoreAddress')
    element.clear()
    element.send_keys(ADDRESS)
    browser.find_element_by_xpath('//label[@for = "home_office"]').click()
    element = browser.find_element_by_id('email_address')
    element.clear()
    # Validate email
    element.send_keys('xyz@jkj')
    assert "Please enter a valid email address."

    element.clear()
    element.send_keys(EMAIL)
    element = browser.find_element_by_id('StorePhoneNumber')
    element.clear()
    element.send_keys(PHONE_NUMBER)
    browser.find_element_by_xpath('//button[@value = "Save"]').click()
    common.wait_until_element_present("icon-success-sign", "CLASS_NAME")


def test_verify_on_storefront(browser, url):
    browser.get(url)
    assert browser.find_element_by_xpath("//div[@id = 'LogoContainer'][contains(., '" + STORE_NAME + "')]")
    assert browser.find_element_by_xpath("//div[@class = 'phoneIcon']/span[contains(., '" + PHONE_NUMBER + "')]")
