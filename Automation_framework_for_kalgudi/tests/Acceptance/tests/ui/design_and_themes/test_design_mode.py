from lib.ui_lib import *
from urlparse import urlparse
import re


def test_no_token_for_design_mode(browser, url, email, password):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    els = browser.find_elements_by_css_selector('div#brand a')
    for el in els:
        if el.text == 'Design':
            el.click()
            break

    browser.find_element_by_link_text('More').click()
    browser.find_element_by_link_text('Design Mode').click()

    # Launch Design Mode in a new browser tab, and switch to it.
    browser.find_element_by_xpath('//*[@id="div2"]/p[2]/button').click()
    browser.switch_to_window(browser.window_handles[-1]) # Last tab in list.

    # Regression: check we're not passing the Design Mode token around
    # in the query string anymore.
    url_parts = urlparse(browser.current_url)
    assert url_parts.query == '' # No token in query
    assert re.match(r'^/?$', url_parts.path) # Storefront root
