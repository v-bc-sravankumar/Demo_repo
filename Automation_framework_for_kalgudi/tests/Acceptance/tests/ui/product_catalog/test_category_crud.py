from helpers.ui.control_panel.category_class import *

CATEGORY_NAME = CommonMethods.generate_random_string()
UPDATED_CATEGORY_NAME = CommonMethods.generate_random_string()


def test_create_category(browser, url, email, password):
    category = CategoryClass(browser)
    category.go_to_admin(browser, url, email, password)
    category.create_category(browser, CATEGORY_NAME)


def test_edit_category(browser, url, email, password):
    category = CategoryClass(browser)
    category.edit_category(browser, CATEGORY_NAME, UPDATED_CATEGORY_NAME)


def test_verify_category_on_storefront(browser, url, email, password):
    browser.get(urlparse.urljoin(url, UPDATED_CATEGORY_NAME))
    assert UPDATED_CATEGORY_NAME.upper() == browser.execute_script("return $('h1:contains(" + UPDATED_CATEGORY_NAME + ")').text();").upper()


def test_delete_category(browser, url, email, password):
    category = CategoryClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    category.delete_category(browser, UPDATED_CATEGORY_NAME)
