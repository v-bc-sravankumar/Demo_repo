from lib.ui_lib import *
from fixtures.currency import *


class CurrencyClass(CommonMethods, DataCurrency):
    def navigate_to_currency(self):
        self.find_element_by_link_text("Setup & Tools").click()
        self.find_element_by_link_text("Currencies").click()

    def currency_row(self, browser, currency_name):
        element = self.wait_until_element_present('tbl-admin', 'CLASS_NAME')
        row = [tr for tr in element.find_elements_by_css_selector('tr') if tr.text.find(currency_name) != -1]
        return row

    def create_currency(self, browser, name):
        self.navigate_to_currency()
        element = self.wait_until_element_present('IndexCreateButton','ID')
        if name[0][1] not in self.find_element_by_xpath('//tbody').text:
            element.click()
            for val in name:
                if val[0] not in 'Currency Country':
                    self.enter_text(val[3], val[1])
                else:
                    self.find_element_by_css_selector(val[3]).click()

            self.find_element_by_xpath('//input[@value = "Save"]').click()
        return self.currency_row(browser, name[0][1])

    def set_as_default(self, browser, name):
        try:
            self.wait_until_element_present("//td[contains(.,'" + name[0][1] + "')]/span[contains(.,'(default)')]", "XPATH")

        except:
            self.find_element_by_xpath(
                "//td[contains(.,'" + name[0][1] + "')]/following-sibling::td/descendant::button").click()
            element = self.wait_until_element_present("//td[contains(.,'" + name[0][
                1] + "')]/following-sibling::td/descendant::a[contains(.,'Set as Default')]")
            element.click()
            self.wait_until_element_present("CurrencyPopupButtonYesPrice", time=30).click()
            assert self.wait_until_element_present("//td[contains(.,'" + name[0][1] +
                                                   "')]/span[contains(.,'(default)')]")

    def edit_currency(self, browser, name, updatedname):
        browser.execute_script("window.location = $('.tbl-admin').find('td:contains(" +
                               name[0][1] + ")').parent('tr').find('.hybridactions')" +
                               ".find('.panel-inline').find('a:eq(0)').attr('href')")
        self.wait_until_element_present('currencyname', 'ID')
        for val in updatedname:
            if val[0] not in 'Currency Country':
                self.enter_text(val[3], val[1], browser, val[2])
            else:
                browser.find_element_by_css_selector(val[3]).click()
        browser.find_element_by_xpath('//input[@value = "Save"]').click()
        return self.currency_row(browser, updatedname[0][1])

    def delete_currency(self, browser, name):
        self.wait_until_element_present('IndexDeleteButton', "ID")
        browser.execute_script("$('.GridRow').find('td:contains(" + name[0][1] +
                               ")').parent('tr').children('td:eq(0)').find('input').attr('checked','checked')")
        browser.find_element_by_id('IndexDeleteButton').click()
        try:
            alert = browser.switch_to_alert()
            alert.accept()
        except WebDriverException:
            browser.execute_script("window.confirm = function(){return true;}")
            browser.find_element_by_name('IndexDeleteButton').click()
        return self.currency_row(browser, name[0][1])
