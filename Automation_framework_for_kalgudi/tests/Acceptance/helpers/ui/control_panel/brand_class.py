from lib.ui_lib import *

class BrandClass(CommonMethods):

    def create_brand(self, browser, brandname):

        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('View Brands').click()
        browser.find_element_by_link_text('Add a Brand...').click()
        browser.find_element_by_id('brands').clear()
        browser.find_element_by_id('brands').send_keys(brandname)
        browser.find_element_by_xpath('//button[text()="Save"]').click()
        self.verify_and_assert_success_message(browser, "1 brand was added successfully.", ".alert-success")

    def edit_brand(self, browser, brandname, updatedname):
        self.search_and_edit(browser, '//input[@id="brands-filter"]', brandname)
        browser.find_element_by_id('brandName').clear()
        browser.find_element_by_id('brandName').send_keys(updatedname)
        browser.find_element_by_id('brandPageTitle').send_keys('Test')
        browser.find_element_by_xpath('//button[text()="Save"]').click()
        self.verify_and_assert_success_message(browser, "The selected brand has been updated successfully.", ".alert-success")


    def delete_brand(self, browser, brandname):

        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('View Brands').click()
        element = self.wait_until_element_present('//input[@id="brands-filter"]', "XPATH")
        element.clear()
        self.navigate_using_paginator(browser, brandname)
        element = self.wait_until_element_present("//tr[contains(.,'" + brandname + "')]", "XPATH")
        element.find_element_by_tag_name('label').click()
        browser.find_element_by_id('IndexDeleteButton').click()
        try:
             self.find_element_by_css_selector('#display-modal .btn-primary').click()
        except NoSuchElementException:
            browser.find_element_by_xpath('//button[text()="Cancel"]').click()
            element = self.wait_until_element_present("//tr[contains(.,'" + brandname + "')]", "XPATH")
            element.find_element_by_tag_name('label').click()
            browser.find_element_by_id('IndexDeleteButton').click()
            self.find_element_by_css_selector('#display-modal .btn-primary').click()

        self.verify_and_assert_success_message(browser, "The selected brand(s) have been deleted successfully.", ".alert-success")
