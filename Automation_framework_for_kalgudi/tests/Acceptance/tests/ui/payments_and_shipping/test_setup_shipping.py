from helpers.ui.control_panel.payment_class import *
from helpers.ui.control_panel.shipping_class import *
from helpers.ui.store_front.checkout_class import *
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


def test_setup_flat_rate_per_order(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    shipping.navigate_to_shipping()
    shipping.open_country_zone("United States")
    row = shipping.setup_flat_rate(shipping.flat_rate_per_order_10)
    # check shipping method is displayed
    assert "Flat Rate Per Order" in row.text
    # check edit button is enabled
    assert row.find_element_by_css_selector("button")
    #checkout
    try:
        checkout = CheckoutClass(browser)
        checkout.add_product_to_cart(browser, url)
        if checkout.is_new_checkout_opened():
            orderid = checkout.continue_new_checkout(browser, 'Flat Rate Per Order', checkout.us_shipping_address)
            order = OrderClass(browser)
            checkout.assert_order_for_new_checkout(browser, url, email, password, orderid)
            #check Shipping method in order details
            assert 'Flat Rate Per Order' in order.find_element_by_css_selector('.qview-shipping-destination').text
            return
        checkout.proceed_to_checkout(browser, url, checkout.us_checkout)
        checkout.select_shipping_method_storefront('Flat Rate Per Order')
        checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
        order_id = checkout.get_order_confirmation_number(browser, admin_url)
        order = OrderClass(browser)
        admin_url = urlparse.urljoin(url, 'admin')
        browser.get(admin_url)
        order.goto_view_orders(browser)
        order.search_order(browser, order_id)
        #check Shipping method in order details
        assert 'Flat Rate Per Order' in order.find_element_by_class_name('qview-shipping-destination').text
    except UnexpectedAlertPresentException:
        raise UnexpectedAlertPresentException()


def test_setup_flat_rate_per_item(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    try:
        shipping = ShippingClass(browser)
        admin_url = urlparse.urljoin(url, 'admin')
        browser.get(admin_url)
        shipping.navigate_to_shipping()
        shipping.open_country_zone("United States")
        row = shipping.setup_flat_rate(shipping.flat_rate_per_item_10)
        # check shipping method is displayed
        assert "Flat Rate Per Item" in row.text
        # check edit button is enabled
        # assert row.find_element_by_class_name("btn")
        assert  row.find_element_by_css_selector("button")
        checkout = CheckoutClass(browser)
        checkout.add_product_to_cart(browser, url)
        if checkout.is_new_checkout_opened():
            orderid = checkout.continue_new_checkout(browser, 'Flat Rate Per Item', checkout.us_shipping_address)
            order = OrderClass(browser)
            checkout.assert_order_for_new_checkout(browser, url, email, password, orderid)
            #check Shipping method in order details
            assert 'Flat Rate Per Item' in order.find_element_by_css_selector('.qview-shipping-destination').text
            return
        checkout.proceed_to_checkout(browser, url, checkout.us_checkout)
        checkout.select_shipping_method_storefront('Flat Rate Per Item')
        checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
        order_id = checkout.get_order_confirmation_number(browser, admin_url)
        order = OrderClass(browser)
        admin_url = urlparse.urljoin(url, 'admin')
        browser.get(admin_url)
        order.goto_view_orders(browser)
        order.search_order(browser, order_id)
        #check Shipping method in order details
        assert 'Flat Rate Per Item' in order.find_element_by_css_selector('.qview-shipping-destination').text
    except UnexpectedAlertPresentException:
        raise UnexpectedAlertPresentException()


def test_setup_free_shipping(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
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


def test_setup_fedex(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    shipping.navigate_to_shipping()
    shipping.open_country_zone("United States")
    row = shipping.setup_fedex()
    # check shipping method is displayed
    assert "FedEx" in row.text
    # check edit button is enabled
    assert row.find_element_by_css_selector("button")
    #checkout
    # try:
    checkout = CheckoutClass(browser)
    checkout.add_product_to_cart(browser, url)
    if checkout.is_new_checkout_opened():
        orderid = checkout.continue_new_checkout(browser, 'FedEx', checkout.us_shipping_address)
        order = OrderClass(browser)
        checkout.assert_order_for_new_checkout(browser, url, email, password, orderid)
        #check Shipping method in order details
        assert 'FedEx' in order.find_element_by_css_selector('.qview-shipping-destination').text
        return
    checkout.proceed_to_checkout(browser, url, checkout.us_checkout)
    checkout.select_shipping_method_storefront('FedEx')
    checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
    order_id = checkout.get_order_confirmation_number(browser, admin_url)
    order = OrderClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    order.goto_view_orders(browser)
    order.search_order(browser, order_id)
    #check Shipping method in order details
    assert 'FedEx' in order.find_element_by_css_selector('.qview-shipping-destination').text


def test_setup_aupost(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    shipping.navigate_to_shipping()
    shipping.setup_store_location_new(shipping.au_store_location)
    shipping.open_country_zone("United States")
    row = shipping.setup_australia_post()
    # check shipping method is displayed
    assert "Australia Post" in row.text
    checkout = CheckoutClass(browser)
    checkout.add_product_to_cart(browser, url)
    if checkout.is_new_checkout_opened():
        orderid = checkout.continue_new_checkout(browser, 'Australia Post', checkout.us_shipping_address)
        order = OrderClass(browser)
        checkout.assert_order_for_new_checkout(browser, url, email, password, orderid)
        #check Shipping method in order details
        assert 'Australia Post' in order.find_element_by_css_selector('.qview-shipping-destination').text
        return
    checkout.proceed_to_checkout(browser, url, checkout.us_checkout)
    checkout.select_shipping_method_storefront('Australia Post')
    checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
    order_id = checkout.get_order_confirmation_number(browser, admin_url)
    order = OrderClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    order.goto_view_orders(browser)
    order.search_order(browser, order_id)
    #check Shipping method in order details
    assert 'Australia Post' in order.find_element_by_css_selector('.qview-shipping-destination').text
    # except UnexpectedAlertPresentException:
    #     raise UnexpectedAlertPresentException()


@pytest.mark.skipif("True")
def test_setup_canada_post(browser, url, email, password):
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    #set CAD as default currency
    currency = CurrencyClass(browser)
    currency.navigate_to_currency()
    currency.create_currency(browser, currency.CanadianDollar)
    currency.set_as_default(browser, currency.CanadianDollar)
    shipping.navigate_to_shipping()
    shipping.setup_store_location_new(browser, shipping.canada_store_location)
    shipping.open_country_zone("United States")
    row = shipping.setup_canada_post()
    # check shipping method is displayed
    assert "Canada Post" in row.text
    shipping.find('Products').click()
    shipping.find('Search Products').click()
    shipping.find_element_by_css_selector('#searchQuery').send_keys("Donatello")
    shipping.find_element_by_xpath('//button[text()="Search"]').click()
    shipping.find_element_by_css_selector('.is-last a').click()
    shipping.find('product-width').send_keys('1')
    shipping.find('product-height').send_keys('1')
    shipping.find('product-depth').send_keys('1')
    shipping.execute_script("$('button#save-product-button').click()")
    try:
        shipping.find_element_by_xpath('//button[text()="Continue"]').click()
    except:
        pass
    shipping.wait_until_element_present('div.alert-success', "CSS_SELECTOR")
    #checkout
    try:
        checkout = CheckoutClass(browser)
        checkout.add_product_to_cart(browser, url)
        if checkout.is_new_checkout_opened(browser):
            checkout.continue_new_checkout(browser, 'Flat Rate Per Order', checkout.us_shipping_address)
        checkout.proceed_to_checkout(browser, url)
        checkout.select_shipping_method_storefront('Canada Post')
        checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
        order_id = checkout.get_order_confirmation_number(browser, admin_url)
        order = OrderClass(browser)
        admin_url = urlparse.urljoin(url, 'admin')
        browser.get(admin_url)
        order.goto_view_orders(browser)
        order.search_order(browser, order_id)
        #check Shipping method in order details
        assert 'Canada Post' in order.find('.qview-shipping-destination').text
        #Disconnect Canada Post
        shipping.navigate_to_shipping()
        shipping.open_country_zone("United States")
        shipping.open_any_shipping_method("Canada Post")
        shipping.find("Connection").click()
        shipping.find_element_by_xpath("//button[text()='Disconnect']").click()
        assert 'Canada Post' in shipping.find_element_by_css_selector('.real-time-table').text
    except UnexpectedAlertPresentException:
        raise UnexpectedAlertPresentException()


def test_usps(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    shipping.navigate_to_shipping()
    shipping.setup_store_location_new(shipping.us_store_location)
    shipping.add_country_zone(shipping.australia_country_zone)
    shipping.open_country_zone("Australia")
    row = shipping.setup_usps()
    # check shipping method is displayed
    assert "USPS" in row.text
    #Update product dementions
    shipping.find_element_by_link_text('Products').click()
    shipping.find_element_by_link_text('Search Products').click()
    shipping.wait_until_element_present('searchQuery', 'ID').send_keys("Donatello")
    shipping.find_element_by_xpath('//button[text()="Search"]').click()
    shipping.find_element_by_css_selector('.is-last a').click()
    shipping.wait_until_element_present('product-width', 'ID').send_keys('1')
    shipping.find_element_by_id('product-height').send_keys('1')
    shipping.find_element_by_id('product-depth').send_keys('1')
    shipping.execute_script("$('button#save-product-button').click()")
    try:
        shipping.find_element_by_xpath('//button[text()="Continue"]').click()
    except:
        pass
    shipping.wait_until_element_present('div.alert-success', "CSS_SELECTOR")
    #checkout
    try:
        checkout = CheckoutClass(browser)
        checkout.add_product_to_cart(browser, url)
        if checkout.is_new_checkout_opened():
            orderid = checkout.continue_new_checkout(browser, 'USPS', checkout.us_shipping_address)
            order = OrderClass(browser)
            checkout.assert_order_for_new_checkout(browser, url, email, password, orderid)
            #check Shipping method in order details
            assert 'USPS' in order.find_element_by_class_name('qview-shipping-destination').text
            return
        checkout.proceed_to_checkout(browser, url, checkout.au_checkout)
        checkout.select_shipping_method_storefront('USPS')
        checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
        order_id = checkout.get_order_confirmation_number(browser, admin_url)
        order = OrderClass(browser)
        admin_url = urlparse.urljoin(url, 'admin')
        browser.get(admin_url)
        order.goto_view_orders(browser)
        order.search_order(browser, order_id)
        #check Shipping method in order details
        assert 'USPS' in order.find_element_by_css_selector('.qview-shipping-destination').text
    except UnexpectedAlertPresentException:
        raise UnexpectedAlertPresentException()


def test_royal_mail(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    shipping = ShippingClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    shipping.navigate_to_shipping()
    shipping.setup_store_location_new(shipping.uk_store_location)
    shipping.open_country_zone("United States")
    row = shipping.setup_royal_mail()
    # check shipping method is displayed

    assert "Royal Mail" in row.text
    # check edit button is enabled
    assert row.find_element_by_css_selector("button")
    #checkout
    try:
        checkout = CheckoutClass(browser)
        checkout.add_product_to_cart(browser, url)
        if checkout.is_new_checkout_opened():
            orderid = checkout.continue_new_checkout(browser, 'Royal Mail', checkout.us_shipping_address)
            order = OrderClass(browser)
            checkout.assert_order_for_new_checkout(browser, url, email, password, orderid)
            #check Shipping method in order details
            assert 'Royal Mail' in order.find_element_by_css_selector('.qview-shipping-destination').text
            return
        checkout.proceed_to_checkout(browser, url, checkout.us_checkout)
        checkout.select_shipping_method_storefront('Royal Mail')
        checkout.select_payment_option_storefront(browser, 'Cash on Delivery')
        order_id = checkout.get_order_confirmation_number(browser, admin_url)
        order = OrderClass(browser)
        admin_url = urlparse.urljoin(url, 'admin')
        browser.get(admin_url)
        order.goto_view_orders(browser)
        order.search_order(browser, order_id)
        #check Shipping method in order details
        assert 'Royal Mail' in order.find_element_by_class_name('qview-shipping-destination').text
    except UnexpectedAlertPresentException:
        raise UnexpectedAlertPresentException()