from lib.ui_lib import *

def test_there_are_featured_products_on_the_homepage(browser, url, email, password):
    browser.get(urlparse.urljoin(url, '/'))
    assert browser.execute_script("return $('.ProductList li').length;") >1

def test_there_are_6_ladies_products(browser, url, email, password):
    browser.get(urlparse.urljoin(url, '/ladies'))
    assert 6 == browser.execute_script("return $('.ProductList li').length;")

def test_there_are_25_brands(browser, url, email, password):
    browser.get(urlparse.urljoin(url, '/brands'))
    time.sleep(5)
    assert 25 <= browser.execute_script("return $('.SubBrandList li').length;")

def test_product_details_page(browser, url, email, password):
    browser.get(urlparse.urljoin(url, '/gucci-white-knight-duffle'))
    assert "[Sample] Gucci, white knight duffle" == browser.execute_script("return $('.ProductMain h1').text();")
    assert "$490.00" in browser.execute_script("return $('.ProductPrice').text();")

