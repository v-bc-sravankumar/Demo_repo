from lib.ui_lib import *

faker = Factory.create()
EMAIL = faker.email()
PASSWORD = faker.word()
FIRSTNAME = faker.firstName() + CommonMethods.generate_random_string()
LASTNAME = faker.lastName() + CommonMethods.generate_random_string()
COMPANY = faker.company()
PHONE = faker.phoneNumber()
ADDRESS1 = faker.buildingNumber()
ADDRESS2 = faker.streetName()
CITY = faker.city()
COUNTRY = "Australia"
STATE = "New South Wales"
POSTCODE = '2000'
#*****************************************************************
# Description : Verify create a account at storefront
#               And adding a product to wishlist
#*****************************************************************
def test_createaccount_storefront(browser, url, email, password):
    account = CommonMethods(browser)
    account.go_to_admin(browser, url, email, password)
    # Disable captcha from control panel
    account.disable_captcha_onlycustomer(browser)
    browser.get(url)
    # create a customer account from storefront
    account.wait_until_element_present("Create an account", "LINK").click()
    browser.find_element_by_id('FormField_1').send_keys(EMAIL)
    browser.find_element_by_id('FormField_2').send_keys(PASSWORD)
    browser.find_element_by_id('FormField_3').send_keys(PASSWORD)
    browser.find_element_by_id('FormField_4').send_keys(FIRSTNAME)
    browser.find_element_by_id('FormField_5').send_keys(LASTNAME)
    browser.find_element_by_id('FormField_6').send_keys(COMPANY)
    browser.find_element_by_id('FormField_7').send_keys(PHONE)
    browser.find_element_by_id('FormField_8').send_keys(ADDRESS1)
    browser.find_element_by_id('FormField_9').send_keys(ADDRESS2)
    browser.find_element_by_id('FormField_10').send_keys(CITY)
    Select(browser.find_element_by_id('FormField_11')).select_by_visible_text('Australia')
    account.wait_until_element_present("//select[@id='FormField_12']/option[contains(.,'New South Wales')]", "XPATH")
    Select(browser.find_element_by_id('FormField_12')).select_by_visible_text('New South Wales')
    browser.find_element_by_id('FormField_13').send_keys(POSTCODE)
    browser.find_element_by_xpath("//input[contains(@value,'Create My Account')]").click()
    assert "Your Account Has Been Created" in browser.find_element_by_xpath('//div[@class="Block"]').text
    # Add a product to wishlist
    browser.get(urlparse.urljoin(url, '/anna-bright-single-bangles'))
    browser.find_element_by_xpath("//input[contains(@value,'Add to Wishlist')]").click()
    account.wait_until_element_present('//div[@class = "BlockContent"]/p[@class = "SuccessMessage"]', "XPATH")
    element = browser.find_element_by_xpath('//div[@class = "BlockContent"]/p[@class = "SuccessMessage"]').text
    assert "The item has been added to your wish list." in element
    account.wait_until_element_present("Sign out", "LINK"). click()
