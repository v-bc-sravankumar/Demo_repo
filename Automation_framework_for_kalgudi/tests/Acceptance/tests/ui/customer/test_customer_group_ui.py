from helpers.ui.control_panel.customergroup_class import *


GROUP_NAME = CommonMethods.generate_random_string()
UPDATE_GROUP_NAME = CommonMethods.generate_random_string()

def test_create_customer_group(browser, url, email, password):
    cgoup = CustomerGroupClass(browser)
    cgoup.go_to_admin(browser, url, email, password)
    cgoup.create_customer_group(browser, GROUP_NAME)

def test_edit_customer_group(browser, url, email, password):
    cgoup = CustomerGroupClass(browser)
    cgoup.edit_customer_group(browser, GROUP_NAME, UPDATE_GROUP_NAME)


def test_delete_customer_group(browser, url, email, password):
    cgoup = CustomerGroupClass(browser)
    cgoup.delete_customer_group(browser, UPDATE_GROUP_NAME)
