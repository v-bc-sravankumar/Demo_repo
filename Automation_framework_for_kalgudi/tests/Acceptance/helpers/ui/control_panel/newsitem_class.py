from lib.ui_lib import *

class NewsItemClass(CommonMethods):

    def create_newsitem(self, browser, name):
        browser.find_element_by_link_text('Web Content').click()
        browser.find_element_by_link_text('Add a News Item').click()
        browser.find_element_by_id('newstitle').clear()
        browser.find_element_by_id('newstitle').send_keys(name)
        browser.find_element_by_id('newstitle').click()
        browser.find_element_by_id('news_custom_url').click()
        browser.find_element_by_id('newssearchkeywords').send_keys("NEWS")
        browser.find_element_by_xpath('//button[text()="Save"]').click()
        self.verify_and_assert_success_message(browser, "The news item has been added successfully.", ".alert-success")

    def edit_newsitem(self, browser, name, updatedname):
        self.edit_without_search(browser, name)
        browser.find_element_by_id('newstitle').clear()
        browser.find_element_by_id('newstitle').send_keys(updatedname)
        browser.find_element_by_id('customUrlGenerateButton').click()
        browser.find_element_by_id('news_custom_url').clear()
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_id('news_custom_url').get_attribute('value').replace('/', '') == s.find_element_by_id('newstitle').get_attribute('value').lower())
        browser.find_element_by_xpath('//button[text()="Save"]').click()
        self.verify_and_assert_success_message(browser, "The selected news item has been updated successfully.", ".alert-success")


    def delete_newsitem(self, browser, name):
        browser.find_element_by_link_text('Web Content').click()
        browser.find_element_by_link_text('View News Items').click()
        browser.execute_script("$('tr:contains(" + name + ") td:nth-child(1) [type=\"checkbox\"]').prop('checked',true)")
        browser.execute_script("$('.list-horizontal button#IndexDeleteButton').click();")
        browser.find_element_by_xpath('//button[text()="Ok"]').click()
        self.verify_and_assert_success_message(browser, "The selected news items have been deleted successfully.", ".alert-success")
        assert browser.execute_script("return $('td:contains(" + name + ")').text()") == ""
