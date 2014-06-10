from lib.ui_lib import *

class WebpageClass(CommonMethods):

    def create_webpage(self, browser, name):
        # Add Web Page
        browser.find_element_by_link_text('Web Content').click()
        browser.find_element_by_link_text('Web Pages').click()
        browser.find_element_by_link_text('Create a Web Page').click()
        element = self.wait_until_element_present('pagetitle', 'ID')
        element.clear()
        element.send_keys(name)
        element.click()
        browser.find_element_by_id('page_custom_url').click()
        browser.find_element_by_id('pagemetatitle').send_keys("Contact")
        browser.find_element_by_id('pagesearchkeywords').send_keys("Contact")
        browser.find_element_by_xpath('//button[text()="Save & Exit"]').click()
        self.verify_and_assert_success_message(browser, "The new page has been added successfully.", ".alert-success")


    def edit_webpage(self, browser, name, updatedname):
        self.edit_without_search(browser, name)
        self.find('pagetitle').clear()
        browser.find_element_by_id('pagetitle').send_keys(updatedname)
        browser.find_element_by_link_text('Reset').click()
        browser.find_element_by_xpath('//button[text()="Save & Exit"]').click()
        self.verify_and_assert_success_message(browser, "The selected page has been updated successfully.", ".alert-success")

    def delete_webpage(self, browser, name):
        browser.find_element_by_link_text('Web Content').click()
        browser.find_element_by_link_text('Web Pages').click()
        # Delete Web page
        browser.execute_script("$('tr:contains(" + name + ") td:nth-child(1) [type=\"checkbox\"]').prop('checked',true)")
        browser.find_element_by_id('IndexDeleteButton').click()
        self.find_element_by_css_selector('#display-modal .btn-primary').click()
        self.verify_and_assert_success_message(browser, "The selected pages have been deleted successfully.", ".alert-success")
        assert browser.execute_script("return $('td:contains(" + name + ")').text()") == ""
