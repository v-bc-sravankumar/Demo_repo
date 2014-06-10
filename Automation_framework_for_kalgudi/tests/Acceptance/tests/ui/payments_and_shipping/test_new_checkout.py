from helpers.ui.control_panel.shipping_class import *
from helpers.ui.store_front.checkout_class import *
from helpers.ui.control_panel.order_class import *
from helpers.ui.control_panel.payment_class import *
from helpers.ui.control_panel.customer_class import *
from fixtures.account import *

faker = Factory.create()
FIRST_NAME = faker.firstName() + CommonMethods.generate_random_string()
LAST_NAME = faker.lastName() + CommonMethods.generate_random_string()
COMPANY = faker.company()
PHONE = faker.phoneNumber()
EMAIL = faker.email()
EMAIL = EMAIL.translate(None, ",!;#'?$%^&*()-~")

def test_setup_payment_shipping(browser, url, email, password):
    #setup Shipping
    shipping = ShippingClass(browser)
    shipping.go_to_admin(browser, url, email, password)
    #setup Payment
    payment = PaymentClass(browser)
    payment.navigate_to_payment_setting()
    payment.set_authorize_net_payment(browser, transactiontype='Authorize & Capture')
    shippingapi=ShippingApi()
    #get browser cookies
    seleniumCookies= browser.get_cookies()
    requestCookies = {}
    for cookie in seleniumCookies:
        requestCookies[cookie['name']] = cookie['value']
    #US store location
    shippingapi.post_store_location(url, requestCookies, shipping.us_store_location_payload )
    #US country zone
    zoneid=shippingapi.post_shipping_zone(url, requestCookies, shipping.au_shipping_zone_payload)
    #Flat Rate Per Order
    shippingapi.post_shipping_flat_rate__per_order_method(url, requestCookies, shipping.flat_rate_per_order_payload, zoneid)

    if "bigcommerce.com" not in url:
        shipping.set_feature_flag(browser, 'enable', 'OptimizedCheckout')

    checkout = CheckoutClass(browser)
    checkout.navigate_to_checkout_setup()

    try:
        element = payment.wait_until_element_present('enableBetaCheckoutButton', 'ID')
        element.click()
    except TimeoutException:
        payment.wait_until_element_present('disableBetaCheckoutButton', 'ID')
    payload={"OptimizedCheckoutRampup":100}
    apipath = urlparse.urljoin(url, 'admin/settings/checkout')
    r = requests.post(apipath, data=json.dumps(payload), headers={'content-type': 'application/json'}, cookies=requestCookies, verify=False)
    assert r.status_code==200


def test_setup_faultypayment(browser, url, email, password):
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    #Negative test for Payment
    payment = PaymentClass(browser)
    payment.navigate_to_payment_setting()
    payment.set_authorize_net_payment_faulty(browser, "invalid")

def test_new_checkout_negative_test(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    checkout = CheckoutClass(browser)
    payment=PaymentClass(browser)
    # Navigate to Storefront & Add a Product to Cart
    checkout.add_product_to_cart(browser, url)

    if not checkout.is_new_checkout_opened():
        pytest.skip("Not new Checkout UI")
    checkout.add_gift_cert_negative()   #Negative test for gift certificate. This asserts the presence of Error Message#
    #checkout.edit_cart_negative()       #Negative test for edit cart. This asserts the presence of Error Message#
    checkout.account_details(checkout.account_details_new)
    checkout.add_ship_bill_address(checkout.au_shipping_address)
    checkout.select_shipping_method_new("Flat Rate Per Order")
    checkout.add_ship_bill_address(checkout.us_billing_address)

    checkout.add_credit_card_new(checkout.visa_card)
    checkout.check_order_total()
    checkout.check_payment_failure(browser)  #Faulty Payment must fail here. This asserts the presence of Error Message#
    #Go back to Admin to reset the correct payment details
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    payment.navigate_to_payment_setting()
    payment.set_authorize_net_payment_faulty(browser, PaymentCredentials.authorize_net_credentials[0][1])

def test_new_checkout(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    checkout = CheckoutClass(browser)
    #checkout.go_to_admin(browser, url, email, password)
    #couponcode=checkout.search_coupon_code(browser, url) #searches a valid coupon code
    # Navigate to Storefront & Add a Product to Cart
    checkout.add_product_to_cart(browser, url)

    if not checkout.is_new_checkout_opened():
        pytest.skip("Not new Checkout UI")
    #checkout.add_coupon_code(couponcode)
    checkout.account_details(checkout.account_details_new)
    orderID=checkout.continue_new_checkout(browser, 'Flat Rate Per Order',checkout.us_shipping_address)
    order = OrderClass(browser)
    checkout.assert_order_for_new_checkout(browser, url, email, password, orderID)
    #check Shipping method in order details
    assert 'Flat Rate Per Order' in order.find_element_by_css_selector('.qview-shipping-destination').text
    #Check Payment method in order details
    assert 'Authorize.net' in order.find_element_by_css_selector('.qview-order-details').text
    # check customer is created
    browser.get(urlparse.urljoin(url, '/admin/index.php?ToDo=viewCustomers'))
    assert checkout.account_details_new['Username']['Value'] in str(checkout.find_element_by_css_selector('.customer-details-table').text)

def test_loggedin_customer(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    customer=CustomerClass(browser)
    checkout = CheckoutClass(browser)
    order = OrderClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    account_email=customer.create_customer(browser, FIRST_NAME, LAST_NAME, COMPANY, EMAIL, PHONE)
    browser.find_element_by_xpath("//tr[contains(.,'" + FIRST_NAME + "')]").find_element_by_css_selector('.dropdown-trigger').click()
    customer.create_customer_address(browser, customer.au_customer_address)
    customer.create_customer_address(browser, customer.us_customer_address)
    couponcode = checkout.search_coupon_code(browser, url) #searches a valid coupon code

    # Navigate to Storefront & Add a Product to Cart
    checkout.add_product_to_cart(browser, url)
    if not checkout.is_new_checkout_opened():
        pytest.skip("Not new Checkout UI")
    #click i have an account link
    checkout.add_coupon_code(couponcode)  #applies the searched coupon code
    checkout.wait_until_element_present("//nav/descendant::li/a[text()='Email']", 'XPATH', time=60).click()
    checkout.find_element_by_css_selector('.co-step-account--guest--haveaccount').click()
    checkout.account_details(checkout.account_details_new, email=account_email)
    checkout.select_existing_address(browser, customer.au_customer_address)
    checkout.wait_until_element_present("//nav/descendant::li[contains(@class, 'is-active')]/a[text()='Billing']", 'XPATH',time=60)
    checkout.select_shipping_method_new("Flat Rate Per Order")
    checkout.select_existing_address(browser, customer.us_customer_address)
    checkout.add_credit_card_new(checkout.visa_card)
    checkout.check_order_total()
    # get order id
    orderID = checkout.pay_for_order()
    browser.get(admin_url)
    order.goto_view_orders(browser)
    order.search_order(browser, orderID)
    #check Shipping method in order details
    assert 'Flat Rate Per Order' in order.find_element_by_css_selector('.qview-shipping-destination').text
    #Check Payment method in order details
    assert 'Authorize.net' in order.find_element_by_css_selector('.qview-order-details').text

def test_disable_feature_flag(browser, url):
    shipping= ShippingClass(browser)
    if "bigcommerce.com" not in url:
        shipping.set_feature_flag(browser, 'disable', 'OptimizedCheckout')
