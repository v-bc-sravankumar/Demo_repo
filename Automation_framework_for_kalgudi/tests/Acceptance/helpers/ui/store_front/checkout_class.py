from lib.ui_lib import *
from fixtures.checkout import *
from helpers.ui.control_panel.order_class import *

class CheckoutClass(CheckoutData,CommonMethods ):

    def __init__(self, browser):
        CheckoutData.__init__(self)
        CommonMethods.__init__(self, browser)

    def add_product_and_checkout_as_guest(self, browser, url):
        # Navigate to Storefront & Add a Product to Cart
        if 'https://' in url:
            url = url.replace('https://', 'http://')
        print url
        browser.get(urlparse.urljoin(url, 'donatello-brown-leather-handbag-with-shoulder-strap'))
        browser.find_element_by_xpath('//input[contains(@src,"AddCartButton.gif")]').click()
        self.wait_until_element_present('//a[contains(@href,"checkout.php")]', "XPATH")
        browser.find_element_by_xpath('//a[contains(@href,"checkout.php")]').click()
        element = self.wait_until_element_present('checkout_type_guest', "ID")
        element.click()
        # Checkout as Guest
        browser.find_element_by_id('CreateAccountButton').click()
        EMAIL = "virendra.brahmbhatt+" + self.generate_random_string() + "@bigcommerce.com"
        FIRSTNAME = "Viru"
        LASTNAME = "Brahmbhatt"
        COMPANY = "Bigcommerce"
        PHONE = "0111111111"
        # Please dont change Street1 & postcode as its dependent on Global Payment test
        ADDRESS1 = "4 CORPORATE SQ"
        ADDRESS2 = ""
        CITY = "Sydney"
        COUNTRY = "United States"
        STATE = "New York"
        POSTCODE = "10004"
        element = self.wait_until_element_present('FormField_1', "ID")
        element.send_keys(EMAIL)
        browser.find_element_by_id('FormField_4').send_keys(FIRSTNAME)
        browser.find_element_by_id('FormField_5').send_keys(LASTNAME)
        browser.find_element_by_id('FormField_6').send_keys(COMPANY)
        browser.find_element_by_id('FormField_7').send_keys(PHONE)
        browser.find_element_by_id('FormField_8').send_keys(ADDRESS1)
        browser.find_element_by_id('FormField_9').send_keys(ADDRESS2)
        browser.find_element_by_id('FormField_10').send_keys(CITY)
        self.select_dropdown_value(browser, 'FormField_11', 'United States')
        time.sleep(2)
        self.select_dropdown_value(browser, 'FormField_12', 'New York')
        browser.find_element_by_id('FormField_13').send_keys(POSTCODE)
        browser.find_element_by_css_selector('.Submit .billingButton').click()
        element = self.wait_until_element_present('//label[contains(.,"Flat Rate Per Order")]', "XPATH")
        element.click()
        element = self.wait_until_element_present('.ML20 input', "CSS_SELECTOR")
        element.click()
        element = self.wait_until_element_present('bottom_payment_button', "ID")
        element.click()

    def add_product_to_cart(self, browser, url):
        # Navigate to Storefront & Add a Product to Cart
        browser.implicitly_wait(5)
        if 'https://' in url:
            url = url.replace('https://', 'http://')
        print url
        browser.get(urlparse.urljoin(url, '/donatello-brown-leather-handbag-with-shoulder-strap'))
        element = self.wait_until_element_present('add-to-cart', "CLASS_NAME")
        element.click()
        # browser.find_element_by_css_selector('.add-to-cart').click()
        element=self.wait_until_element_present('.ProceedToCheckout a', "CSS_SELECTOR")
        element.click()

    def proceed_to_checkout(self, browser, url, country_data=None):
        self.wait_until_element_present('uniform-checkout_type_guest', "ID")
        self.find_element_by_id('checkout_type_guest').click()
        # Checkout as Guest
        browser.find_element_by_id('CreateAccountButton').click()
        EMAIL = "test.engineer+" + self.generate_random_string() + "@bigcommerce.com"
        element = self.wait_until_element_present('FormField_1', "ID")
        element.send_keys(EMAIL)
        if country_data==None:
            country_data=self.us_checkout

        for item in country_data:
            if item=="Country" or item=="State":
                self.find_element_by_css_selector(country_data['Country']['Element']).click()
                time.sleep(3) #intentinaly put sleep here as wait_untill_element_present is not useful
                self.wait_until_element_present("//select[@id='FormField_12']/option[contains(.,'"+country_data['State']['Value']+"')]", "XPATH")
                #self.select_dropdown_value(browser, 'FormField_12', country_data['State']['Value'])
                Select(browser.find_element_by_id('FormField_12')).select_by_visible_text(country_data['State']['Value'])
            else:
                element=country_data[item]['Element']
                value=country_data[item]['Value']
                self.wait_until_element_present(element, "ID").is_enabled()
                self.find_element_by_id(element).clear()
                self.find_element_by_id(element).send_keys(value)
            
        browser.find_element_by_css_selector('.AddBillingAddress input.billingButton').click()
        self.wait_until_element_invisible('.AddBillingAddress input.billingButton', 'CSS_SELECTOR')

    def select_shipping_method_storefront(self, shipping_method):
        try:
            element = self.wait_until_element_present("//label[contains(.,'"+shipping_method+"')]", "XPATH", time=180)
            element.click()
        except:
            raise
        element = self.wait_until_element_present('#CheckoutStepShippingProvider input.btn', "CSS_SELECTOR")
        element.click()

    def select_payment_option_storefront(self, browser, payment_option):
        if self.element_exists('//input[@value = "Proceed to Payment"]', browser, "XPATH"):
            try:
                element=self.find_element_by_xpath("(//div[@id='provider_list']/label[text()='"+payment_option+"']/preceding-sibling::div)[last()]/descendant::input")
                element.click()
            except ElementNotVisibleException:
                raise

        element = self.wait_until_element_present('bottom_payment_button', "ID", time=300)
        element.click()

    def enter_credit_card(self, browser, card_type, cardholder_name, card_number, expiry_month, expiry_year, ccv):
        self.wait_until_element_present("//input[contains(@value,'Pay for Order')]", "XPATH")
        try:
            Select(browser.find_element_by_id('creditcard_cctype')).select_by_visible_text(card_type)
            element = self.find_element_by_id('creditcard_name')
            element.clear()
            element.send_keys(cardholder_name)
            self.wait_until_element_present('creditcard_ccno', "ID", browser).send_keys(card_number)
            Select(browser.find_element_by_id('creditcard_ccexpm')).select_by_visible_text(expiry_month)
            Select(browser.find_element_by_id('creditcard_ccexpy')).select_by_visible_text(expiry_year)
            browser.find_element_by_name('creditcard_cccvd').send_keys(ccv)
        except:
            self.find_element_by_xpath("//input[contains(@name,'_name')]").clear()
            self.find_element_by_xpath("//input[contains(@name,'_name')]").send_keys(cardholder_name)
            self.find_element_by_xpath("//input[contains(@name,'_ccno')]").send_keys(card_number)
            self.wait_until_element_present("//select/option[text()='" + expiry_month + "']", "XPATH").click()
            self.wait_until_element_present("//select/option[text()='" + expiry_year + "']", "XPATH").click()
            
        browser.find_element_by_xpath("//input[contains(@value,'Pay for Order')]").click()

    def get_order_confirmation_number(self, browser, url):
        try:
            element = self.wait_until_element_present('NotifyMessage a', "CSS_SELECTOR")
            Order_Id = element.text
        except TimeoutException:
            orderlink = browser.find_element_by_xpath('//a[contains(@href, "view_order&order_id=")]').get_attribute('href')
            Order_Id = re.search(r'([_id=]*)id=.*', orderlink).group().replace('_id=', "")

        assert Order_Id != ''
        # assert "your order number is: ".upper() + Order_Id in browser.find_element_by_css_selector('p.order-number').text.upper()
        return Order_Id

    # New Checkout
    def is_new_checkout_opened(self):
        try:
            # move into the iframe
            iframe = self.wait_until_element_present("//iframe[contains(@id, 'easyXDM_default')]", 'XPATH', time =10)
            self.switch_to_frame(iframe)
            assert "Secure Checkout" in str(self.find_element_by_class_name('co-header--title').text)
            return True

        except:
            return False

    def account_details(self, account, email=None):
        element=self.wait_until_element_present("//nav/descendant::li/a[text()='Email']", 'XPATH')
        #element.click()

        try:
            if email!=None:
                element=self.find_element_by_id(account['Username']['Element'])
                element.clear()
                element.send_keys(email)
                self.find_element_by_id(account['Password']['Element']).send_keys(account['Password']['Value'])
            else:
                element=self.find_element_by_id('account-details-email')
                element.clear()
                element.send_keys(account['Username']['Value'])
                self.find_element_by_id(account['Password']['Element']).send_keys(account['Password']['Value'])
        except:
            pass

        self.find_element_by_id('continue-button').click()
        try:
            assert self.find_element_by_id('account-details-email')
        except:
            assert self.find_element_by_css_selector('.co-existing-address-form')

    def add_credit_card_new(self, payment_details):
        #self.find_element_by_id("continue-button").click()
        #assert "Please fill in all required fields correctly." in str(self.find("div[ng-bind='formError']").text)
        #self.find_element_by_id('payment-provider-name').send_keys('Authorize.net')
        try:
            for item in payment_details:
                if item=="Select":
                    for subitem in payment_details[item]:
                        element=self.find_element_by_css_selector(payment_details[item][subitem])
                        element.click()
                else:
                    element_name=payment_details[item]['Element']
                    value=payment_details[item]['Value']
                    element = self.find_element_by_name(element_name)
                    element.clear()
                    element.send_keys(value)

            self.find_element_by_id('continue-button').click()

        except NoSuchElementException:
            raise

    def add_credit_card_new(self, payment_details, payment_name="Authorize.net"):
        #self.find_element_by_id("continue-button").click()
        #assert "Please fill in all required fields correctly." in str(self.find("div[ng-bind='formError']").text)
        #self.find_element_by_id('payment-provider-name').send_keys('Authorize.net')
        try:
            for item in payment_details:
                if item=="Select":
                    for subitem in payment_details[item]:
                        if subitem=="PaymentMethod":
                            try:
                                self.select_dropdown_value_by_css(self.browser, payment_details[item][subitem], payment_name)
                            except NoSuchElementException:
                                pass
                            except TimeoutException:
                                pass
                        else:
                            element=self.find_element_by_css_selector(payment_details[item][subitem])
                            element.click()
                else:
                    element_name=payment_details[item]['Element']
                    value=payment_details[item]['Value']
                    element = self.find_element_by_name(element_name)
                    element.clear()
                    element.send_keys(value)

            self.find_element_by_id('continue-button').click()

        except NoSuchElementException:
            raise

    # Shipping Billing Addresses
    def add_ship_bill_address(self, address):
        #self.find("continue-button").click()
        #assert "Please fill in all required fields correctly." in str(self.find("div[ng-bind='formError']").text)
        try:
            for item in address:
                if item == 'Select':
                    self.find_element_by_css_selector(address[item]['Country']['Element']).click()
                    element=self.wait_until_element_present("//select/option[text()='"+address[item]['State']['Value']+"']", "XPATH")
                    element.click()
                else:
                    element = self.find_element_by_css_selector(address[item]['Element'])
                    element.clear()
                    element.send_keys(address[item]['Value'])
        except NoSuchElementException:
            return False
        try:
            if not self.find_element_by_id("use-billling-address").is_selected():
                self.find_element_by_id("use-billling-address").click()
            pass
        except:
            pass
        self.find_element_by_id("continue-button").click()

    def select_existing_address(self, browser, address):
        assert self.find_element_by_css_selector('.bui-icon-add')
        self.find_element_by_xpath("//div[contains(.,'Add new address')]/span").click()
        assert "Use my existing addresses" in str(self.find_element_by_css_selector('.co-main--layout').text)
        self.find('Use my existing addresses').click()
        element=self.wait_until_element_present(".co-existing-address-form", "CSS_SELECTOR")
        #assert valid address is displayed
        assert str(element.text).find(address['City']['Value'])
        assert str(element.text).find(address['Select']['State']['Value'])
        assert str(element.text).find(address['Select']['Country']['Value'])
        assert str(element.text).find(address['Address1']['Value'])
        assert str(element.text).find(address['Postcode']['Value'])

        self.execute_script("$('.co-selectable-panel:contains(\""+address['Select']['Country']['Value']+"\") span').click()")
        assert 'Selected' in str(self.find_element_by_css_selector('.co-actionable-animate--top').text)

        try:
            if not self.find("use-billling-address").is_selected():
                self.find("use-billling-address").click()
            pass
        except:
            pass
        self.find("continue-button").click()
        time.sleep(5)


    def select_shipping_method_new(self,  shipping_name):
        try:
            self.find('co-side-shipping_quote')
            self.wait_until_element_present("co-side-shipping_quote", 'ID')
            self.wait_until_element_present("//select[@id='co-side-shipping_quote']/option[contains(.,'"+shipping_name+"')]", 'XPATH').click()
        except NoSuchElementException:
            raise

    def check_confirmation(self, account_details, shipping_details, billing_details, card_details):
        try:
            element=self.wait_until_element_present('.co-step-confirmation--email', "CSS_SELECTOR", time=60)
            assert account_details['Username']['Value'] in str(element.text)
            #verify shipping details
            #assert shipping_details['Name']['Value'] in str(self.find("div[co-step-confirmation-address='shippingAddress']").text)
            assert str(self.find("div[formatted-address='shippingAddress']").text).find(shipping_details['Address1']['Value'])
            assert str(self.find("div[formatted-address='shippingAddress']").text).find(shipping_details['Suburb']['Value'])
            assert str(self.find("div[formatted-address='shippingAddress']").text).find(shipping_details['Select']['Country']['Value'])
            assert str(self.find("div[formatted-address='shippingAddress']").text).find(shipping_details['Select']['State']['Value'])
            assert str(self.find("div[formatted-address='shippingAddress']").text).find(shipping_details['Zip']['Value'])

            #verify billing details
            #assert billing_details['Name']['Value'] in str(self.find("div[co-step-confirmation-address='billingAddress']").text)
            assert str(self.find("div[formatted-address='billingAddress']").text).find(billing_details['Address1']['Value'])
            assert str(self.find("div[formatted-address='billingAddress']").text).find(billing_details['Suburb']['Value'])
            assert str(self.find("div[formatted-address='billingAddress']").text).find(billing_details['Select']['Country']['Value'])
            assert str(self.find("div[formatted-address='billingAddress']").text).find(billing_details['Select']['State']['Value'])
            assert str(self.find("div[formatted-address='billingAddress']").text).find(billing_details['Zip']['Value'])


            assert str(self.find_element_by_css_selector('.co-step-confirmation--payment').text).find(card_details['CardName']['Value'])

        except NoSuchElementException:
            raise

    def pay_for_order(self):
        try:
            self.find_element_by_css_selector('#pay-button').click()
            self.wait_until_element_invisible('.co-step-confirmation--email', "CSS_SELECTOR")
            self.wait_until_element_present('test-order-number', "ID", time=60).is_displayed()
            assert self.wait_until_element_present('.co-step-success', "CSS_SELECTOR", time=60)
            orderID = str(self.find_element_by_id('test-order-number').text)
            return orderID
        except NoSuchElementException:
            raise
        except:
            raise

    def create_new_account(self, account_details):
        try:
            self.find_element_by_css_selector('#password').send_keys(account_details['Password']['Value'])
            self.find_element_by_css_selector('#create-account-button').click()
            assert "Your account has been created." in str(self.find_element_by_css_selector('.co-step-success--account-success').text)
        except NoSuchElementException:
            raise

    def check_order_total(self):
        try:
            orderamount = float(str(self.find_element_by_css_selector('#order-item-amount').text[1:]))
            ordercoupon= float(str(self.find_element_by_id('order-coupon-amount').text[2:]))
            ordershipping = float(str(self.find_element_by_css_selector('#order-shipping-amount').text[1:]))
            ordertax = float(str(self.find_element_by_css_selector('#order-tax-amount').text[1:]))
            ordergrandtotal = float(str(self.find_element_by_css_selector('#order-grand-total').text[1:]))
            #assert ordergrandtotal== (orderamount + ordertax + ordershipping)
            assert ordergrandtotal== ((orderamount+ordertax+ordershipping)-ordercoupon)
        except NoSuchElementException:
            raise

    def check_payment_failure(self,browser):
        browser.find_element_by_id('pay-button').click()
        #element=self.find_element_by_xpath('.co-step-confirmation .co-form-error--body')
        #element=self.wait_until_element_present('confirmationForm', "NAME").text
        #print element
        error=self.wait_until_element_present('.co-main--layout .co-form-error--body', "CSS_SELECTOR")
        print error
        assert error

    def continue_new_checkout(self, browser, shipping, shipping_address, payment='Authorize.net'):
        #self.account_details(self.account_details_new)
        self.add_ship_bill_address(shipping_address)
        self.wait_until_element_present("//nav/descendant::li[contains(@class, 'is-active')]/a[text()='Billing']", "XPATH")
        self.select_shipping_method_new(shipping)
        self.add_ship_bill_address(self.us_billing_address)
        self.add_credit_card_new(self.visa_card)
        self.check_order_total()
        self.check_confirmation(self.account_details_new, shipping_address, self.us_billing_address, self.visa_card)
        # get order id
        orderID = self.pay_for_order()
        return orderID

    def assert_order_for_new_checkout(self, browser, url, email, password, orderID):
        order = OrderClass(browser)
        self.go_to_admin(browser, url, email, password)
        browser.get(urlparse.urljoin(url, '/admin/index.php?ToDo=viewOrders'))
        order.search_order(browser, orderID)


    def edit_cart_negative(self):
        self.find_element_by_class_name('co-side--changeitems').click()
        self.find_element_by_class_name('bui-icon-trash').click()
        try:
            assert self.find_element_by_class_name('co-form-error--body')
        except ElementNotVisibleException:
            raise
        self.execute_script("$('.co-side--finishchangeitems').click()")

    def add_gift_cert_negative(self):
        self.find_element_by_class_name("co-side--apply-coupon-link").click()
        self.find_element_by_class_name("co-side--discount-input").send_keys("INVALID")
        self.find_element_by_class_name("co-side--discount-button").click()
        try:
            assert self.find_element_by_class_name("co-form-error--body")
        except ElementNotVisibleException:
            raise
        self.find_element_by_class_name("co-side--discount-input").clear()
        self.find_element_by_class_name("co-side--discount-button").click()

    def search_coupon_code(self, browser, url):
        if 'https://' in url:
            url = url.replace('https://', 'http://')
        print url
        browser.get(urlparse.urljoin(url, '/admin/index.php?ToDo=editCoupon&couponId=1'))
        couponcode = browser.find_element_by_id('couponcode').get_attribute('value')
        return couponcode

    def add_coupon_code(self, couponcode):
        self.find_element_by_class_name("co-side--apply-coupon-link").click()
        self.find_element_by_class_name("co-side--discount-input").send_keys(couponcode)
        self.find_element_by_class_name("co-side--discount-button").click()
        try:
            assert self.find_element_by_id('order-coupon-amount')
        except ElementNotVisibleException:
            raise

    def navigate_to_checkout_setup(self):
        self.find_element_by_link_text("Setup & Tools").click()
        self.find_element_by_link_text('Checkout').click()

