from lib.ui_lib import *

class  AccountsClass(CommonMethods):

    def create_user_account(self, browser, username, firstname, lastname, email, password):
        browser.find_element_by_link_text('Users').click()
        element = self.wait_until_element_present('Create a User Account', "LINK")
        element.click()
        element = self.wait_until_element_present('username', "ID")
        element.send_keys(username)
        browser.find_element_by_id('userpass').send_keys(password)
        browser.find_element_by_id('userpass1').send_keys(password)
        browser.find_element_by_id('useremail').send_keys(email)
        browser.find_element_by_id('userfirstname').send_keys(firstname)
        browser.find_element_by_id('userlastname').send_keys(lastname)
        self.select_dropdown_value(browser, 'userrole', 'Sales Staff')
        browser.find_element_by_xpath('//button[text() = "Save"]').click()
        self.verify_and_assert_success_message(browser, "The new user account has been added successfully.", ".alert-success")

    def edit_user_account(self, browser, username, updatedusername):
        browser.execute_script("window.location = $('.tbl-admin').find('tr:contains(" + username + ")').find('.panel-inline').find('li:contains(Edit)').find('a').attr('href')")
        element = self.wait_until_element_present('username', "ID")
        element.clear()
        element.send_keys(updatedusername)
        try:
            self.select_dropdown_value(browser, 'userrole', 'Sales Manager')
        except WebDriverException as e:
            self.select_dropdown_value(browser, 'userrole', 'Sales Manager')
            if "Click succeeded but Load Failed" in e.msg:
                pass
        browser.find_element_by_xpath('//button[text() = "Save"]').click()
        self.verify_and_assert_success_message(browser, "The selected user account has been updated successfully.", ".alert-success")

    def delete_user_account(self, browser, username):
        browser.execute_script("$('.tbl-admin').find('tr:contains(" + username + ")').find('td').find('input').attr('checked','checked')")
        browser.find_element_by_id('IndexDeleteButton').click()
        browser.find_element_by_xpath('//button[text() = "Ok"]').click()
        self.verify_and_assert_success_message(browser, "The selected user accounts have been deleted successfully.", ".alert-success")
