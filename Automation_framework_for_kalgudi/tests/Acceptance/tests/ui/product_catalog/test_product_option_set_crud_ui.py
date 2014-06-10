from helpers.ui.control_panel.product_class import *


OPTION_SET_NAME = CommonMethods.generate_random_string()
UPDATED_OPTION_SET_NAME = CommonMethods.generate_random_string()

#************************************************************
# Description:Verify Product option set CRUD in control Panel
#************************************************************

def test_create_product_option_set(browser, url, email, password):
    product = ProductClass(browser)
    product.go_to_admin(browser, url, email, password)
    product.create_product_option_set(browser, OPTION_SET_NAME)

def test_edit_product_option_set(browser, url, email, password):
    product = ProductClass(browser)
    product.edit_product_option_set(browser, OPTION_SET_NAME, UPDATED_OPTION_SET_NAME)

def test_delete_product_option_set(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    product = ProductClass(browser)
    element = product.wait_until_element_present('tab-optionSetsTab', "ID")
    element.click()

    product.delete_product_option(browser, UPDATED_OPTION_SET_NAME)
