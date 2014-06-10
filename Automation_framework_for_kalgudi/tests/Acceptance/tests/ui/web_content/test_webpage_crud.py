from helpers.ui.control_panel.webpage_class import *

WEB_PAGE_NAME = CommonMethods.generate_random_string()
UPDATED_PAGE_NAME = CommonMethods.generate_random_string()

def test_create_webpage(browser, url, email, password):
    web = WebpageClass(browser)
    web.go_to_admin(browser, url, email, password)
    web.create_webpage(browser, WEB_PAGE_NAME)

@pytest.mark.skipif("True")
def test_edit_webpage(browser, url, email, password):
    web = WebpageClass(browser)
    web.edit_webpage(browser, WEB_PAGE_NAME, UPDATED_PAGE_NAME)

@pytest.mark.skipif("True")
def test_verify_webpage_on_storefront(browser, url, email, password):
    web = WebpageClass(browser)
    browser.get(urlparse.urljoin(url, UPDATED_PAGE_NAME))
    browser.execute_script("$('span:contains(" + UPDATED_PAGE_NAME + ")').first().click();")
    assert UPDATED_PAGE_NAME.upper() == browser.execute_script("return $('h1:contains(" + UPDATED_PAGE_NAME + ")').text();").upper()

def test_delete_webpage(browser, url, email, password):
    web = WebpageClass(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    web.delete_webpage(browser, WEB_PAGE_NAME)
