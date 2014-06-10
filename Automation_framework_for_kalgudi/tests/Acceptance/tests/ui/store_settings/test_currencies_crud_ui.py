from helpers.ui.control_panel.currency_class import *


def test_create_currency(browser, url, email, password):
    currency = CurrencyClass(browser)
    currency.go_to_admin(browser, url, email, password)
    currency_row = currency.create_currency(browser, currency.IndianRupees)
    assert currency_row and len(currency_row) == 1


def test_edit_currency(browser):
    currency = CurrencyClass(browser)
    currency_row = currency.edit_currency(browser, currency.IndianRupees, currency.CanadianDollar)
    assert currency_row and len(currency_row) == 1


def test_delete_currency(browser):
    currency = CurrencyClass(browser)
    currency_row = currency.delete_currency(browser, currency.CanadianDollar)
    assert (currency_row is not None) and len(currency_row) == 0


def test_create_currency_with_validation(browser, url, email, password):
    # skip test if browser is phantomjs
    if browser.capabilities['browserName'] == 'phantomjs': pytest.skip("browser not supported")
    currency = CurrencyClass(browser)
    currency.go_to_admin(browser, url, email, password)
    name = currency.IndianRupees[0][1]
    currency.navigate_to_currency()

    currency.wait_until_element_present('IndexCreateButton', "ID").click()
    currency.wait_until_element_present('currencyname', "ID").send_keys(name)
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert currency.get_alert_text() == "Please select a country or region for this currency."

    browser.find_element_by_id('currencycode').send_keys('45')
    browser.execute_script(
        "$('#currencyorigin').find('option:contains(India)').attr('selected','selected'); toggleOrigin();")
    browser.find_element_by_xpath('//label[@for = "currencyconvertercurrency_bigcommerce"]').click()
    browser.find_element_by_class_name('FormButton').click()
    assert currency.get_alert_text() == "Please enter a valid currency code first."

    browser.find_element_by_id('currencycode').clear()
    browser.find_element_by_id('currencycode').send_keys('INR')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert currency.get_alert_text() == "Please enter an exchange rate for this currency."

    browser.find_element_by_class_name('FormButton').click()
    alert_text = currency.get_alert_text()
    rest = "The current exchange rate of (\d+.\d+) for this currency has been updated"
    matches = re.compile(rest).findall(alert_text)
    assert len(matches) > 0

    conversion_rate = matches[0]
    exchange_rate = currency.find_element_by_id("currencyexchangerate").get_attribute("value")
    assert exchange_rate == conversion_rate

    element = currency.wait_until_element_present('currencystring', "ID")
    element.send_keys('$')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert (currency.get_alert_text() == 'Please enter a decimal token.')

    browser.find_element_by_id('currencydecimalstring').send_keys('5')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert (currency.get_alert_text() == 'Please enter a thousands token.')

    browser.find_element_by_id('currencythousandstring').send_keys('7')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert (currency.get_alert_text() == 'Please enter a decimal places.')

    browser.find_element_by_id('currencydecimalplace').send_keys('A')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert (currency.get_alert_text() == "The decimal place is invalid. The decimal place must consist of number(s).")

    browser.find_element_by_id('currencydecimalplace').clear()
    browser.find_element_by_id('currencydecimalplace').send_keys('2')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert (currency.get_alert_text() == ("The currency decimal string is invalid. The currency decimal " +
            "string must only contain 1 character (no numbers)."))

    browser.find_element_by_id('currencydecimalstring').clear()
    browser.find_element_by_id('currencydecimalstring').send_keys('.')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert (currency.get_alert_text() == "The currency thousand string is invalid. The currency thousand " +
            "string must only contain 1 character (no numbers).")

    browser.find_element_by_id('currencythousandstring').clear()
    browser.find_element_by_id('currencythousandstring').send_keys(',')
    browser.find_element_by_id('currencycode').clear()
    browser.find_element_by_id('currencycode').send_keys('45')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    assert (currency.get_alert_text() == "The currency code is invalid. The code must consist of only 3 characters.")

    browser.find_element_by_id('currencycode').clear()
    browser.find_element_by_id('currencycode').send_keys('INR')
    browser.find_element_by_xpath('//input[@value = "Save"]').click()
    currency_row = currency.currency_row(browser, name)
    assert currency_row and len(currency_row) == 1

