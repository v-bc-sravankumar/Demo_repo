from helpers.ui.control_panel.payment_class import *
from helpers.ui.store_front.checkout_class import *

faker = Factory.create()
RECIPIENT_NAME = "recipient" + faker.firstName()
RECIPIENT_EMAIL = "recipient" + faker.email()
SENDER_NAME = "sender" + faker.firstName()
SENDER_EMAIL = "sender" + faker.email()
CERTIFICATE_AMOUNT = "500"

# checkout as guest
GUEST_EMAIL = "guest" + CommonMethods.generate_random_string() + "@bigcommerce.com"
FIRSTNAME = faker.firstName()
LASTNAME = faker.lastName()
COMPANY = faker.company()
PHONE = faker.phoneNumber()
ADDRESS1 = "4 CORPORATE SQ"
ADDRESS2 = ""
CITY = "Sydney"
POSTCODE = "30329"

#************************************************************
# Description:Verify purchase a gift certificate in storefront
#************************************************************


def test_set_payment_method(browser, url, email, password):
    payment = PaymentClass(browser)
    payment.go_to_admin(browser, url, email, password)
    payment.navigate_to_payment_setting()
    payment.set_securepay_payments(browser)
    payment.set_feature_flag(browser, 'disable', 'OptimizedCheckout')


def test_purchase_gift_certificate(browser, url, email, password):
    common = CommonMethods(browser)
    browser.get(url)
    element = common.wait_until_element_present("Gift Certificates", "LINK")
    element.click()
    payment = PaymentClass(browser)
    payment.find_element_by_id('to_name')
    browser.find_element_by_id('to_name').send_keys(RECIPIENT_NAME)
    browser.find_element_by_id('to_email').send_keys("xyz@jkj")
    try:
        browser.find_element_by_id('SaveCertificate').click()
    except WebDriverException as e:
        if "Click succeeded but Load Failed" in e.msg:
            pass
    # Validate Email
    email_message = "Please enter a valid email address for the person you wish to send this gift certificate to."
    payment.validate_field(browser, email_message)
    browser.find_element_by_id('to_email').clear()
    browser.find_element_by_id('to_email').send_keys(RECIPIENT_EMAIL)
    browser.find_element_by_id('from_name').send_keys(SENDER_NAME)
    browser.find_element_by_id('from_email').send_keys(SENDER_EMAIL)
    browser.find_element_by_id('certificate_amount').send_keys('abcd')
    browser.find_element_by_id('SaveCertificate').click()
    # Validate Amount
    payment.validate_field(browser, "Please enter a valid amount for this gift certificate.")
    browser.find_element_by_id('certificate_amount').clear()
    browser.find_element_by_id('certificate_amount').send_keys('1500')
    browser.find_element_by_id('SaveCertificate').click()
    # Validate Amount between minimum and maximum gift certificate value
    payment.validate_field(browser, "Please enter an amount between the minimum and maximum gift certificate value.")
    browser.find_element_by_id('certificate_amount').clear()
    browser.find_element_by_id('certificate_amount').send_keys(CERTIFICATE_AMOUNT)
    browser.find_element_by_id('agree2').click()
    browser.find_element_by_class_name('themeCheck').click()
    browser.find_element_by_id('SaveCertificate').click()
    message_confirmation = "Your gift certificate has been generated and saved in your cart."
    assert message_confirmation in browser.find_element_by_xpath('//div[@id = "CartStatusMessage"]').text


def test_verify_gift_certificate(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    if 'https://' in url:
        url = url.replace('https://', 'http://')
    payment = PaymentClass(browser)
    element = payment.wait_until_element_present('//a[contains(@href,"checkout.php")]', 'XPATH')
    element.click()
    element = payment.wait_until_element_present('uniform-checkout_type_guest', "ID",time=90)
    element.click()
    # Checkout as Guest
    browser.find_element_by_id('CreateAccountButton').click()
    element = payment.wait_until_element_present('FormField_1', 'ID')
    element.send_keys(GUEST_EMAIL)
    browser.find_element_by_id('FormField_4').send_keys(FIRSTNAME)
    browser.find_element_by_id('FormField_5').send_keys(LASTNAME)
    browser.find_element_by_id('FormField_6').send_keys(COMPANY)
    browser.find_element_by_id('FormField_7').send_keys(PHONE)
    browser.find_element_by_id('FormField_8').send_keys(ADDRESS1)
    browser.find_element_by_id('FormField_9').send_keys(ADDRESS2)
    browser.find_element_by_id('FormField_10').send_keys(CITY)
    Select(browser.find_element_by_id('FormField_11')).select_by_value('Australia')
    time.sleep(2)
    Select(browser.find_element_by_id('FormField_12')).select_by_value('New South Wales')
    browser.find_element_by_id('FormField_13').send_keys(POSTCODE)
    browser.find_element_by_css_selector('.Submit .billingButton').click()
    payment.wait_until_element_present('CartContents', "CLASS_NAME")
    displayed_gift_rate = browser.execute_script("return $('.CartContents').find('td:contains(Gift Certificate)').parent('tr').find('.ProductPrice').text()")
    assert CERTIFICATE_AMOUNT in displayed_gift_rate
    checkout = CheckoutClass(browser)
    checkout.select_payment_option_storefront(browser, 'SecurePay')
    checkout.enter_credit_card(browser, 'Visa', 'test', '4242424242424242', 'Dec', '2020', '123')
    checkout.get_order_confirmation_number(browser, url)
