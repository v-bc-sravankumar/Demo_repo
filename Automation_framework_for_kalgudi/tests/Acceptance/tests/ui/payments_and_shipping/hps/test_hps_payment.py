from helpers.ui.control_panel.payment_class import *
from helpers.ui.control_panel.shipping_class import *
from helpers.ui.store_front.checkout_class import *
from helpers.ui.control_panel.order_class import *



def test_hps_add_shipping_method(browser, url, email, password):
    pytest.skip("HPS store creation fails in bamboo")
    payment = PaymentClass(browser)
    payment.go_to_admin(browser, url, email, password)
    shipping = ShippingClass(browser)
    shipping.navigate_to_shipping()
    if not shipping.is_new_ui(browser):
        shipping.setup_shipping_flat_rate_per_order(browser, url)
        return
    shipping.setup_store_location_new(browser, shipping.us_store_location)
    shipping.add_country_zone(browser, shipping.us_country_zone)
    shipping.open_country_zone("United States")
    shipping.setup_flat_rate(browser, shipping.flat_rate_per_order_10)


def test_hps_payment_authorize_capture_transaction(browser, url, email, password):
    pytest.skip("HPS store creation fails in bamboo")
    payment = PaymentClass(browser)
    payment.go_to_admin(browser, url, email, password)
    admin_url = browser.current_url
    payment.navigate_to_payment_setting()
    try:
            browser.find_elements_by_xpath("//button[contains(text(),'Set up')]")
            browser.find_elements_by_link_text("Heartland Payment Systems")
    except NoSuchElementException:
            raise Exception('HPS gateway not set as default')
    
    payment.set_hps_payment(browser, transtype='Authorize & Capture')
    checkout = CheckoutClass(browser)
    checkout.add_product_to_cart(browser, url)
    if checkout.is_new_checkout_opened(browser):
        pytest.skip("New Checkout UI")
    checkout.proceed_to_checkout(browser, url)
    checkout.select_shipping_method_storefront('Flat Rate Per Order')
    checkout.select_payment_option_storefront(browser, 'Heartland Payment Systems')
    checkout.enter_credit_card(browser, 'Visa', 'Testing', '4111111111111111', 'Dec', '2020', '123')
    order_id = checkout.get_order_confirmation_number(browser, admin_url)
    # Verify status ID is 11 (Awaiting Fulfillment)
    order = OrderClass(browser)
    assert '11' in order.get_order_status(browser, admin_url, order_id)    
