"""
Various helper methods and WebDriver wrappers
specifically targetting the Bigcommerce Control Panel.
"""

from includes import *
from selenium import webdriver
from selenium.common.exceptions import *
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select

class CommonMethods():
    def __init__(self, browser):
        """
        A constructor
        :param browser: an existing webdriver browser object
        :raise AttributeError: if browser is not a webdriver browser object
        """
        if not type(browser).__name__ == 'WebDriver':
            raise AttributeError("argument is not a valid selenium WebDriver object, got object of type '%s' " %
                                 type(browser).__name__)
        self.browser = browser

    @property
    def browser(self):
        """
        The webdriver browser object currently being used by this class
        :return: webdriver browser object
        """
        return self.browser

    def __getattr__(self, attr):
        """
        Called when an attribute lookup has not found the attribute in the usual places. This method checks if a
        webdriver browser object responds to 'attr' and calls it if it does. Throws an AttributeError if browser does
        not respond to 'attr'
        :param attr:
        :return: :raise AttributeError:
        """

        def make_interceptor(callable_method):
            def func(*args, **kwargs):
                return callable_method(*args, **kwargs)
            return func
        att = None
        if hasattr(self.browser, attr):
            att = getattr(self.browser, attr)
        if callable(att):
            return make_interceptor(att)
        else:
            raise AttributeError("The browser instance has no attribute '%s'" % attr)

    def _find(self, selector, plural=False, selector_type="id"):
        """
        A private convenience method that calls any of find_elements_* method depending on selector_type
        :param selector:
        :param plural:
        :param selector_type:
        :return: :raise TypeError:
        """
        selector_type = selector_type.lower()

        s_methods = {
            "id": "find_element_by_id",
            "css": "find_element_by_css_selector",
            "xpath": "find_element_by_xpath",
            "partial_link": "find_element_by_partial_link_text",
            "link": "find_element_by_link_text",
            "link_text": "find_element_by_link_text",
            "name": "find_element_by_name",
            "class": "find_element_by_class_name",
            "tag": "find_element_by_tag_name"
        }

        p_methods = {
            "id": "find_elements_by_id",
            "css": "find_elements_by_css_selector",
            "xpath": "find_elements_by_xpath",
            "partial_link": "find_elements_by_partial_link_text",
            "link": "find_elements_by_link_text",
            "link_text": "find_elements_by_link_text",
            "name": "find_elements_by_name",
            "class": "find_elements_by_class_name",
            "tag": "find_elements_by_tag_name"
        }

        try:
            method_name = s_methods[selector_type]
            if plural:
                method_name = p_methods[selector_type]

            attr = getattr(self.browser, method_name)(selector)
            return attr
        except KeyError:
            raise TypeError("Invalid selector type")

    def find(self, selector, iterator=0, plural=False):
        """
        A convenience method to find an element that matches a selector without having to specify a selector
        :param selector: can be an id, css, xpath, name, class, link text or partial link text
        :param iterator: for internal use don't use this argument
        :param plural: for internal use don;t use this argument
        :return: a webdriver element
        :raise webdriver.NoSuchElementException:
        """
        types = ["class", "css", "xpath", "id", "link_text", "name", "link", "partial_link", "tag"]

        if iterator >= len(types):
            raise NoSuchElementException()

        try:
            element = self._find(selector, plural, types[iterator])
            if type(element).__name__ == 'list' and len(element) == 0:
                raise NoSuchElementException()
            return element
        except:
            return self.find(selector, iterator + 1, plural)

    def find_all(self, selector):
        """
        A convenience method to find all elements that match a selector without having to specify a selector_type.
        :param selector: can be an id, css, xpath, name, class, link text or partial link text
        :return: list of elements that match selector
        """
        return self.find(selector, iterator=0, plural=True)

    def login(self, url,browser, username, password):
        browser.get(url)
        browser.find_element_by_id('username').clear()
        browser.find_element_by_id('username').send_keys(username)
        browser.find_element_by_id('password1').clear()
        browser.find_element_by_id('password1').send_keys(password)
        browser.find_element_by_id('login').click()

    def login_without_ana(self, browser, url, username, password):
        browser.find_element_by_id('username').send_keys(username)
        browser.find_element_by_id('password').send_keys(password)
        browser.find_element_by_id('password').send_keys(Keys.RETURN)  #Had a hard time finding the button!

    def check_for_slick(self):
        # Slick window comes up only the first time the store is created
        # the following code handles either the first time or the subsequent visits
        # to the store without failing.
        try:
            self.wait_until_element_present('slick-close', "CLASS_NAME", time=1).click()
            # Once Slick window closed, small popup comes as "Okay, I have got it"
            self.wait_until_element_present('//button[contains(text(),"Okay")]', "XPATH", time=1).click()
        except NoSuchElementException:
            pass

        except ElementNotVisibleException:
            pass

        except TimeoutException:
            pass

    # Login to Admin
    def go_to_admin(self, browser, url, username, password, check_for_login=False):
        """
        Navigate to control panel login page
        Enter valid login credentials & login
        """
        admin = urlparse.urljoin(url, 'admin')
        browser.get(admin)

        try:
            self.login(browser, username, password)
            self.check_for_slick()
        except NoSuchElementException:
            if check_for_login:
                raise Exception('login window not found')

            try:
                admin = urlparse.urljoin(url, 'admin/login')
                browser.get(admin)
                self.login_without_ana(browser, url, username, password)
            except:
                admin = urlparse.urljoin(url, 'admin')
                browser.get(admin)



    @staticmethod
    def generate_random_string(size=10, chars=string.ascii_uppercase + string.digits):
        return ''.join(random.choice(chars) for x in range(size))

    def wait_until_element_present(self, element, searchby, browser=None, time = 20, first = True):
        try:
            if not browser:
                browser=self.browser
            if searchby == "ID":
                WebDriverWait(browser, time).until(lambda s: self.find_element_by_id(element).is_displayed() and self.find_element_by_id(element))
                return self.find_element_by_id(element)
            elif searchby == "XPATH":
                WebDriverWait(browser, time).until(lambda s: self.find_element_by_xpath(element).is_displayed() and self.find_element_by_xpath(element))
                return self.find_element_by_xpath(element)
            elif searchby == "NAME":
                WebDriverWait(browser, time).until(lambda s: self.find_element_by_name(element).is_displayed() and self.find_element_by_name(element))
                return self.find_element_by_name(element)
            elif searchby == "LINK":
                WebDriverWait(browser, time).until(lambda s: self.find_element_by_link_text(element).is_displayed() and self.find_element_by_link_text(element))
                return self.find_element_by_link_text(element)
            elif searchby == "CSS_SELECTOR":
                WebDriverWait(browser, time).until(lambda s: self.find_element_by_css_selector(element).is_displayed() and self.find_element_by_css_selector(element))
                return self.find_element_by_css_selector(element)
            elif searchby == "CLASS_NAME":
                WebDriverWait(browser, time).until(lambda s: self.find_element_by_class_name(element).is_displayed() and self.find_element_by_class_name(element))
                return self.find_element_by_class_name(element)
            elif searchby == "TAGNAME":
                WebDriverWait(browser, time).until(lambda s: self.find_element_by_tag_name(element).is_displayed() and self.find_element_by_tag_name(element))
                return browser.find_element_by_tag_name(element)
            elif searchby == "JQUERY":
                WebDriverWait(browser, time).until(lambda s: self.execute_script(element))
        except TimeoutException:
            browser.save_screenshot('timeout.png')
            raise
        except StaleElementReferenceException:
            if first:
                return self.wait_until_element_present(browser, element, searchby, time, False)
            else:
                browser.save_screenshot('stale_element.png')
                raise

    def wait_until_element_invisible(self, element, searchby, browser=None, time = 30, first = True):
        try:
            if not browser:
                browser=self.browser
            if searchby == "ID":
                WebDriverWait(self.browser, 30).until(EC.invisibility_of_element_located((By.ID, element)))
                return True
            elif searchby == "XPATH":
                WebDriverWait(self.browser, 30).until(EC.invisibility_of_element_located((By.XPATH, element)))
                return True
            elif searchby == "NAME":
                WebDriverWait(self.browser, 30).until(EC.invisibility_of_element_located((By.NAME, element)))
                return True
            elif searchby == "LINK":
                WebDriverWait(self.browser, 30).until(EC.invisibility_of_element_located((By.LINK_TEXT, element)))
                return True
            elif searchby == "CSS_SELECTOR":
                WebDriverWait(self.browser, 30).until(EC.invisibility_of_element_located((By.CSS_SELECTOR, element)))
                return True
            elif searchby == "CLASS_NAME":
                WebDriverWait(self.browser, 30).until(EC.invisibility_of_element_located((By.CLASS_NAME, element)))
                return True
            elif searchby == "TAGNAME":
                WebDriverWait(self.browser, 30).until(EC.invisibility_of_element_located((By.TAG_NAME, element)))
                return True
        except TimeoutException:
            browser.save_screenshot('timeout.png')
            raise
        except StaleElementReferenceException:
            if first:
                return self.wait_until_element_invisible(browser, element, searchby, time, False)
            else:
                browser.save_screenshot('stale_element.png')
                raise


    def select_dropdown_value(self, browser, dropdown_id, option_text):
        dropdown_list = self.wait_until_element_present(dropdown_id, 'ID')
        for option in dropdown_list.find_elements_by_tag_name('option'):
            if option.text == option_text:
                option.click()

    def get_dropdown_selected_value(self, browser, element_id):
        return browser.execute_script("return $('#" + element_id + " option:selected').text()")

    def clear_field(self, browser, element_id):
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_id(element_id).is_displayed() and s.find_element_by_id(element_id))
        browser.find_element_by_id(element_id).clear()

    def verify_and_assert_success_message(self, browser, success_message, classname):
        # StaleElementReferenceException: Message: u'Element is no longer attached to the DOM' ; Stacktrace: Method fxdriver.cache.getElementAt threw an error in resource://fxdriver/modules/web_element_cache.js
        try:
            WebDriverWait(browser, 40).until(lambda s: success_message in s.find_element_by_css_selector(classname).text)
            assert success_message in browser.find_element_by_css_selector(classname).text
        except StaleElementReferenceException:
            WebDriverWait(browser, 40).until(lambda s: success_message in s.find_element_by_css_selector(classname).text)
            assert success_message in browser.find_element_by_css_selector(classname).text

    def select_dropdown_value_by_css(self, browser, dropdown_class, option_text):
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_css_selector(dropdown_class).is_displayed() and s.find_element_by_css_selector(dropdown_class))
        dropdown_list = browser.find_element_by_css_selector(dropdown_class)
        for option in dropdown_list.find_elements_by_tag_name('option'):
            if option.text == option_text:
                option.click()

    def search_and_edit(self, browser, element_by_xpath, search_keyword):
        element = self.wait_until_element_present(element_by_xpath, "XPATH")
        element.clear()
        element.send_keys(search_keyword)
        browser.find_element_by_css_selector('.action-divider .filter-button').click()
        # element_row = "return $('tr:contains(" + search_keyword + ")  button.dropdown-trigger')"
        element_row = "//td[contains(.,'" + search_keyword + "')]"
        # self.wait_until_element_present(browser, element_row, "JQUERY")
        self.wait_until_element_present(element_row, "XPATH")
        search_box = self.wait_until_element_present("//tr[contains(., '" + search_keyword + "')]", 'XPATH')
        # search_keyword.find_element_by_css_selector('.dropdown-trigger').click()
        element = self.wait_until_element_present(search_keyword, "LINK", time=240)
        element.click()

    def edit_without_search(self, browser, keyword_to_click):
        # element_row = "return $('tr:contains(" + keyword_to_click + ")  button.dropdown-trigger')"
        element_row = "//td[contains(.,'" + keyword_to_click + "')]"
        self.wait_until_element_present(keyword_to_click, "xpath")
        browser.find_element_by_xpath("//tr[contains(.,'" + keyword_to_click + "')]").find_element_by_css_selector('.dropdown-trigger').click()
        try:
            element = self.wait_until_element_present("Edit", "LINK")
            element.click()
        except TimeoutException:
            browser.find_element_by_link_text(keyword_to_click).click()
        except NoSuchElementException:
            browser.find_element_by_link_text(keyword_to_click).click()
        except StaleElementReferenceException:
            browser.find_element_by_link_text(keyword_to_click).click()

    def set_feature_flag(self, browser, status, feature):
        admin = browser.current_url
        browser.get(urlparse.urljoin(browser.current_url, '/admin/index.php?ToDo=' + status.lower() + '&feature=' + feature))
        assert browser.page_source.find(status)
        browser.get(admin)

    def get_dropdown_values(self, browser, dropdown_id):
        WebDriverWait(browser, 30).until(lambda s: s.find_element_by_id(dropdown_id).is_displayed() and s.find_element_by_id(dropdown_id))
        dropdown_list = browser.find_element_by_id(dropdown_id)
        option_text = ""
        for option in dropdown_list.find_elements_by_tag_name('option'):
            option_text = option_text + option.text + ' '

        return option_text

    def element_exists(self, selector, browser=None, search_by="ID"):
        try:
            self.wait_until_element_present(selector, search_by, browser)
            return True
        except (NoSuchElementException, Exception):
            return False

    def verify_errors(self, browser, error_text, classname):
        element = self.wait_until_element_present(classname, 'CSS_SELECTOR')
        error_message = element.text
        assert error_text in error_message

    def search_page_source_with_keywords(self, browser, url, keywords):
        referrer_url = browser.current_url
        browser.get(url)
        html_source = browser.page_source
        for keyword in keywords:
            assert keyword in html_source
        browser.get(referrer_url)

    def search_page_source_keywords_absent(self, browser, url, keywords):
        referrer_url = browser.current_url
        browser.get(url)
        html_source = browser.page_source
        for keyword in keywords:
            assert keyword not in html_source
        browser.get(referrer_url)

