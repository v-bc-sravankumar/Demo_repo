from lib.ui_lib import *
from fixtures.account import *

class CustomerClass(CommonMethods, AccountData):

    def __init__(self, browser):
        CommonMethods.__init__(self, browser)
        AccountData.__init__(self)

    def create_customer(self, browser, firstname, lastname, company, email, phone):
        browser.find_element_by_link_text('Customers').click()
        browser.find_element_by_link_text('Add a Customer').click()
        browser.find_element_by_id('custFirstName').send_keys(firstname)
        browser.find_element_by_id('custLastName').send_keys(lastname)
        browser.find_element_by_id('custCompany').send_keys(company)
        browser.find_element_by_id('custEmail').send_keys(email)
        browser.find_element_by_id('custPhone').send_keys(phone)
        browser.find_element_by_id('custStoreCredit').send_keys(10)
        browser.find_element_by_id('custPassword').send_keys('password1')
        browser.find_element_by_id('custPasswordConfirm').send_keys('password1')
        browser.find_element_by_xpath('//button[text()="Save & Exit"]').click()
        return email

    def search_customers(self, browser, firstname, lastname):
        self.wait_until_element_present('Customers', 'LINK').click()
        self.wait_until_element_present('View Customers', 'LINK').click()
        e = self.wait_until_element_present('search-query', 'ID')
        e.clear()
        e.send_keys(firstname + ' ' + lastname)
        try:
            browser.find_element_by_xpath('//span[@class="responsive-hide"]').click()
        except WebDriverException:
            e.send_keys(Keys.RETURN)

    def edit_customer(self, browser, firstname, lastname):
        element = self.wait_until_element_present("Edit", "LINK")
        element.click()
        browser.find_element_by_id('custFirstName').clear()
        browser.find_element_by_id('custFirstName').send_keys(firstname)
        browser.find_element_by_id('custLastName').clear()
        browser.find_element_by_id('custLastName').send_keys(lastname)
        browser.find_element_by_xpath('//button[text()="Save & Exit"]').click()
        self.search_customers(browser, firstname, lastname)
        customer_row = "//td[contains(.,'" + firstname + "')]"
        self.wait_until_element_present(customer_row, "XPATH")
        assert firstname + ' ' + lastname in browser.find_element_by_xpath("//td[contains(.,'" + firstname + "')]").text

    def create_customer_address(self, browser, customer_address):
        try:
            element = self.wait_until_element_present("Edit", "LINK")
            element.click()
        except:
            pass

        browser.find_element_by_link_text('Customer Address Book').click()
        browser.find_element_by_xpath('//button[text()="Add an Address..."]').click()
        for item in customer_address:
                if item == 'Select':
                    self.find_element_by_css_selector(customer_address[item]['Country']['Element']).click()
                    element = self.wait_until_element_present(customer_address[item]['State']['Element'], 'CSS_SELECTOR')
                    element.click()
                else:
                    element=self.find_element_by_id(customer_address[item]['Element'])
                    value=customer_address[item]['Value']
                    element.send_keys(value)

        try:
            browser.find_element_by_xpath('//button[text()="Save & Exit"]').click()
        except Exception:
            browser.find_element_by_css_selector('.btn-primary').click()

        #self.verify_and_assert_success_message(browser, "The customer address has been created successfully.", "#MessageTmpBlock")
        assert customer_address['FirstName']['Value'] + ' ' + customer_address['LastName']['Value'] in browser.find_element_by_xpath("//td[contains(.,'" + customer_address['FirstName']['Value'] + "')]").text

    def edit_customer_address(self, browser):
        element = self.wait_until_element_present("Edit", "LINK")
        element.click()
        browser.find_element_by_id('FormField_8').send_keys('Updated')
        browser.find_element_by_xpath('//button[text()="Save & Exit"]').click()
        self.verify_and_assert_success_message(browser, "The selected customer address has been updated successfully.", "#MessageTmpBlock")

    def delete_customer_address(self, browser):
        # Delete an address
        browser.execute_script("$('tr:contains(\"Updated\") input').first().prop('checked',true);")
        browser.execute_script("window.confirm = function(){return true;}")
        try:
            browser.execute_script("$('.list-horizontal button.btn-icon-large').trigger('click')")
            alert = browser.switch_to_alert()
            alert.accept()
            browser.find_element_by_css_selector('#MessageTmpBlock').text
        except WebDriverException:
            browser.execute_script("confirm()")
        assert "The selected customer address has been updated successfully." in str(self.find_element_by_css_selector('#MessageTmpBlock').text)

        #self.verify_and_assert_success_message(browser, "The selected address book entries have been deleted successfully.", "#MessageTmpBlock")

    def delete_customer(self, browser, firstname, lastname):

        browser.execute_script("$('tr:contains(" + firstname + ") .exportSelectableItem').prop('checked',true);")
        browser.execute_script("$('.list-horizontal button.btn-icon-large').trigger('click')")

        try:
            self.find_element_by_css_selector('#display-modal .btn-primary').click()
        except NoSuchElementException:
            self.wait_until_element_present('//button[text()="Ok"]', 'XPATH').click()

        self.search_customers(browser, firstname, lastname)
        self.verify_and_assert_success_message(browser, "No customers matched your search criteria. Please try again.", "#customers-index")
