from lib.ui_lib import *
from helpers.ui.store_front.checkout_class import *

class TaxClass(CommonMethods, CheckoutClass):

    def navigate_to_tax(self, browser):
        browser.find_element_by_link_text('Setup & Tools').click()
        browser.find_element_by_link_text('Tax').click()

    def create_tax_zone(self, browser, name):
        self.navigate_to_tax(browser)
        element = self.find_element_by_id('tab-taxZonesTab')
        element.click()
        try:
            browser.find_element_by_xpath('//button[text() = "Add a Tax Zone..."]').click()
        except WebDriverException:
            browser.execute_script("$('.addTaxZoneButton').trigger('click')")
        element = self.find_element_by_id('name')
        element.send_keys(name)
        try:
            browser.find_element_by_id('ISSelectcountries_226').click()
        except:
            browser.execute_script("$('#countrySelect').find('li:contains(\"United States\")').trigger('click')")
        try:
            browser.find_element_by_xpath('//button[text() = "Save"]').click()
        except:
            browser.execute_script("$('.saveButton').trigger('click')")
        self.verify_and_assert_success_message(browser, "The new tax zone was created successfully. You now need to create some tax rates.", ".alert-success")

    def create_tax_rate(self, browser, name, rate):

        try:
            self.wait_until_element_present('//button[text() = "Add a Tax Rate..."]', 'XPATH').click()
        except WebDriverException:
            browser.execute_script("$('.addTaxRateButton').trigger('click')")
        element = self.wait_until_element_present('name', 'ID')
        element.send_keys(name)
        self.wait_until_element_present('rates-', 'ID').send_keys(rate)
        self.wait_until_element_present('rates-3', 'ID').send_keys('0')
        self.wait_until_element_present('rates-1', 'ID').send_keys('0')
        self.wait_until_element_present('rates-2', 'ID').send_keys('0')
        try:
            browser.find_element_by_xpath('//button[text() = "Save"]').click()
        except:
            browser.execute_script("$('.saveButton').trigger('click')")
        self.verify_and_assert_success_message(browser, "The new tax rate was saved successfully.", ".alert-success")

    def edit_tax_zone(self, browser, name, updatedname):
        self.navigate_to_tax(browser)

        element = self.wait_until_element_present('tab-taxZonesTab', 'ID')
        element.click()
        browser.execute_script("window.location=$('.taxZonesGrid').find('tr:contains(" + name + ")').find('.panel-inline').find('li:contains(Edit Settings)').find('a').attr('href')")
        element = self.find_element_by_id('name')
        element.clear()
        element.send_keys(updatedname)
        try:
            browser.find_element_by_xpath('//button[text() = "Save"]').click()
        except:
            browser.execute_script("$('.saveButton').trigger('click')")
        self.verify_and_assert_success_message(browser, "The updated tax zone has been saved successfully.", ".alert-success")


    def edit_tax_rate(self, browser, zonename, taxratename, updatedtaxratename,):
        self.navigate_to_tax(browser)
        element = self.wait_until_element_present('tab-taxZonesTab', 'ID')
        element.click()
        browser.execute_script("window.location=$('.taxZonesGrid').find('tr:contains(" + zonename + ")').find('.panel-inline').find('li:contains(Edit Rates)').find('a').attr('href')")
        time.sleep(2)
        browser.execute_script("window.location=$('#taxRatesGrid').find('tr:contains(" + taxratename + ")').find('.panel-inline').find('li:contains(Edit)').find('a').attr('href')")
        time.sleep(2)
        try:
            browser.find_element_by_id('name').clear()
            browser.find_element_by_id('name').send_keys(updatedtaxratename)
        except:
            browser.execute_script("$('#taxRateForm').find('#name').val(" + updatedtaxratename + ")")
        browser.find_element_by_id('rates-').clear()
        browser.find_element_by_id('rates-').send_keys('10')
        try:
            browser.find_element_by_xpath('//button[text() = "Save"]').click()
        except:
            browser.execute_script("$('.saveButton').trigger('click')")
        self.verify_and_assert_success_message(browser, "The tax rate was updated successfully.", ".alert-success")


    def delete_tax_zone(self, browser, taxzonename):
        browser.find_element_by_link_text('Setup & Tools').click()
        browser.find_element_by_link_text('Tax').click()
        element = self.find_element_by_id('tab-taxZonesTab')
        element.click()
        browser.execute_script("$('.taxZonesGrid').find('tr:contains(" + taxzonename + ")').find('input').attr('checked','checked')")
        browser.execute_script("window.confirm = function(){return true;}");
        self.find_element_by_css_selector('button.deleteTaxZonesButton').click()
        try:
            alert = browser.switch_to_alert()
            alert.accept()
        except WebDriverException:
            browser.execute_script("confirm()")
        self.verify_and_assert_success_message(browser, "The selected tax rate(s) were deleted successfully.", ".alert-success")


    def delete_tax_rate(self, browser, zonename, taxratename):
        try:
            browser.find_element_by_link_text('Setup & Tools').click()
        except WebDriverException as e:
            if "Click succeeded but Load Failed" in e.msg:
                pass
        browser.find_element_by_link_text('Tax').click()
        element = self.find_element_by_id('tab-taxZonesTab')
        element.click()
        browser.execute_script("window.location=$('.taxZonesGrid').find('tr:contains(" + zonename + ")').find('.panel-inline').find('li:contains(Edit Rates)').find('a').attr('href')")
        self.wait_until_element_present('tab-taxRatesTab', 'ID').click()
        browser.execute_script("$('tr:contains(\""+taxratename+"\") .check input').click()")
        try:
            browser.find_element_by_id('IndexDeleteButton').click()
        except WebDriverException as e:
            if "Click succeeded but Load Failed" in e.msg:
                pass
        self.find('#display-modal button.btn-primary').click()
        assert "The selected tax rate(s) were deleted successfully." in str(self.find('.alert-success').text)

    def check_tax_storefront(self, browser, url):
        if 'https://' in url:
            url = url.replace('https://', 'http://')
        browser.get(urlparse.urljoin(url, 'donatello-brown-leather-handbag-with-shoulder-strap'))
        price = browser.find_element_by_xpath('//span[@class = "ProductPrice VariationProductPrice"]').text
        PRODUCT_PRICE = float(price[1:])
        browser.find_element_by_css_selector('.add-to-cart').click()
        element=self.wait_until_element_present('.ProceedToCheckout a', 'CSS_SELECTOR', browser)
        element.click()

        return PRODUCT_PRICE
