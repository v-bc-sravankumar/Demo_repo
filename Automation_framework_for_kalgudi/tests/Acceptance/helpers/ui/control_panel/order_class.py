from lib.ui_lib import *

class OrderClass(CommonMethods):

    def get_order_number(self,browser):
        element = self.wait_until_element_present('//div[@class = "alert alert-success"]/p', "XPATH")
        browser_success_msg = element.text
        order_number = browser_success_msg[7:10]
        assert order_number != ''
        return order_number

    def search_order(self,browser,orderID):
        self.wait_until_element_present('keyword-filter', 'ID')
        e = browser.find_element_by_id('keyword-filter')
        e.send_keys(orderID)
        try:
            browser.find_element_by_xpath('//span[@class="responsive-hide"]').click()
        except NoSuchElementException:
            browser.find_element_by_css_selector('.action-divider .filter-button').click()
        except WebDriverException:
            pass

    def get_order_status(self,browser, url, Order_Id):
        browser.get(url)
        self.goto_view_orders(browser)
        self.wait_until_element_present(".order-status #status_"+Order_Id, 'CSS_SELECTOR', time=60)
        return browser.find_element_by_css_selector(".order-status #status_"+Order_Id+" option[selected=true]").get_attribute('value')

    def refund_funds(self,browser, Order_Id):
        # Open order cog
        element = self.wait_until_element_present("//tr[@data-order-id = '" + Order_Id + "']", 'XPATH')
        element = element.find_element_by_class_name('dropdown-trigger')
        element.click()
        # Open Refund modal
        element = self.wait_until_element_present('Refund', 'LINK')
        element.click()
        # Refund transaction
        element = self.wait_until_element_present('//label[@for="refundType_full"]', 'XPATH')
        element.click()
        browser.find_element_by_id('refund-save').click()

    def capture_funds(self,browser, Order_Id):
        # Open order cog
        element = self.wait_until_element_present("//tr[@data-order-id = '" + Order_Id + "']", 'XPATH')
        element = element.find_element_by_class_name('dropdown-trigger')
        element.click()
        # Open capture modal
        element = self.wait_until_element_present('Capture Funds', 'LINK')
        element.click()
        # Process capture
        element = self.wait_until_element_present('#display-modal .dialog-actions .btn-primary', 'CSS_SELECTOR')
        element.click()


    def void_transaction(self,browser, Order_Id):
        # Open order cog
        element = self.wait_until_element_present("//tr[@data-order-id = '" + Order_Id + "']", 'XPATH')
        element = element.find_element_by_class_name('dropdown-trigger')
        element.click()
        # Open capture modal
        element = self.wait_until_element_present('Void Transaction', 'LINK')
        element.click()
        # Process capture
        element = self.wait_until_element_present('#display-modal .dialog-actions .btn-primary', 'CSS_SELECTOR')
        element.click()


    def delete_order(self,browser):

        browser.find_element_by_xpath('//label[@for = "order0"]').click()
        self.select_dropdown_value(browser, 'OrderActionSelect', 'Archive Selected')
        browser.find_element_by_id('action-confirm').click()
        try:
            alert = browser.switch_to_alert()
            alert.accept()
        except WebDriverException:
            browser.execute_script("window.confirm = function(){return true;}");
            browser.find_element_by_id('action-confirm').click()
        #Verify Order delete
        element = self.wait_until_element_present('//div[@class = "alert alert-success"]/p', "XPATH").text
        assert "The selected orders have been deleted successfully." in element

    def goto_view_orders(self,browser):
        self.wait_until_element_present('Orders', 'LINK').click()
        self.wait_until_element_present('View Orders', 'LINK').click()

    def cp_add_order_item(self,browser,name):
        #Add an Item
        element = self.wait_until_element_present('quote-item-search', "ID")
        element.click()
        element.send_keys(name)
        self.wait_until_element_present('//div[@class = "recordContent undefined"]', 'XPATH')
        browser.execute_script("$('#quote-item-search').trigger('keyup')")
        browser.execute_script("$('.recordContent:eq(0)').trigger('click')")
        self.wait_until_element_present('//span[@class = "swatchColour swatchColour_1"]', "XPATH")
        browser.find_element_by_xpath('//span[@class = "swatchColour swatchColour_1"]').click()
        browser.find_element_by_id('dialog-options-submit').click()
        self.wait_until_element_present('//th[@class = "image"]', "XPATH")
        browser.find_element_by_xpath('//button[text() = "Next"]').click()
        self.wait_until_element_present('//label[@for = "shipping-single"]', 'XPATH')

    def cp_select_shipping_payment(self,browser, paymentname):
        browser.find_element_by_xpath('//label[@for = "shipping-single"]').click()
        browser.find_element_by_xpath('//button[text() = "Next"]').click()
        self.wait_until_element_present("//select[@id='paymentMethod']/option[text()='"+paymentname+"']", "XPATH").click()
        self.find_element_by_css_selector('.Field_custom_name input')
        self.execute_script("$('.Field_custom_name input').val('"+paymentname+"');")
        browser.find_element_by_xpath('//button[@class = "btn btn-primary orderMachineSaveButton orderSaveButton"]').click()

    def create_order_controlpanel(self,browser, email, password, firstname, lastname,company,phone,street_add1,street_add2,city,country,state,postcode, invalid_email,invalid_pwd):
        element = self.wait_until_element_present('Orders', "LINK")
        element.click()
        browser.find_element_by_link_text('Add an Order').click()
        element = self.wait_until_element_present('//label[@for = "check-new-customer"]', "XPATH")
        element.click()
        #Validation for Invalid Email
        browser.find_element_by_id('FormField_1').send_keys(invalid_email)
        browser.find_element_by_xpath('//button[text() = "Next"]').click()
        assert "Please enter a valid email address such as joe@example.com" in browser.find_element_by_xpath('//div[@class = "dialog-content"]/p').text
        browser.find_element_by_css_selector('#display-modal .btn-primary').click()
        browser.find_element_by_id('FormField_1').clear()
        browser.find_element_by_id('FormField_1').send_keys(email)
        #Validation for Invalid Password
        browser.find_element_by_id('FormField_2').send_keys(invalid_pwd)
        browser.find_element_by_xpath('//button[text() = "Next"]').click()
        assert "The password and confirmed password do not match." in browser.find_element_by_xpath('//div[@class = "dialog-content"]/p').text
        browser.find_element_by_css_selector('#display-modal .btn-primary').click()
        browser.find_element_by_id('FormField_2').clear()
        browser.find_element_by_id('FormField_2').send_keys(password)
        browser.find_element_by_id('FormField_3').send_keys(password)
        self.select_dropdown_value(browser, 'accountCustomerGroup', '-- Do not assign to any group --')
        browser.find_element_by_id('FormField_4').send_keys(firstname)
        browser.find_element_by_id('FormField_5').send_keys(lastname)
        browser.find_element_by_id('FormField_6').send_keys(company)
        browser.find_element_by_id('FormField_7').send_keys(phone)
        browser.find_element_by_id('FormField_8').send_keys(street_add1)
        browser.find_element_by_id('FormField_9').send_keys(street_add2)
        browser.find_element_by_id('FormField_10').send_keys(city)
        self.select_dropdown_value(browser, 'FormField_11', country)
        self.select_dropdown_value(browser, 'FormField_12', state)
        self.clear_field(browser,'FormField_13')
        browser.find_element_by_id('FormField_13').send_keys(postcode)
        browser.find_element_by_xpath('//button[text() = "Next"]').click()
        #Add an Item
        self.cp_add_order_item(browser,'[Sample] Anna, bright single bangles')
        # Select Shipping address and Payment method
        self.cp_select_shipping_payment(browser, 'Manual Payment')

        # Verify and Assert the success message
        browser_success_msg = self.wait_until_element_present('.alert-success', 'CSS_SELECTOR').text
        order_success_msg="Order #%s has been created successfully." % browser_success_msg[7:10]
        orderID = self.get_order_number(browser)

        assert order_success_msg in browser_success_msg
        return orderID
