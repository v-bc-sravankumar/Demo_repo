from helpers.ui.control_panel.payment_class import *
from helpers.ui.control_panel.shipping_class import *
from helpers.ui.control_panel.checkout_class import *
from helpers.ui.control_panel.order_class import *
from helpers.ui.control_panel.currency_class import *

from selenium.common.exceptions import UnexpectedAlertPresentException


def test_setup_payment(browser, url, email, password):
    payment = PaymentClass(browser)
    payment.go_to_admin(browser, url, email, password)
    payment.navigate_to_payment_setting()
    payment.set_cash_on_delivery_payments(browser)
    payment.set_authorize_net_payment(browser, transactiontype='Authorize & Capture')
    currency = CurrencyClass(browser)
    currency.navigate_to_currency()
    currency.create_currency(browser, currency.USDollar)
    currency.set_as_default(browser, currency.USDollar)


def test_setup_store_location(browser, url, email, password):
    shipping = ShippingClass(browser)
    shipping.navigate_to_shipping()
    shipping.setup_store_location_new(shipping.us_store_location)


def test_add_country_zone(browser, url, email, password):
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    shipping.navigate_to_shipping()
    shipping.add_country_zone( shipping.us_country_zone)
    if not shipping.is_country_zone_present(shipping.us_country_zone):
        pytest.fail(shipping.us_country_zone['Country']['Value'] + "zone is not displayed")


def test_setup_free_shipping(browser, url, email, password):
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    shipping.navigate_to_shipping()
    shipping.open_country_zone( "United States")
    row = shipping.setup_free_shipping()
    # check shipping method is displayed
    assert "Free shipping" in row.text
    # check edit button is enabled
    assert  row.find_element_by_css_selector("button")
    #checkout
    try:
        checkout = CheckoutClass(browser)
        checkout.add_product_to_cart(browser, url)
        if checkout.is_new_checkout_opened():
            orderid = checkout.continue_new_checkout(browser, 'Free Shipping', checkout.us_shipping_address)
            order = OrderClass(browser)
            checkout.assert_order_for_new_checkout(browser, url, email, password, orderid)
            #check Shipping method in order details
            assert 'Free Shipping' in order.find_element_by_css_selector('.qview-shipping-destination').text
            return
        checkout.proceed_to_checkout(browser, url, checkout.us_checkout)
        checkout.select_shipping_method_storefront('Free Shipping')
        checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
        order_id = checkout.get_order_confirmation_number(browser, admin_url)
        order = OrderClass(browser)
        admin_url = urlparse.urljoin(url, 'admin')
        browser.get(admin_url)
        order.goto_view_orders(browser)
        order.search_order(browser, order_id)
        #check Shipping method in order details
        assert 'Free Shipping' in order.find_element_by_css_selector('.qview-shipping-destination').text
    except UnexpectedAlertPresentException:
        raise UnexpectedAlertPresentException()

