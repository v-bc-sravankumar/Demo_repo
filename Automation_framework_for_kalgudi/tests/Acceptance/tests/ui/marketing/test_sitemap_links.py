from lib.ui_lib import *
from urlparse import *
import re

def test_sitemap(browser, url, email, password):
    # this ensures we also test with the alternate/secondary/temporary url
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    common.set_feature_flag(browser, 'enable', 'ShopPathNormalFix')
    common.set_feature_flag(browser, 'enable', 'CanonicalLink')
    alternate_url = re.sub(r'/admin/', '', browser.current_url)

    # this ensures thats regardless of the URL passed in, we always start with canonical url
    browser.get(url)
    canonical_url = browser.find_element_by_xpath("//*[@id=\"LogoContainer\"]/h1/a").get_attribute("href").strip('/')

    page = '/sitemap'
    browser.get(urljoin(alternate_url, page))

    # lets find canonica_url in every a href and fail if not
    xpath = "//a[contains(@href,'" + alternate_url + "')]"
    elems = browser.find_elements_by_xpath(xpath)

    pages = ['/account.php', '/login.php?action=create_account', '/login.php', '/search.php', '/wishlist.php']
    for elem in elems:
        url = urlparse(elem.get_attribute('href'))
        assert url.path in pages
