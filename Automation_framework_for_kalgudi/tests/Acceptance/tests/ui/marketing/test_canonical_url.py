from lib.ui_lib import *
import re

def check_canonical_url(browser, url, url_part):
    browser.get(urlparse.urljoin(url, url_part))
    try:
        el = browser.find_element_by_xpath("//link[@rel='canonical']")
        return el.get_attribute("href")
    except NoSuchElementException:
        return ''

def test_presence_of_canonical(browser, url, email, password):
    # this ensures we also test with the alternate/secondary/temporary url
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    common.set_feature_flag(browser, 'enable', 'ShopPathNormalFix')
    common.set_feature_flag(browser, 'enable', 'CanonicalLink')
    alternate_url = re.sub(r'/admin/', '', browser.current_url)

    # this ensures thats regardless of the URL passed in, we always start with canonical url
    browser.get(url)
    canonical_url = browser.find_element_by_xpath("//*[@id=\"LogoContainer\"]/h1/a").get_attribute("href").strip('/')

    pages = ['/blog/', '/mens/', '/brands/', '/gant-red-duffle/', '/', '/accessories-2/', '/raquel-florentine-jungle-dress/']
    for page in pages:
        # browsing on http primary domain as specified
        assert check_canonical_url(browser, canonical_url, page) == canonical_url+page

        # browsing on https primary domain as specified
        assert check_canonical_url(browser, re.sub(r'\w+:', 'https:', url), page) == canonical_url+page

        # browsing on https secondary domain
        assert check_canonical_url(browser, alternate_url, page) == canonical_url+page

        # browsing on http secondary domain
        assert check_canonical_url(browser, re.sub(r'\w+:', 'http:', alternate_url), page) == canonical_url+page

def test_absence_of_canonical(browser, url, email, password):
   pages = ["/login.php", "/login.php?from=account.php%3Faction%3D", "/login.php?action=create_account", "/cart.php", "/sitemap",
            "/shipping-returns/", "/rss-syndication/"]

   for page in pages:
       assert check_canonical_url(browser, url, page) == ''

def get_robots_tag(browser, url, url_part):
    browser.get(urlparse.urljoin(url, url_part))
    try:
        el = browser.find_element_by_xpath("//html/head/meta[@name = 'robots']")
        return el
    except NoSuchElementException:
        return None

def test_presence_of_robots_tag(browser, url, email, password):
    pages = ["/login.php", "/login.php?from=account.php%3Faction%3D", "/login.php?action=create_account", "/cart.php", "/sitemap",
            "/shipping-returns/", "/rss-syndication/"]

    # this ensures we also test with the alternate/secondary/temporary url
    common = CommonMethods(browser)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    common.set_feature_flag(browser, 'enable', 'ShopPathNormalFix')
    common.set_feature_flag(browser, 'enable', 'CanonicalLink')
    common.set_feature_flag(browser, 'enable', 'NoIndexNoFollow')
    alternate_url = re.sub(r'/admin/', '', browser.current_url)

    for page in pages:
        # browsing on https secondary domain
        el = get_robots_tag(browser, alternate_url, page)
        assert el != None
        assert el.get_attribute('name') == "robots"
        assert el.get_attribute('content') == "noindex, nofollow"

        # browsing on http secondary domain
        el = get_robots_tag(browser, re.sub(r'\w+:', 'http:', alternate_url), page)
        assert el != None
        assert el.get_attribute('name') == "robots"
        assert el.get_attribute('content') == "noindex, nofollow"
