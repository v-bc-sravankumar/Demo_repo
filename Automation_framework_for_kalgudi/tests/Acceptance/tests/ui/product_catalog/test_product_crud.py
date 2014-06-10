from helpers.ui.control_panel.product_class import *
from lib.api_lib import *
from helpers.ui.control_panel.category_class import *

faker = Factory.create()
PRODUCT_NAME = CommonMethods.generate_random_string()

CATEGORY_NAME = CommonMethods.generate_random_string()
new_price = '100'
new = "NEW"

def test_create_product_ui(browser, url, email, password):
    product = ProductClass(browser)
    product.go_to_admin(browser, url, email, password)
    product.create_product_ui(browser,PRODUCT_NAME)


def test_edit_product(browser, url, email, password):
    product = ProductClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    product.edit_product(browser, PRODUCT_NAME)


def test_create_new_category(browser, url, email, password):
    product = ProductClass(browser)
    product.create_category(browser, PRODUCT_NAME, CATEGORY_NAME)


def test_add_image(browser, url, email, password):
    product = ProductClass(browser)
    product.add_image(browser, PRODUCT_NAME)


def test_301_redirect(browser, url, email, password):
    # To veify 301 redirect url changes are saved.
    product = ProductClass(browser)
    browser.find_element_by_link_text('Setup & Tools').click()
    browser.find_element_by_link_text('301 redirects').click()
    body = product.wait_until_element_present("//tr[contains(.,'" + PRODUCT_NAME + "')]", 'XPATH')
    assert PRODUCT_NAME in body.text


def test_delete_product(browser, url, email, password):
    product = ProductClass(browser)
    product.delete_product(browser, PRODUCT_NAME)

@pytest.mark.skipif("True")
def test_delete_category(browser, url, email, password):
    category = CategoryClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    category.delete_category(browser, CATEGORY_NAME)