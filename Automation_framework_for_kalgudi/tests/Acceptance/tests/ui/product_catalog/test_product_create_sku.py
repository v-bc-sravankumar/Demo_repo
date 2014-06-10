from helpers.ui.control_panel.product_class import *

PRODUCT_NAME = CommonMethods.generate_random_string()
SKU = CommonMethods.generate_random_string()
@pytest.mark.skipif("True")
def test_create_product_ui(browser, url, email, password):
    product = ProductClass(browser)
    product.go_to_admin(browser, url, email, password)
    product.create_product_ui(browser,PRODUCT_NAME)

@pytest.mark.skipif("True")
def test_create_new_sku(browser, url, email, password):
    product = ProductClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    product.create_new_sku(browser, PRODUCT_NAME, SKU)

@pytest.mark.skipif("True")
def test_verify_sku_on_storefront(browser, url, email, password):
    product = ProductClass(browser)
    browser.get(urlparse.urljoin(url, PRODUCT_NAME))
    product.find_element_by_xpath('//span[@title = "Silver"]/span[@class = "swatchColour swatchColour_1"]')
    try:
        browser.find_element_by_xpath('//span[@title = "Silver"]/span[@class = "swatchColour swatchColour_1"]').click()
    except:
        browser.execute_script("$('.showPreview[title=Silver]').trigger('click')")
    product.wait_until_element_present('//span[@class="VariationProductSKU"]', "XPATH")
    assert SKU in browser.find_element_by_xpath('//span[@class="VariationProductSKU"]').text

@pytest.mark.skipif("True")
def test_delete_product(browser, url, email, password):
    product = ProductClass(browser)
    product.delete_product(browser, PRODUCT_NAME)