#       def enter_text_by_id(self,browser, element_id, value):
#               gts_account_id_field = browser.find_element_by_id(element_id)
#               gts_account_id_field.clear()
#               gts_account_id_field.send_keys(value)

    def upgrade_staging_plan(self,browser):
        self.find('Billing').click()
        self.find('Upgrade Account').click()
        browser.switch_to_window(browser.window_handles[-1])
        self.find('icon-smlarrow-next').click()
        self.find_element_by_css_selector('.plan-diamond .form-submit').click()
        self.find_element_by_xpath("//label[text()='Mastercard']").click()
        self.find('cardNumber').send_keys('5425233430109903')
        self.find('nameOnCard').send_keys('Test Name')
        self.find_element_by_css_selector("#cc-expiry-month option[value='01']").click()
        self.find_element_by_css_selector("#cc-expiry-year option[value='16']").click()
        self.find('cvv2').send_keys('123')
        self.find('submitPayment').click()
        assert "Plan Upgrade Complete" in self.find_element_by_css_selector('.CCWide').text


    def enable_google_trusted_store(self, browser, url, gts_account_id=100, gts_mc_account_id=200, gts_est_shipping_days=5):
        browser.find_element_by_link_text('Marketing').click()
        browser.find_element_by_link_text('Google Trusted Stores NEW').click()
        #Toggle on/off
        element=self.find('#gts_enabled')
        try:
            self.find('.provider-action i').click()
        except:
            self.execute_script("$('#gts_enabled').selected(true)")
            self.execute_script("$('#gts_enabled').click()")

        try:
            self.find_element_by_xpath("//button[text()='Next']").click()
        except:
            pass
        shipping_feed=self.find_element_by_xpath("//li[contains(., 'Shipping feed')]/input").get_attribute('value')
        cancellation_feed=self.find_element_by_xpath("//li[contains(., 'Cancellation feed')]/input").get_attribute('value')
        element=self.find_element_by_xpath("//input[contains(@ng-model,'shipping_days')]")
        element.clear()
        element.send_keys(gts_est_shipping_days)
        element=self.find_element_by_xpath("//input[contains(@ng-model, 'gts_account_id')]")
        element.clear()
        element.send_keys(gts_account_id)
        element=self.find_element_by_xpath("//input[contains(@ng-model, 'merchant_center_account_id')]")
        element.clear()
        element.send_keys(gts_mc_account_id)
        self.find('.dialog-medium button.btn-primary').click()
        assert "Google Trusted Stores" in self.find('tbody').text
        assert self.find('.provider-action i')
        return str(shipping_feed), str(cancellation_feed)


    # Validate fields in currency crud
    def validate_field(self, browser, assert_text):
        try:
            alert = browser.switch_to_alert()
            # TODO: This needs to be fixed. what are we asserting here?
            assert assert_text
            alert.accept()
        except WebDriverException:
            # TODO: This needs to be fixed. what are we asserting here?
            assert assert_text
            browser.execute_script("window.confirm = function(){return true;}")

    def get_alert_text(self):
        alert = self.browser.switch_to_alert()
        text = alert.text
        alert.accept()
        return text

    # Navigate to all the pages to click the checkbox
    def navigate_using_paginator(self, browser, keyword, page=10):
        i = 0
        while not self.element_exists("//tr[contains(.,'" + keyword + "')]", browser, "XPATH") and i <= page:
            if self.element_exists('//a[@rel="next"]', browser, "XPATH"):
                browser.find_element_by_css_selector('.action-bar-bottom .pagination').find_element_by_xpath('//a[@rel="next"]').click()
                time.sleep(1)
            i = i + 1

    # Following method used for disable captcha
    def disable_captcha_onlycustomer(self, browser):
        browser.find_element_by_link_text("Setup & Tools").click()
        browser.find_element_by_link_text('Store settings').click()
        element = self.find_element_by_id('tab-DisplaySettingsTab')
        element.click()
        if browser.find_element_by_id('CaptchaEnabled').is_selected():
            browser.find_element_by_xpath('//label[@for="CaptchaEnabled"]').click()
        browser.find_element_by_name('SubmitButton').click()
        self.verify_and_assert_success_message(browser, "The modified settings have been saved successfully.", ".alert-success")

        browser.find_element_by_link_text('Setup & Tools').click()
        browser.find_element_by_link_text('Comments').click()
        element = self.wait_until_element_present('Built-in', "LINK")
        element.click()
        if browser.find_element_by_id('comments_builtincomments_customers_only').is_selected():
            browser.find_element_by_xpath('//label[@for="comments_builtincomments_customers_only"]').click()
        browser.find_element_by_xpath('//button[text()="Save"]').click()
        self.verify_and_assert_success_message(browser, "The comment system settings were saved successfully.", ".alert-success")
        browser.find_element_by_link_text('Home').click()

    def enter_text(self, selector, value, browser=None, search_by="ID"):
        element = self.find_element_by_id(selector)
        element.clear()
        element.send_keys(value)
