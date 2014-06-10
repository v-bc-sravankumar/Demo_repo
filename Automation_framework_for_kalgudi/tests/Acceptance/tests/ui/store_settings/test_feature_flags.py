from lib.ui_lib import *


def test_ff_ebay_disabled(browser, url, email, password):
    login = CommonMethods(browser)
    login.go_to_admin(browser, url, email, password)
    login.set_feature_flag(browser, 'disable', 'ShowSellOnEBay')
    browser.find_element_by_link_text('Orders').click()
    browser.find_element_by_link_text('View Orders').click()
    browser.find_element_by_link_text("Custom Views").click()
    browser.find_element_by_id("custom-orders-search-create").click()
    try:
        browser.find_element_by_id("ebayOrderId")
    except NoSuchElementException:
        pass
    #Checking for Ebay is under Order-> Search Orders
    browser.find_element_by_link_text('Orders').click()
    browser.find_element_by_link_text('Search Orders').click()
    try:
        browser.find_element_by_id("ebayOrderId")
    except NoSuchElementException:
        pass
    #Checking for Ebay under View Products-> Bulk action menu
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('View Products').click()
    assert "List These Products on eBay" not in browser.find_element_by_id("bulk-action-menu").text


def test_ff_ebay_enabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'enable', 'ShowSellOnEBay')
    #Checking for Ebay under Marketing
    browser.find_element_by_link_text('Marketing').click()
    browser.find_element_by_link_text('Sell on eBay').click()
    #Checking for Ebay is under Order-> Custom View
    browser.find_element_by_link_text('Orders').click()
    browser.find_element_by_link_text('View Orders').click()
    browser.find_element_by_link_text("Custom Views").click()
    assert 'Orders from eBay' in browser.find_element_by_id("custom-orders-view-id").text
    #Checking for Ebay is under Order-> Create a new Custom View
    browser.find_element_by_link_text('Orders').click()
    browser.find_element_by_link_text('View Orders').click()
    browser.find_element_by_link_text("Custom Views").click()
    browser.find_element_by_id("custom-orders-search-create").click()
    assert 'Orders from eBay' in browser.find_element_by_id("ebayOrderId").text
    #Checking for Ebay is under Order-> Search Orders
    browser.find_element_by_link_text('Orders').click()
    browser.find_element_by_link_text('Search Orders').click()
    assert 'Orders from eBay' in browser.find_element_by_id("ebayOrderId").text
    #Checking for Ebay under View Products-> Bulk action menu
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('View Products').click()
    assert "List These Products on eBay" in browser.find_element_by_id("bulk-action-menu").text


def test_ff_googleshoppingfeed_disabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'disable', 'ShowGoogleShoppingFeed')
    #Checking for Google Shopping in Add Products
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Add a Product').click()
    try:
        browser.find_element_by_id("tab-google-product-search")
    except NoSuchElementException:
        pass
    #Checking for Google Shopping in Product Category
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Product Categories').click()
    browser.find_element_by_link_text('Create a Category').click()
    try:
        browser.find_element_by_id("tab-tab_google_ps")
    except NoSuchElementException:
        pass


#THIS TEST IS SKIPPED BECAUSE IT WILL WORK ONLY FOR STORE'S HAVING HIGHER PLANS
@pytest.mark.skipif(True, reason="This works for store with gold plan and above")
def test_ff_googleshoppingfeed_enabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'enable', 'ShowGoogleShoppingFeed')
    #Checking for Google Shopping in Add Products
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Add a Product').click()
    assert 'Google Shopping' in browser.find_element_by_id("tab-google-product-search").text
    #Checking for Google Shopping in Product Category
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Product Categories').click()
    browser.find_element_by_link_text('Create a Category').click()
    assert 'Google Shopping' in browser.find_element_by_id("tab-tab_google_ps").text
    #Checking for Google Shopping under Marketing
    browser.find_element_by_link_text('Marketing').click()
    panels = browser.find_elements_by_css_selector(".panel-inline")
    assert 'Google Shopping Feed' in panels[8].text


def test_ff_shoppingcomparison_disabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'disable', 'ShowShoppingComparisonSites')
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('View Products').click()
    assert "Toggle Shopping Comparison Feeds" not in browser.find_element_by_id("bulk-action-menu").text
    #Checking for ShoppingComparison under Add Products
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Add a Product').click()
    login.wait_until_element_present('Other Details', 'LINK').click()
    panelheadings = browser.find_elements_by_css_selector("#other-details .panel-heading")
    assert "Shopping Comparison" not in panelheadings[6].text
    #Checking for ShoppingComparison under Bulk action menu under Product category
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Product Categories').click()
    try:
        browser.find_element_by_id("bulk-action-menu")
    except NoSuchElementException:
        pass


