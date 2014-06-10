from helpers.ui.control_panel.tax_class import *
from helpers.ui.store_front.checkout_class import *
from helpers.ui.control_panel.shipping_class import *
from helpers.ui.control_panel.payment_class import *
from lib.api_lib import *


TAX_ZONE_NAME = CommonMethods.generate_random_string()
UPDATED_TAX_ZONE_NAME = CommonMethods.generate_random_string()
TAX_RATE_NAME = 'GST'
TAX_RATE = 20
UPDATED_TAX_RATE_NAME = 'CST'
global CASH_PAYMENT_ORDER_ID
#************************************************************************************************************
# Description:Verify TAX ZONE and Tax Rate CRUD operations in control Panel and Verify Tax rate in storefront
#************************************************************************************************************

def test_set_payment_method_and_shipping_method(browser, url, email, password):
    tax = TaxClass(browser)
    tax.go_to_admin(browser, url, email, password)
    admin_url = browser.current_url
    payment = PaymentClass(browser)
    payment.navigate_to_payment_setting()
    payment.set_cash_on_delivery_payments(browser)
    shipping = ShippingClass(browser)
    shippingapi=ShippingApi()
    #get browser cookies
    seleniumCookies= browser.get_cookies()
    requestCookies = {}
    for cookie in seleniumCookies:
        requestCookies[cookie['name']] = cookie['value']
    #US store location
    shippingapi.post_store_location(url, requestCookies, shipping.us_store_location_payload )
    #US country zone
    zoneid=shippingapi.post_shipping_zone(url, requestCookies, shipping.us_shipping_zone_payload)
    #Flat Rate Per Order
    shippingapi.post_shipping_flat_rate__per_order_method(url, requestCookies, shipping.flat_rate_per_order_payload, zoneid)

def test_create_tax_zone(browser, url, email, password):
    pytest.skip("Script setup is modified by other script prior execution")
    tax = TaxClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    tax.create_tax_zone(browser, TAX_ZONE_NAME)

def test_create_tax_rate(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    tax = TaxClass(browser)
    tax.create_tax_rate(browser, TAX_RATE_NAME, TAX_RATE)

def test_verify_tax_in_storefront(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    tax = TaxClass(browser)
    checkout=CheckoutClass(browser)
    PRODUCT_PRICE=tax.check_tax_storefront(browser, url)
    if tax.is_new_checkout_opened():
            pytest.skip("New Checkout UI")
    checkout.proceed_to_checkout(browser,url, country_data=checkout.us_checkout)
    checkout.select_shipping_method_storefront('Flat Rate Per Order')
    tax.wait_until_element_present('CartContents', "CLASS_NAME")
    txt = browser.execute_script("return $('.SubTotal').find('td:contains(Tax)').parent('tr').find('.ProductPrice').text()")
    Displayed_tax_rate = float(txt[1:])
    assert Displayed_tax_rate == (PRODUCT_PRICE * TAX_RATE) / 100
    checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
    CASH_PAYMENT_ORDER_ID = checkout.get_order_confirmation_number(browser, url)

def test_edit_tax_zone(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    tax = TaxClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    tax.edit_tax_zone(browser, TAX_ZONE_NAME, UPDATED_TAX_ZONE_NAME)

def test_edit_tax_rate(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    tax = TaxClass(browser)
    tax.edit_tax_rate(browser, UPDATED_TAX_ZONE_NAME, TAX_RATE_NAME, UPDATED_TAX_RATE_NAME)

def test_delete_tax_rate(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    tax = TaxClass(browser)
    tax.delete_tax_rate(browser, UPDATED_TAX_ZONE_NAME, UPDATED_TAX_RATE_NAME)

def test_delete_tax_zone(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    tax = TaxClass(browser)
    tax.delete_tax_zone(browser, UPDATED_TAX_ZONE_NAME)
