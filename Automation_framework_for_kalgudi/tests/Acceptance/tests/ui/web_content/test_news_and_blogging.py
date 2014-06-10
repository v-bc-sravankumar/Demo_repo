from lib.ui_lib import *

#News and Blogging tests have been merged into 1 file to avoid problems with concurrent execution,
#They cannot be executed at the same time because they share a feature flag)

NEWS_TITLE = CommonMethods.generate_random_string()
UPDATED_NEWS_TITLE = CommonMethods.generate_random_string()

POST_TITLE = CommonMethods.generate_random_string()
UPDATED_POST_TITLE = CommonMethods.generate_random_string()
SETTINGS_BLOG_TITLE = CommonMethods.generate_random_string()


#News Tests - Functionality is deprecated
@pytest.mark.skipif("True")
def test_create_newsitem(browser, url, email, password):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    if 'bigcommerce.com' in url:
        pytest.skip("Cannot disable feature Flag on Production store")
    else:
        common.set_feature_flag(browser, 'disable', 'Blogging')

    browser.find_element_by_link_text('Web Content').click()
    browser.find_element_by_link_text('Add a News Item').click()
    browser.find_element_by_id('newstitle').clear()
    browser.find_element_by_id('newstitle').send_keys(NEWS_TITLE)
    browser.find_element_by_id('newstitle').click()
    browser.find_element_by_id('news_custom_url').click()
    browser.find_element_by_id('newssearchkeywords').send_keys("NEWS")
    browser.find_element_by_xpath('//button[text()="Save"]').click()
    common.verify_and_assert_success_message(browser, "The news item has been added successfully.", ".alert-success")

@pytest.mark.skipif("True")
def test_edit_newsitem(browser, url, email, password):
    common = CommonMethods(browser)
    if 'bigcommerce.com' in url:
        pytest.skip("Cannot disable feature Flag on Production store")
    else:
        common.edit_without_search(browser, NEWS_TITLE)
        browser.find_element_by_id('newstitle').clear()
        browser.find_element_by_id('newstitle').send_keys(UPDATED_NEWS_TITLE)
        browser.find_element_by_id('customUrlGenerateButton').click()
        browser.find_element_by_id('news_custom_url').clear()
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_id('news_custom_url').get_attribute('value').replace('/','') == s.find_element_by_id('newstitle').get_attribute('value').lower())
        browser.find_element_by_xpath('//button[text()="Save"]').click()
        common.verify_and_assert_success_message(browser, "The selected news item has been updated successfully.", ".alert-success")

@pytest.mark.skipif("True")
def test_verify_newsitem_on_storefront(browser, url, email, password):
    if 'bigcommerce.com' in url:
        pytest.skip("Cannot disable feature Flag on Production store")
    else:
        browser.get(urlparse.urljoin(url, UPDATED_NEWS_TITLE))
        browser.execute_script("$('span:contains(" + UPDATED_NEWS_TITLE + ")').first().click();")
        assert UPDATED_NEWS_TITLE.upper()  == browser.execute_script("return $('h1:contains(" + UPDATED_NEWS_TITLE + ")').text();").upper()

@pytest.mark.skipif("True")
def test_delete_newsitem(browser, url, email, password):
    common = CommonMethods(browser)
    if 'bigcommerce.com' in url:
        pytest.skip("Cannot disable feature Flag on Production store")
    else:
        browser.get(urlparse.urljoin(url, 'admin'))
        browser.find_element_by_link_text('Web Content').click()
        browser.find_element_by_link_text('View News Items').click()
        browser.execute_script("$('tr:contains(" + UPDATED_NEWS_TITLE + ") td:nth-child(1) [type=\"checkbox\"]').prop('checked',true)")
        browser.execute_script("$('.list-horizontal button#IndexDeleteButton').click();")
        browser.find_element_by_xpath('//button[text()="Ok"]').click()
        common.verify_and_assert_success_message(browser, "The selected news items have been deleted successfully.", ".alert-success")
        assert browser.execute_script("return $('td:contains(" + UPDATED_NEWS_TITLE + ")').text()") == ""


