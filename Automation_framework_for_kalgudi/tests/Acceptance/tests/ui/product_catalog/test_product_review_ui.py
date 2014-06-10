from lib.ui_lib import *



faker = Factory.create()
HEADLINE = faker.word()
REVIEW = CommonMethods.generate_random_string()
EMAIL = faker.email()

UPDATE_HEADLINE = faker.word()
UPDATE_REVIEW = CommonMethods.generate_random_string()
AUTHOR = CommonMethods.generate_random_string()

#******************************************************************
# Description:Verify Product Review option
#******************************************************************

def test_productreview_on_storefront(browser, url, email, password):
    common = CommonMethods(browser)
    common.go_to_admin(browser, url, email, password)
    common.disable_captcha_onlycustomer(browser)
    # Open Product url
    browser.get(urlparse.urljoin(url, 'anna-bright-single-bangles'))
    # Write review for the product
    try:
        browser.find_element_by_css_selector("#ProductReviews > h2").click()
    except WebDriverException as e:
        if "Click succeeded but Load Failed" in e.msg:
            pass
    browser.execute_script("$('#ProductReviews').find('img').trigger('click')")
    browser.find_element_by_xpath('//div[@class = "prodAccordionContent"]/a').click()
    element = common.wait_until_element_present('revtitle', 'ID', time=30)
    element.clear()
    element.send_keys(HEADLINE)
    common.select_dropdown_value(browser, 'revrating', '5 stars (best)')
    element = common.wait_until_element_present('revtext', 'ID', time=30)
    element.clear()
    element.send_keys(REVIEW)
    try:
        browser.find_element_by_xpath("//input[contains(@value,'Save My Review')]").click()
    except:
        browser.execute_script("$('.Submit').find('input').trigger('click')")
    common.wait_until_element_present('SuccessMessage', 'CLASS')
    admin_url = urlparse.urljoin(url, 'admin')
    browser.get(admin_url)
    assert 'pending product review' in browser.find_element_by_xpath('//section[@class = "panel-simple"]').text

def search_review(browser, title):
    common = CommonMethods(browser)
    e = browser.find_element_by_id('search-query')
    e.send_keys(title)
    browser.find_element_by_xpath('//button[@class="btn btn-secondary"]').click()
    try:
        element = common.wait_until_element_present('.alert.alert-error>p', 'CSS_SELECTOR')
        browser.find_element_by_id('search-query').clear()
        browser.find_element_by_xpath('//button[@class="btn btn-secondary"]').click()
    except Exception:
        pass

def test_productsreview_approval(browser, url, email, password):
    common = CommonMethods(browser)
    # Approve pending product review
    browser.find_element_by_link_text('Products').click()
    element = common.wait_until_element_present('Product Reviews', 'LINK')
    element.click()
    search_review(browser, HEADLINE)
    browser.execute_script("$('#IndexGrid').find('td:contains(" + HEADLINE + ")').parent('tr').find('input').attr('checked','checked')")
    browser.find_element_by_xpath('//button[text()="Approve Selected"]').click()
    common.verify_and_assert_success_message(browser, "The selected reviews have been approved successfully.", ".alert-success")

def test_productreview_edit(browser, url, email, password):
    common = CommonMethods(browser)
    # Edit the produtc review
    browser.execute_script("window.location = $('#IndexGrid').find('td:contains(" + HEADLINE + ")').parent('tr').find('.panel-inline').find('li:contains(Edit)').find('a').attr('href')")
    element = common.wait_until_element_present('//input[@name="revtitle"]', 'XPATH')
    element.clear()
    element.send_keys(UPDATE_HEADLINE)
    browser.find_element_by_xpath('//textarea[@name="revtext"]').clear()
    browser.find_element_by_xpath('//textarea[@name="revtext"]').send_keys(UPDATE_REVIEW)
    browser.find_element_by_xpath('//input[@name="revfromname"]').send_keys(AUTHOR)
    browser.execute_script("$('.Field150').find('option:contains(Pending)').attr('selected','selected')")
    browser.execute_script("$('.Field150').find('option:contains(Poor (1 Star))').attr('selected','selected')")
    try:
        browser.find_element_by_xpath('//input[@value="Save"]').click()
    except WebDriverException as e:
        if "Click succeeded but Load Failed" in e.msg:
            pass
    common.verify_and_assert_success_message(browser, "The selected review has been updated successfully.", ".alert-success")

def test_productreview_disapprove_delete(browser, url, email, password):
    common = CommonMethods(browser)
    # Disapporve pending product review in control panel
    search_review(browser, UPDATE_HEADLINE)
    browser.find_element_by_id('search-query').click()
    browser.execute_script("$('#IndexGrid').find('td:contains(" + UPDATE_HEADLINE + ")').parent('tr').find('input').attr('checked','checked')")
    browser.find_element_by_xpath('//button[text()="Disapprove Selected"]').click()
    common.verify_and_assert_success_message(browser, "The selected reviews have been disapproved successfully.", ".alert-success")
    # Delete disapporved product review
    browser.execute_script("$('.GridRow:first').find('td:eq(0)').find('input').attr('checked','checked')")
    browser.find_element_by_xpath('//button[text()="Delete Selected"]').click()
    try:
        alert = browser.switch_to_alert()
        alert.accept()
    except WebDriverException:
        browser.execute_script("window.confirm = function(){return true;}");
        browser.find_element_by_name('IndexDeleteButton').click()
    common.verify_and_assert_success_message(browser, "The selected reviews have been deleted successfully.", ".alert-success")
