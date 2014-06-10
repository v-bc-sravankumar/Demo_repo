from lib.ui_lib import *

faker = Factory.create()
EMAIL = faker.email()
NAME = faker.firstName()

#*******************************************************
# Description: Verify newsletter subscription
#*******************************************************
def test_newsletter(browser, url, email, password):
    newsletter = CommonMethods(browser)
    newsletter.go_to_admin(browser, url, email, password)
    browser.find_element_by_link_text('Marketing').click()
    browser.find_element_by_link_text('Email Marketing').click()
    element = newsletter.wait_until_element_present('Export Only', "LINK")
    element.click()
    # Getting the count before customer subscription
    count = browser.find_element_by_id('emailintegration_exportonly_subscribercount').get_attribute('value')
    browser.find_element_by_link_text('Home').click()
    # Subscription to newsletter
    browser.get(url)
    browser.find_element_by_xpath('//input[@value="Submit"]').click()
    # Validating first name field
    newsletter.validate_field(browser, "You forgot to type in your first name.")
    browser.find_element_by_id('nl_first_name').send_keys(NAME)
    browser.find_element_by_xpath('//input[@value="Submit"]').click()
    # validating email field
    newsletter.validate_field(browser, "You forgot to type in your email address.")
    browser.find_element_by_id('nl_email').send_keys("john#123")
    browser.find_element_by_xpath('//input[@value="Submit"]').click()
    newsletter.validate_field(browser, "Please enter a valid email address, such as john@example.com.")
    browser.find_element_by_id('nl_email').clear()
    browser.find_element_by_id('nl_email').send_keys(EMAIL)
    browser.find_element_by_xpath('//input[@value="Submit"]').click()
    assert "THANKS FOR SUBSCRIBING!".upper() in browser.find_element_by_xpath('//h1[@class="TitleHeading"]').text.upper()
    newsletter.go_to_admin(browser, url, email, password)
    browser.find_element_by_link_text('Marketing').click()
    browser.find_element_by_link_text('Email Marketing').click()
    element = newsletter.wait_until_element_present('Export Only', "LINK")
    element.click()
    # Getting the count after customer subscription
    re_count = browser.find_element_by_id('emailintegration_exportonly_subscribercount').get_attribute('value')
    before_subcription_count = int(count)
    after_subcription_count = int(re_count)
    increment_value = before_subcription_count + 1
    # verifying before and after newsletter subscription count
    assert after_subcription_count == increment_value
