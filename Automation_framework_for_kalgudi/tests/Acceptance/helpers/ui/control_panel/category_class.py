from lib.ui_lib import *

class CategoryClass(CommonMethods):

    def create_category (self, browser, categoryname):
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('Product Categories').click()
        browser.find_element_by_link_text('Create a Category').click()
        self.wait_until_element_present('catname', 'ID').send_keys(categoryname)
        browser.find_element_by_id('category_custom_url').clear()
        browser.find_element_by_id('catpagetitle').send_keys('Title')
        browser.find_element_by_id('catmetakeywords').send_keys('Keywords')
        browser.find_element_by_id('catmetadesc').send_keys('Description')
        browser.find_element_by_id('catsearchkeywords').send_keys('Search Keywords')
        browser.find_element_by_xpath('//button[text()="Save & Exit"]').click()
        assert categoryname in browser.find_element_by_id('CategoryList').text

    def edit_category(self, browser, categoryname, updatename):
        self.edit_without_search(browser, categoryname)
        self.wait_until_element_present('catname', 'ID').clear()
        browser.find_element_by_id('catname').send_keys(updatename)
        browser.find_element_by_link_text('Reset').click()
        browser.find_element_by_id('category_custom_url').clear()
        WebDriverWait(browser, 60).until(lambda s: s.find_element_by_id('category_custom_url').get_attribute('value').replace('/', '') == s.find_element_by_id('catname').get_attribute('value').lower())
        browser.find_element_by_id('catpagetitle').clear()
        browser.find_element_by_id('catpagetitle').send_keys('Title')
        browser.find_element_by_xpath('//button[text()="Save & Exit"]').click()
        assert updatename in browser.find_element_by_id('CategoryList').text

    def delete_category(self, browser, categoryname):
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('Product Categories').click()
        browser.execute_script("$('tr:contains(" + categoryname + ") td:nth-child(1) [type=\"checkbox\"]').prop('checked',true)")
        browser.find_element_by_id('IndexDeleteButton').click()
        element = self.wait_until_element_present('#display-modal .btn-primary', 'CSS_SELECTOR')
        element.click()
        assert categoryname not in str(self.find_element_by_css_selector("#CategoryList").text)
        #assert browser.execute_script("return $('td:contains(" + categoryname + ")').text()") == ""