#Blogging Tests
def test_create_blog_post(browser, url, email, password):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    if 'bigcommerce.com' in url:
        pass
    else:
        common.set_feature_flag(browser, 'enable', 'Blogging')
    browser.find_element_by_link_text('Web Content').click()
    browser.find_element_by_link_text('Blog').click()
    common.wait_until_element_present('blog_new_post', 'ID').click()
    element = common.wait_until_element_present('post-title', 'ID')
    element.click()
    element.clear()
    element.send_keys(POST_TITLE)
    element.click()
    browser.find_element_by_id('post-custom-url').click()
    browser.find_element_by_id('action-publish').click()
    common.verify_and_assert_success_message(browser, "Published. Nicely done. Check it out in your store", ".alert-success")

def test_edit_blog_post(browser, url, email, password):
    common = CommonMethods(browser)
    browser.find_element_by_link_text(POST_TITLE).click()
    element = common.wait_until_element_present('post-title', 'ID')
    element.click()
    element.clear()
    element.send_keys(UPDATED_POST_TITLE)
    browser.find_element_by_id('post-title').click()
    browser.find_element_by_id('post-custom-url').click()
    browser.find_element_by_id('action-publish').click()
    common.verify_and_assert_success_message(browser, "Published. Nicely done. Check it out in your store", ".alert-success")

def test_verify_blog_post_on_storefront(browser, url, email, password):
    common = CommonMethods(browser)
    browser.get(urlparse.urljoin(url, "blog"))
    common.wait_until_element_present(UPDATED_POST_TITLE.upper(), 'LINK').click()
    assert UPDATED_POST_TITLE.upper() == browser.execute_script("return $('h1:contains(" + UPDATED_POST_TITLE.upper() + ")').text();").upper()


def test_validate_default_blog_settings(browser, url, email, password):
    pytest.skip("Script setup is modified by other script prior execution")
    common = CommonMethods(browser)
    browser.get(urlparse.urljoin(url, "admin"))
    browser.find_element_by_link_text('Web Content').click()
    browser.find_element_by_link_text('Blog').click()
    browser.find_element_by_id('blog_settings').click()
    assert browser.execute_script("return $('#blog_title').val()") == 'Blog'
    assert browser.execute_script("return $('#blog_url').val()") == '/blog/'
    assert browser.execute_script("return $('#blog_posts_url').val()") == '/blog/{title}/'

def test_edit_blog_settings(browser, url, email, password):
    common = CommonMethods(browser)
    browser.get(urlparse.urljoin(url, "admin"))
    browser.find_element_by_link_text('Web Content').click()
    browser.find_element_by_link_text('Blog').click()
    browser.find_element_by_id('blog_settings').click()
    browser.find_element_by_id('blog_title').clear()
    browser.find_element_by_id('blog_title').send_keys(SETTINGS_BLOG_TITLE)
    browser.find_element_by_id('blog_url').click()
    common.wait_until_element_present('//button[text()="Save Settings"]', 'XPATH').click()
    common.wait_until_element_present('blog_settings', 'ID').click()
    assert browser.execute_script("return $('#blog_title').val()") == SETTINGS_BLOG_TITLE

# Thereis an issue with blogpost url
@pytest.mark.skipif("True")
def test_verify_blog_settings_on_storefront(browser, url, email, password):
    common = CommonMethods(browser)
    browser.get(urlparse.urljoin(url, SETTINGS_BLOG_TITLE.lower()))
    assert SETTINGS_BLOG_TITLE.upper() in str(common.find('h1.PostTitle')).upper()

def test_delete_blog_post(browser, url, email, password):
    common = CommonMethods(browser)
    browser.get(urlparse.urljoin(url, "admin"))
    browser.find_element_by_link_text('Web Content').click()
    browser.find_element_by_link_text('Blog').click()
    browser.execute_script("$('tr:contains(" + UPDATED_POST_TITLE + ")').find('.dropdown-trigger').trigger('click');")
    browser.execute_script("window.confirm = function(){return true;}")
    try:
        browser.find_element_by_link_text('Delete').click()
        alert = browser.switch_to_alert()
        alert.accept()
    except WebDriverException:
        pass
        #browser.execute_script("window.confirm = function(){return true;}")
        browser.execute_script("confirm()")

    assert UPDATED_POST_TITLE not in str(common.find_element_by_css_selector('tbody').text)
