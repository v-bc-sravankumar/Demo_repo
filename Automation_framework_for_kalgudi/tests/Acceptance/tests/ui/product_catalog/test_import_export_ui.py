from lib.ui_lib import *


def test_import_basic(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Import Products').click()
    filepath = (os.path.dirname(__file__))[:-2] + "/fixtures/testproduct.csv"
    common.wait_until_element_present('ImportFile', 'ID').send_keys(filepath)
    browser.find_element_by_xpath('//input[@type="submit"]').click()
    browser.find_element_by_xpath('//input[@type="submit"]').click()
    browser.find_element_by_id('StartImport').click()
    common.verify_and_assert_success_message(browser, "The product import was completed successfully.",
                                             ".alert-success")


def test_export_basic(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    common = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Export Products').click()
    common.select_dropdown_value(browser, 'template', 'Bulk Edit')
    browser.find_element_by_xpath('//button[text()="Continue"]').click()
    common.wait_until_element_present('Export my products to a CSV file', 'LINK').click()
    download = common.wait_until_element_present('Download my Products file', 'LINK')
    download.click()
