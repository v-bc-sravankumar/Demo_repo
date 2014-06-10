from fixtures.payment import *
from lib.ui_lib import *

class PaymentClass(PaymentCredentials, CommonMethods):

    # Checkout / Payments methods
    def navigate_to_payment_setting(self):
        self.find_element_by_link_text('Setup & Tools').click()
        self.find_element_by_link_text('Payments').click()
        element = self.wait_until_element_present('summary', 'TAGNAME')
        element.click()

    def set_any_payment(self, paymentname):
        if not self.element_exists(paymentname, search_by='LINK'):
            try:
                self.wait_until_element_present("//summary/i[@class='icon-chevron-down icon']", "XPATH", time=5)

            except:
                element = self.find_element_by_tag_name('summary')
                element.click()

            element = self.wait_until_element_present('//label[text()="' + paymentname + '"]', "XPATH")
            element.click()
            self.find_element_by_xpath('//input[@value = "Save"]').click()
            #self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully. If you enabled a new payment provider you can configure it by clicking its tab below.', ".alert-success")
            self.find_element_by_link_text(paymentname).click()
        else:
            self.find_element_by_link_text(paymentname).click()

    def turn_off_payment(self, browser, paymentname):
        if self.element_exists(paymentname, browser, 'LINK'):
            try:
                element = browser.find_element_by_tag_name('summary')
                element.click()                
            except:
                element = self.wait_until_element_present("//summary/i[@class='icon-chevron-down icon']", "XPATH", time=5)
                
            element = self.wait_until_element_present('//label[text()="' + paymentname + '"]', "XPATH")
            element.click()
            browser.find_element_by_xpath('//input[@value = "Save"]').click()
            self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")

    def set_eway_au_payment(self, browser):
        self.set_any_payment('eWay Australia')
        for val in self.eway_au_credentials:
            self.enter_text(val[3], val[1], browser, val[2])
        # Enter Display name
        browser.find_element_by_id('checkout_eway_displayname').clear()
        browser.find_element_by_id('checkout_eway_displayname').send_keys('eWay Australia')
        # Select test mode
        self.select_dropdown_value(browser, 'checkout_eway_testmode', 'Yes')
        # Save
        browser.find_element_by_css_selector('.SaveButton').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")

    def set_simplify_payment(self, browser, paymentname):
        self.set_any_payment('Simplify Commerce')
        # click Connect btn
        browser.find_element_by_id('simplify-connect').click()
        # enter connection details
        if self.element_exists('.existingAccountBtn', browser, 'CSS_SELECTOR'):
            browser.find_element_by_css_selector('.existingAccountBtn').click()
        for val in self.simplify_credentials:
            self.enter_text(val[3], val[1], browser, val[2])
        # press Login btn
        browser.find_element_by_id('button-login').click()

        self.verify_and_assert_success_message(browser, 'Your Simplify account will be automatically linked with BIGCOMMERCE full access rights.', ".pad-top p")
        # Click Link My account btn
        browser.find_element_by_css_selector('.large.button-primary.button.right').click()
        time.sleep(2)
        assert self.wait_until_element_present('span.toggle-label.is-active', 'CSS_SELECTOR').text == 'Live'
        # swich to test mode
        browser.find_element_by_xpath("//label[@for='checkout_simplify_testmode']").click()
        browser.find_element_by_css_selector('a.btn.btn-primary').click()

        if self.element_exists('.existingAccountBtn', browser, 'CSS_SELECTOR'):
            browser.find_element_by_css_selector('.existingAccountBtn').click()
        for val in self.simplify_credentials:
            self.enter_text(val[3], val[1], browser, val[2])
        # press Login btn
        browser.find_element_by_id('button-login').click()
        self.verify_and_assert_success_message(browser, 'Your Simplify account will be automatically linked with BIGCOMMERCE full access rights.', ".pad-top p")
        # Click Link My account btn
        browser.find_element_by_css_selector('.large.button-primary.button.right').click()

        self.wait_until_element_present('.toggle-label', 'CSS_SELECTOR')
        assert browser.find_element_by_css_selector('.toggle-label.is-active').text == 'Test mode'
        # Enter Display name
        browser.find_element_by_id('checkout_simplify_displayname').clear()
        browser.find_element_by_id('checkout_simplify_displayname').send_keys(paymentname)
        # Save
        browser.find_element_by_css_selector('.SaveButton').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")

    def set_braintree_payment(self, browser, transactiontype='Authorize & Capture'):

        self.set_any_payment('Braintree')
        for val in self.braintree_credentials:
            self.enter_text(val[3], val[1], browser, val[2])
        # Enter Display name
        browser.find_element_by_id('checkout_braintree_displayname').clear()
        browser.find_element_by_id('checkout_braintree_displayname').send_keys('Braintree')
        # Set transaction type
        self.select_dropdown_value(browser, 'checkout_braintree_transaction_type', transactiontype)
        # Switch to test mode
        browser.find_element_by_xpath("//label[@for='checkout_braintree_testmode']").click()
        time.sleep(2)        
        try:
            assert self.wait_until_element_present('span.toggle-label.is-active', 'CSS_SELECTOR').text == 'Test mode'
        except:
            self.wait_until_element_present("//label[@for='checkout_braintree_testmode']", 'XPATH', time=10).click()
        # Save
        browser.find_element_by_css_selector('.SaveButton').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")

    def set_global_payments(self, browser, transaction_type='Sale'):
        if not self.element_exists('Global Payments', browser, 'LINK'):
            self.wait_until_element_present('//label[@for = "ISSelectcheckoutproviders_checkout_globalpayments_input"]',
                                            "XPATH", browser)
            browser.find_element_by_xpath('//label[@for = "ISSelectcheckoutproviders_checkout_globalpayments_input"]').click()
            browser.find_element_by_css_selector('.SaveButton').click()
            self.verify_and_assert_success_message(browser, "The modified payment settings have been saved successfully. If you enabled a new payment provider you can configure it by clicking its tab below.", ".alert-success")

        browser.find_element_by_link_text('Global Payments').click()
        browser.find_element_by_id('checkout_globalpayments_displayname').clear()
        browser.find_element_by_id('checkout_globalpayments_displayname').send_keys('Global Payments')
        browser.find_element_by_id('checkout_globalpayments_username').clear()
        browser.find_element_by_id('checkout_globalpayments_username').send_keys('bigc7659')
        browser.find_element_by_id('checkout_globalpayments_password').clear()
        browser.find_element_by_id('checkout_globalpayments_password').send_keys('Beyond40k')
        self.select_dropdown_value(browser, 'checkout_globalpayments_transaction_type', transaction_type)
        browser.find_element_by_css_selector('.SaveButton').click()
        self.verify_and_assert_success_message(browser, "The modified payment settings have been saved successfully.", ".alert-success")

    def set_securepay_payments(self, browser):
        if not self.element_exists('SecurePay', browser, 'LINK'):
            if not self.element_exists('tab1', browser, 'ID'):
                element = self.wait_until_element_present('summary', "TAGNAME")
                element.click()
            self.wait_until_element_present('icon-chevron-right', "CLASS_NAME").click()
            self.wait_until_element_present('ISSelectcheckoutproviders_checkout_securepay', 'ID').click()
            browser.find_element_by_xpath('//input[@value = "Save"]').click()
            self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully. If you enabled a new payment provider you can configure it by clicking its tab below.', ".alert-success")

        self.wait_until_element_present('SecurePay', 'LINK').click()
        element = self.wait_until_element_present('checkout_securepay_merchantid', 'ID')
        element.clear()
        element.send_keys('ABC0001')
        element = browser.find_element_by_id('checkout_securepay_password')
        element.clear()
        element.send_keys('abc123')
        self.select_dropdown_value(browser, 'checkout_securepay_testmode', 'Yes')
        browser.find_element_by_xpath('//input[@value = "Save"]').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")

    def set_authorize_net_payment(self, browser,transactiontype):
        self.set_any_payment('Authorize.net')
        for val in self.authorize_net_credentials:
            self.enter_text(val[3], val[1], browser, val[2])
        # Enter Display name
        browser.find_element_by_link_text('Authorize.net').click()
        browser.find_element_by_id('checkout_authorizenet_displayname').clear()
        browser.find_element_by_id('checkout_authorizenet_displayname').send_keys('Authorize.net')
        # Set transaction type
        self.select_dropdown_value(browser, 'checkout_authorizenet_transactiontype', transactiontype)
        # Switch to test mode
        self.select_dropdown_value(browser, 'checkout_authorizenet_testmode', 'Yes')
        # Save
        browser.find_element_by_xpath('//input[@value = "Save"]').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")

    def set_cash_on_delivery_payments(self, browser):
        self.set_any_payment('Cash on Delivery')
        # Save
        browser.find_element_by_css_selector('.SaveButton').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")

    def set_qbms_payment(self, browser):
        self.set_any_payment('Quick Books Merchant Services')
        for val in self.qbms_credentials:
            self.enter_text(val[3], val[1], browser, val[2])
        # Enter Display name
        browser.find_element_by_id('checkout_qbms_displayname').clear()
        browser.find_element_by_id('checkout_qbms_displayname').send_keys('Quick Books Merchant Services')
        # Select test mode
        self.select_dropdown_value(browser, 'checkout_qbms_testmode', 'Yes')
        # Save
        browser.find_element_by_css_selector('.SaveButton').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success") 

    def set_payleap_payment(self, browser,transtype):
        self.set_any_payment('PayLeap')
        for val in self.payleap_credentials:
            self.enter_text(val[3], val[1], browser, val[2])
        # Enter Display name
        browser.find_element_by_id('checkout_payleap_displayname').clear()
        browser.find_element_by_id('checkout_payleap_displayname').send_keys('PayLeap')
            #Set transaction type
        self.select_dropdown_value(browser,'checkout_payleap_transtype', transtype)
        # Select test mode
        self.select_dropdown_value(browser, 'checkout_payleap_testmode', 'Yes (Development Account)')
        # Save
        browser.find_element_by_css_selector('.SaveButton').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")  

    def set_hps_payment(self, browser,transtype):
        browser.find_element_by_link_text('Heartland Payment Systems').click()
        for val in self.hps_credentials:
             self.enter_text(val[3], val[1], browser, val[2])
        # Enter Display name
        browser.find_element_by_id('checkout_hps_displayname').clear()
        browser.find_element_by_id('checkout_hps_displayname').send_keys('Heartland Payment Systems')
        # Select test mode
        self.select_dropdown_value(browser, 'checkout_hps_testmode', 'Yes, turn on Test Mode')
        #Set transaction type
        self.select_dropdown_value(browser,'checkout_hps_hps_transaction_type', 'Yes, require credit card codes')
        #Set require card code
        self.select_dropdown_value(browser,'checkout_hps_avs_check', 'Approve if Credit Card Issuer approves transaction') 
        # Save
        browser.find_element_by_css_selector('.SaveButton').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")


    def set_authorize_net_payment_faulty(self,browser, value): #Running faulty Authorize.Net scenario
        browser.find_element_by_link_text('Authorize.net').click()

        self.find_element_by_id('checkout_authorizenet_merchantid').clear()
        self.find_element_by_id('checkout_authorizenet_merchantid').send_keys(value)
        self.find_element_by_xpath('//input[@value = "Save"]').click()
        self.verify_and_assert_success_message(browser, 'The modified payment settings have been saved successfully.', ".alert-success")





