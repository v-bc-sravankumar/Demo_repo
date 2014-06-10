from lib.ui_lib import *

class CustomerGroupClass(CommonMethods):

    def create_customer_group(self, browser, name):
        element = browser.find_element_by_link_text("Customers")
        element.click()
        browser.find_element_by_link_text('Customer Groups').click()
        browser.find_element_by_link_text("Create a Customer Group").click()
        browser.find_element_by_id('groupname').send_keys(name)
        browser.find_element_by_id('discount').clear()
        browser.find_element_by_id('discount').send_keys('1.99')
        browser.find_element_by_xpath('//button[text()="Save"]').click()
        self.verify_and_assert_success_message(browser, "The new customer group has been added successfully.", ".alert-success")
        assert name in browser.find_element_by_id('customers-groups-index').text


    def edit_customer_group(self, browser, name, updatedname):
        self.edit_without_search(browser, name)
        browser.find_element_by_id('groupname').clear()
        browser.find_element_by_id('groupname').send_keys(updatedname)
        browser.find_element_by_xpath('//button[text()="Save"]').click()
        self.verify_and_assert_success_message(browser, "The selected customer group has been updated successfully.", ".alert-success")



    def delete_customer_group(self, browser, name):
        # Delete Group
        browser.execute_script("$('tr:contains(" + name + ") td:nth-child(1) [type=\"checkbox\"]').prop('checked',true)")
        browser.find_element_by_id("IndexDeleteButton").click()
        try:
            self.find_element_by_css_selector('#display-modal .btn-primary').click()
        except NoSuchElementException:
            browser.find_element_by_xpath('//button[text()="Cancel"]').click()
            browser.execute_script("$('tr:contains(" + name + ") td:nth-child(1) [type=\"checkbox\"]').prop('checked',true)")
            browser.find_element_by_id("IndexDeleteButton").click()
            self.find_element_by_css_selector('#display-modal .btn-primary').click()

        self.verify_and_assert_success_message(browser, "The selected customer groups have been deleted successfully.", ".alert-success")
        assert name not in browser.find_element_by_id('customers-groups-index').text
