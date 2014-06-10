from helpers.ui.control_panel.brand_class import *


BRAND_NAME = CommonMethods.generate_random_string()
UPDATED_BRAND_NAME = "Updated" + BRAND_NAME


def test_create_brand(browser, url, email, password):
    brand = BrandClass(browser)
    brand.go_to_admin(browser, url, email, password)
    brand.create_brand(browser, BRAND_NAME)

def test_edit_brand(browser, url, email, password):
    brand = BrandClass(browser)
    brand.edit_brand(browser, BRAND_NAME, UPDATED_BRAND_NAME)

def test_verify_brand_on_storefront(browser, url, email, password):
    browser.get(url + '/brands/' + UPDATED_BRAND_NAME)
    browser.execute_script("$('span:contains(" + UPDATED_BRAND_NAME + ")').first().click();")
    assert UPDATED_BRAND_NAME.upper() == browser.execute_script("return $('h1:contains(" + UPDATED_BRAND_NAME + ")').text();").upper()

def test_delete_brand(browser, url, email, password):
    brand = BrandClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    brand.delete_brand(browser, UPDATED_BRAND_NAME)
