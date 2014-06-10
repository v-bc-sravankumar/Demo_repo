from helpers.ui.control_panel.order_class import *


faker = Factory.create()
EMAIL = faker.email()
PASSWORD = CommonMethods.generate_random_string()
FIRST_NAME = faker.firstName()
LAST_NAME = faker.lastName()
COMPANY = faker.company()
PHONE = faker.phoneNumber()
STREET_ADD1 = faker.buildingNumber()
STREET_ADD2 = faker.streetName()
CITY = faker.city()
MANUAL_PAYMENT_NAME = faker.name()
COUNTRY = 'Australia'
STATE = 'New South Wales'
POSTCODE = '2000'
QTY = '2'
UPDATED_FIRSTNAME = faker.firstName()
UPDATED_LAST_NAME = faker.lastName()
UPDATED_COMPANY = faker.company()
UPDATED_QTY = '3'

SHIPTRACK_NO = CommonMethods.generate_random_string()
SHIPMETHOD_DESC = CommonMethods.generate_random_string()
SHIP_COMMENTS = CommonMethods.generate_random_string()
SHIPPING_MODULE = 'Australia Post'

INVALID_EMAIL = faker.name()
INVALID_PWD = CommonMethods.generate_random_string()
#*********************************************************************************
# Description: Verify Order module scenarios functionality in admin panel
#*********************************************************************************


def test_create_order(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    order = OrderClass(browser)

    order.go_to_admin(browser, url, email, password)
    global orderID
    orderID = order.create_order_controlpanel(browser, EMAIL, PASSWORD, FIRST_NAME, LAST_NAME, COMPANY, PHONE, STREET_ADD1, STREET_ADD2, CITY, COUNTRY, STATE, POSTCODE, INVALID_EMAIL,INVALID_PWD)


def test_orders_shipitem(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    order = OrderClass(browser)
    order.find_element_by_class_name('order-id')

    order.wait_until_element_present("//tr[contains(., '" + orderID + "')]", 'XPATH')
    browser.execute_script("$('tr:contains(\""+orderID+"\") .shipTrigger').click()")
    order.wait_until_element_present('shipmethod', "ID", browser)
    try:
        order.select_dropdown_value(browser, 'shipping_module', SHIPPING_MODULE)
    except WebDriverException as e:
        order.select_dropdown_value(browser, 'shipping_module', SHIPPING_MODULE)
        if "Click succeeded but Load Failed" in e.msg:
            pass
    order.clear_field(browser, 'shipmethod')
    browser.find_element_by_id('shipmethod').send_keys(SHIPMETHOD_DESC)
    browser.find_element_by_id('shiptrackno').send_keys(SHIPTRACK_NO)
    browser.find_element_by_id('shipcomments').send_keys(SHIP_COMMENTS)
    browser.find_element_by_name('CreateShiptment').click()
    time.sleep(2)
    browser.execute_script("$('.view-shipments-trigger[data-order-id=" + orderID + "]').trigger('click')")
    order.wait_until_element_present('trackingNo', 'ID')
    browser_tracking_no = browser.find_element_by_id('trackingNo').get_attribute("value")
    # verify the ship item tracking number
    assert (browser_tracking_no == SHIPTRACK_NO)
    browser.find_element_by_id('close-shipments-dialog').click()


def test_edit_order(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    order = OrderClass(browser)
    order.goto_view_orders(browser)
    order.wait_until_element_present('OrderActionSelect', 'ID')
    order.search_order(browser, orderID)
    browser.find_element_by_xpath("//tr[contains(., '" + orderID + "')]").find_element_by_css_selector('.dropdown-trigger').click()
    element = order.wait_until_element_present("Edit Order", 'LINK')
    element.click()
    element = order.wait_until_element_present('FormField_4', 'ID')
    element.clear()
    browser.find_element_by_id('FormField_4').send_keys(UPDATED_FIRSTNAME)
    browser.find_element_by_id('FormField_5').clear()
    browser.find_element_by_id('FormField_5').send_keys(UPDATED_LAST_NAME)
    browser.find_element_by_id('FormField_6').clear()
    browser.find_element_by_id('FormField_6').send_keys(UPDATED_COMPANY)
    # Select Next to enter Quantity of an edited order
    browser.find_element_by_xpath('//button[text() = "Next"]').click()
    try:
        element = order.wait_until_element_present('#display-modal .btn-primary', "CSS_SELECTOR")
        element.click()
    except:
        try:
            element = order.wait_until_element_present('//button[text() = "Ok"]', "XPATH")
            element.click()
        except:
            pass

    # Provide order quantity and go to next step
    element = order.wait_until_element_present('quantity', "NAME")
    element.send_keys(UPDATED_QTY)
    browser.find_element_by_name('price').click()
    browser.find_element_by_name('AddAnother').click()
    try:
        order.wait_until_element_present('AddAnother', 'ID').click()
    except TimeoutException:
        browser.execute_script("$('.orderMachineNextButton').trigger('click')")
    order.wait_until_element_present('orderSaveButton', "CLASS_NAME")
    browser.execute_script("$('.orderSaveButton').trigger('click')")
    element = order.wait_until_element_present('//div[@class = "alert alert-success"]/p', "XPATH").text
    assert "has been updated successfully." in element

def test_search_advance(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    order = OrderClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    order.goto_view_orders(browser)
    order.wait_until_element_present('OrderActionSelect', 'ID')
    order.search_order(browser, orderID)
    # Selecting Advance Search Option
    browser.find_element_by_link_text('Search').click()
    order.wait_until_element_present('searchQuery', 'ID')
    browser.find_element_by_id('searchQuery').send_keys(orderID)
    browser.find_element_by_xpath('//button[text() = "Search"]').click()
    order.wait_until_element_present('//div[@class = "alert alert-success"]/p', 'XPATH')
    order.wait_until_element_present('order-id', 'CLASS_NAME')
    assert browser.find_elements_by_xpath("//form[@id='orders-index']/table/tbody[contains(., '" + orderID + "')]")

def test_delete_order(browser, url, email, password):
    pytest.skip("Skipping due to flakiness on Bamboo")
    order = OrderClass(browser)
    order.goto_view_orders(browser)
    order.search_order(browser, orderID)
    order.delete_order(browser)
