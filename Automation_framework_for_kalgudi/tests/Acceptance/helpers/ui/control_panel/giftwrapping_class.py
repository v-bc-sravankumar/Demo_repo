from lib.ui_lib import *

class GiftWrappingClass(CommonMethods):

    def create_giftwrapping(self, browser, name):
        browser.find_element_by_link_text('Setup & Tools').click()
        browser.find_element_by_link_text('Gift wrapping').click()
        browser.find_element_by_id('IndexCreateButton').click()
        browser.find_element_by_id('wrapname').send_keys(name)
        browser.find_element_by_id('wrapprice').send_keys('10')
        browser.find_element_by_name('SubmitButton1').click()
        self.verify_and_assert_success_message(browser, "The gift wrapping option has been created successfully.", ".alert-success")

    def edit_giftwrapping(self, browser, name, updatedname):
        browser.execute_script("window.location=$('.tbl-admin').find('tr:contains(" + name + ")').find('.panel-inline').find('li:contains(Edit)').find('a').attr('href')")
        browser.find_element_by_id('wrapname').clear()
        browser.find_element_by_id('wrapname').send_keys(updatedname)
        browser.find_element_by_id('wrapprice').clear()
        browser.find_element_by_id('wrapprice').send_keys('20')
        try:
            browser.find_element_by_name('SubmitButton1').click()
        except WebDriverException as e:
            if "Click succeeded but Load Failed" in e.msg:
                pass
        self.verify_and_assert_success_message(browser, "The selected gift wrapping option has been updated successfully.", ".alert-success")

    def deleted_giftwrapping(self, browser, name):
        browser.find_element_by_link_text('Setup & Tools').click()
        browser.find_element_by_link_text('Gift wrapping').click()
        browser.execute_script("$('tr:contains(" + name + ") td:nth-child(1) [type=\"checkbox\"]').prop('checked',true)")
        browser.find_element_by_id('IndexDeleteButton').click()
        try:
            alert = browser.switch_to_alert()
            alert.accept()
        except WebDriverException:
            browser.execute_script("window.confirm = function(){return true;}");
            browser.find_element_by_id('IndexDeleteButton').click()
        try:
            self.verify_and_assert_success_message(browser, "Your store does not have any gift wrapping options configured. Click 'Add a New Gift Wrapping Option' to create one.", ".alert-success")
        except :
            self.verify_and_assert_success_message(browser, "The selected gift wrapping options have been deleted successfully.", ".alert-success")
