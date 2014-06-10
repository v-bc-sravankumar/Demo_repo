""" Helper methods for mobile web app"""


from lib.ui_lib import *
from fixtures.mobile_web import *


class MobileWebClass(CommonMethods, MobileWebData):

    def mobile_web_login_using_predefined_credentials(self, browser):
        """Logs into the mobile web app using credentials in MobileWebData"""
        self.mobile_web_login(browser, self.mobile_store_url, self.mobile_store_email, self.mobile_store_password)

    def mobile_web_login_using_env_credentials(self, browser, url, email, password):
        """Logs into the mobile web app using credentials in MobileWebData"""
        self.mobile_web_login(browser, url, email, password)

    def goto_mobile_menu(self, browser):
        """Goes to mobile menu. Handy function for after login use."""
        try:
            self.find_element_by_css_selector(".left.icon-reorder.back")
        except:
            try:
                self.find_element_by_css_selector(".icon.icon-logout")
            except:
                self.find_element_by_css_selector(".dashboard").click()

        self.find_element_by_css_selector(".icon.icon-logout")

    def goto_mobile_orders(self, browser):
        """Goes to mobile web app's orders page"""
        self.find_element_by_css_selector('.orders').click()
        self.find_element_by_css_selector('.search')

    def find_order_by_no(self, browser, order_no):
        """Searches order by number in mobile web app"""
        self.find_element_by_css_selector('.icon-search').clear()
        self.find_element_by_css_selector('.icon-search').send_keys(order_no)
        self.find_element_by_css_selector('.icon-search').send_keys(Keys.RETURN)
        order_no = '#'+str(order_no)
        self.find_element_by_css_selector('.order-time').click()

    def mobile_web_login(self, browser, url, email, password):
        """Logs into the mobile web app using credentials in MobileWebData"""
        self.get(self.mobile_staging_url)
        try:
            self.find_element_by_css_selector("#url").clear()
            self.find_element_by_css_selector("#url").send_keys(url)
            self.find_element_by_css_selector("#username").clear()
            self.find_element_by_css_selector("#username").send_keys(email)
            self.find_element_by_css_selector("#password").clear()
            self.find_element_by_css_selector("#password").send_keys(password)
            self.find_element_by_css_selector(".blue-button").click()
            self.find_element_by_css_selector(".left.icon-reorder.back").click()
            self.find_element_by_css_selector(".dashboard")
        except:
            self.find_element_by_css_selector(".left.icon-reorder.back").click()  # In case already logged in