def test_ff_shoppingcomparison_enabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'enable', 'ShowShoppingComparisonSites')
    #Click on Shopping comparison
    browser.find_element_by_link_text('Marketing').click()
    browser.find_element_by_link_text('Shopping Comparison Sites').click()
    #Set a comparison site
    try:
        login.wait_until_element_present("Bizrate/Shopzilla/Beso", "LINK")
    except:
        login.wait_until_element_present("ISSelectmodules_shoppingcomparison_bizrate", "ID").click()
        browser.find_element_by_xpath('//button[text()="Save"]').click()

    #Checking for ShoppingComparison under View Products-> Bulk action menu
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('View Products').click()
    assert "Toggle Shopping Comparison Feeds" in browser.find_element_by_id("bulk-action-menu").text
    #Checking for ShoppingComparison under Add Products
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Add a Product').click()
    login.wait_until_element_present('Other Details', 'LINK').click()
    panelheadings = browser.find_elements_by_css_selector("#other-details .panel-heading")
    assert "Shopping Comparison" in panelheadings[6].text
    #Checking for ShoppingComparison under Bulk action menu under Product category
    browser.find_element_by_link_text('Products').click()
    browser.find_element_by_link_text('Product Categories').click()
    assert 'Bulk Update Bizrate' in browser.find_element_by_id("bulk-action-menu").text


def test_ff_customergroup_disabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'disable', 'ShowCustomerGroups')
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('View Customers').click()
    browser.find_element_by_link_text("Custom Views").click()
    browser.find_element_by_id("custom-search").find_element_by_link_text("create a new view").click()
    assert 'Choose a Customer Group' not in browser.find_element_by_css_selector(".OuterPanel").text
    #Checking for Customer Group under Add Customers
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('Add a Customer').click()
    assert 'Do not assign to a customer group' not in browser.find_element_by_css_selector('.panel-block').text
    #Checking for Customer Group under Search Customers
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('Search Customers').click()
    try:
        browser.find_element_by_id("custGroupId")
    except NoSuchElementException:
        pass
    #Checking for Customer Group in Store settings
    browser.find_element_by_link_text('Setup & Tools').click()
    browser.find_element_by_link_text('Store settings').click()
    browser.find_element_by_link_text('Miscellaneous').click()
    assert 'Customer Groups Settings' not in browser.find_element_by_id('frmSettings').text


def test_ff_customergroup_enabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'enable', 'ShowCustomerGroups')
    #Click on Customer Group
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('Customer Groups').click()
    #Checking for Customer Group under View Customers-> Create a new Custom View
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('View Customers').click()
    browser.find_element_by_link_text("Custom Views").click()
    browser.find_element_by_id("custom-search").find_element_by_link_text("create a new view").click()
    assert 'Choose a Customer Group' in browser.find_element_by_css_selector(".OuterPanel").text
    #Checking for Customer Group under Add Customers
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('Add a Customer').click()
    assert 'Do not assign to a customer group' in browser.find_element_by_css_selector('.panel-block').text
    #Checking for Customer Group under Search Customers
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('Search Customers').click()
    assert 'Choose a Customer Group' in browser.find_element_by_id('custGroupId').text
    #Checking for Customer Group in Store settings
    browser.find_element_by_link_text('Setup & Tools').click()
    browser.find_element_by_link_text('Store settings').click()
    browser.find_element_by_link_text('Miscellaneous').click()
    assert 'Customer Groups Settings' in browser.find_element_by_id('frmSettings').text


def test_ff_customergroupdiscount_disabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'disable', 'ShowCustomerGroupsDiscounts')
    #Checking for CustomerGroup discounts
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('Customer Groups').click()
    browser.find_element_by_link_text('Create a Customer Group').click()
    customergrouptext = browser.find_element_by_id('customer-groups-form').text
    assert 'Group Discount' not in customergrouptext
    assert 'Category Level Discounts' not in customergrouptext
    assert 'Product Level Discounts' not in customergrouptext
    assert 'Storewide Discount' not in customergrouptext


def test_ff_customergroupdiscount_enabled(browser, url, email, password):
    login = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    login.set_feature_flag(browser, 'enable', 'ShowCustomerGroupsDiscounts')
    #Checking for CustomerGroup discounts
    browser.find_element_by_link_text('Customers').click()
    browser.find_element_by_link_text('Customer Groups').click()
    browser.find_element_by_link_text('Create a Customer Group').click()
    customergrouptext = browser.find_element_by_id('customer-groups-form').text
    assert 'Group Discount' in customergrouptext
    assert 'Category Level Discounts' in customergrouptext
    assert 'Product Level Discounts' in customergrouptext
    assert 'Storewide Discount' in customergrouptext
