from lib.ui_lib import *


def go_to_store_settings(browser, url, email, password):
    """Navigates to Store settings page"""
    maint = CommonMethods(browser)
    maint.go_to_admin(browser, url, email, password)
    maint.find_element_by_link_text('Setup & Tools').click()
    maint.wait_until_element_present("Store settings", "LINK", browser).click()


def turning_toggle_on_off(browser):
    """Turns on or off the maintenance mode toggle switch"""
    maint = CommonMethods(browser)
    maint.wait_until_element_present("content", "ID", browser)
    try:
        maint.execute_script("return $('.checkbox-toggle span.is-active:contains(\"Open\")').length") == 1
    except TimeoutException:
        maint.execute_script("return $('.checkbox-toggle span.is-active:contains(\"Open\")').length") == 0
    maint.find_element_by_xpath("//div[@class=\"checkbox-toggle reverse-toggle\"]/label").click()
    maint.find_element_by_name('SubmitButton').click()
    maint.verify_and_assert_success_message(browser, "The modified settings have been saved successfully.",
                                            ".alert-success")


def test_maintenance_mode_on(browser, url, email, password):
    """Puts the store in to maintenance mode,verifies and checks the link at store front"""
    maint = CommonMethods(browser)
    go_to_store_settings(browser, url, email, password)
    turning_toggle_on_off(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    store_url = re.sub(r'/admin/', '', browser.current_url)
    maint.get(store_url)
    maint.wait_until_element_present("Click here to see your store", "LINK", browser).click()
    assert "Your store is down for maintenance." in maint.find_element_by_id('MaintenanceModeHeader').text


def test_maintenance_mode_off(browser, url, email, password):
    """Turns the maintenance mode off and confirms the store front is visible"""
    maint = CommonMethods(browser)
    go_to_store_settings(browser, url, email, password)
    turning_toggle_on_off(browser)
    maint.get(url)
    maint.wait_until_element_present("Header", "ID", browser)
    assert maint.find_element_by_link_text("Sign in")
