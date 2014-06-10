from lib.ui_lib import *


class BannersClass(CommonMethods):

    def create_banner(self, browser, name):
        browser.find_element_by_link_text('Marketing').click()
        browser.find_element_by_link_text('View Banners').click()
        self.wait_until_element_present('Create a Banner', 'LINK').click()
        browser.find_element_by_id('bannername').send_keys(name)
        try:
            browser.execute_script("tinyMCE.activeEditor.dom.remove(tinyMCE.activeEditor.dom.select('p'));")
            browser.execute_script("tinymce.activeEditor.execCommand('mceInsertContent', true, \"TEST AUTOMATION BANNER\");")
        except WebDriverException:
            browser.find_element_by_id('wysiwyg').clear()
            browser.find_element_by_id('wysiwyg').send_keys('TEST AUTOMATION BANNER')

        browser.execute_script("$('#bannerpage1').prop('checked',true)")
        browser.execute_script("$('#bannerloc').val('bottom')")
        browser.find_element_by_name('SubmitButton1').click()
        self.verify_and_assert_success_message(browser, "The new banner has been saved successfully.", ".alert-success")

    def edit_banner(self, browser, name, updatedname):
        self.search_and_edit(browser, '//input[@id="banners-filter"]', name)
        browser.find_element_by_id('bannername').clear()
        browser.find_element_by_id('bannername').send_keys(updatedname)
        browser.find_element_by_name('SubmitButton1').click()
        self.verify_and_assert_success_message(browser, "The selected banner has been updated successfully.", ".alert-success")

    def delete_banner(self, browser, name):
        element=browser.find_element_by_link_text('Marketing')
        element.click()
        browser.find_element_by_link_text('View Banners').click()
        

        element = self.find_element_by_css_selector('#banners-filter')
        element.send_keys(name)
        element.send_keys(Keys.RETURN)

        # wait for banner and click checkbox to delete
        element = self.wait_until_element_present("//tr[contains(.,'" + name + "')]", "XPATH")
        element.find_element_by_tag_name('label').click()
        browser.find_element_by_id('IndexDeleteButton').click()
        self.wait_until_element_present('#display-modal .btn-primary', 'CSS_SELECTOR').click()
        try:
            self.verify_and_assert_success_message(browser, "No banners have been created.", ".alert-success")
        except TimeoutException:
            self.verify_and_assert_success_message(browser, "The selected banner(s) have been deleted successfully.", ".alert-success")
