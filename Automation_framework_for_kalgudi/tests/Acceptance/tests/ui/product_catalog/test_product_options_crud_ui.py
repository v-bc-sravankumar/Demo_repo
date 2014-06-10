from helpers.ui.control_panel.product_class import *

OPTION_NAME = CommonMethods.generate_random_string()
UPDATED_OPTION_NAME = CommonMethods.generate_random_string()

#******************************************************
# Description:Verify Product options CRUD in control Panel
#******************************************************
def test_create_product_option(browser, url, email, password):
    product = ProductClass(browser)
    product.go_to_admin(browser, url, email, password)
    product.create_product_option(browser, OPTION_NAME)

def test_edit_product_option(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    product = ProductClass(browser)
    product.edit_product_option(browser, OPTION_NAME, UPDATED_OPTION_NAME)

def test_delete_product_option(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    product = ProductClass(browser)
    product.delete_product_option(browser, UPDATED_OPTION_NAME)
