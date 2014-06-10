from helpers.ui.control_panel.customer_class import *
from fixtures.account import *

faker = Factory.create()
FIRST_NAME = faker.firstName() + CommonMethods.generate_random_string()
LAST_NAME = faker.lastName() + CommonMethods.generate_random_string()
COMPANY = faker.company()
PHONE = faker.phoneNumber()
EMAIL = faker.email()
EMAIL = EMAIL.translate(None, ",!;#'?$%^&*()-~")
STREET_ADD1 = faker.buildingNumber()
STREET_ADD2 = faker.streetName()
CITY = faker.city()
PHONE = faker.phoneNumber()
STATE = 'New South Wales'
POSTCODE = '2000'

UPDATE_FIRST_NAME = faker.firstName() + CommonMethods.generate_random_string(5)
UPDATE_LAST_NAME = faker.lastName() + CommonMethods.generate_random_string(5)



def test_create_customer(browser, url, email, password):
    customer = CustomerClass(browser)
    customer.go_to_admin(browser, url, email, password)
    customer.create_customer(browser, FIRST_NAME, LAST_NAME, COMPANY, EMAIL, PHONE)

def test_edit_customer(browser, url, email, password):
    customer = CustomerClass(browser)
    customer.search_customers(browser, FIRST_NAME, LAST_NAME)
    browser.find_element_by_xpath("//tr[contains(.,'" + FIRST_NAME + "')]").find_element_by_css_selector('.dropdown-trigger').click()
    customer.edit_customer(browser, UPDATE_FIRST_NAME, UPDATE_LAST_NAME)

def test_create_customer_addrees(browser, url, email, password):
    customer = CustomerClass(browser)
    customer.search_customers(browser, UPDATE_FIRST_NAME, UPDATE_LAST_NAME)
    browser.find_element_by_xpath("//tr[contains(.,'" + UPDATE_FIRST_NAME + "')]").find_element_by_css_selector('.dropdown-trigger').click()
    customer.create_customer_address(browser, customer.au_customer_address)

def test_edit_customer_address(browser, url, email, password):
    customer = CustomerClass(browser)
    browser.find_element_by_xpath("//tr[contains(.,'" + customer.au_customer_address['FirstName']['Value'] + "')]").find_element_by_css_selector('.dropdown-trigger').click()
    customer.edit_customer_address(browser)

def test_delete_customer_address(browser, url, email, password):
    customer = CustomerClass(browser)
    customer.delete_customer_address(browser)

def test_delete_customer(browser, url, email, password):
    admin = urlparse.urljoin(url, 'admin')
    browser.get(admin)
    customer = CustomerClass(browser)
    customer.search_customers(browser, UPDATE_FIRST_NAME, UPDATE_LAST_NAME)
    customer.delete_customer(browser, UPDATE_FIRST_NAME, UPDATE_LAST_NAME)
