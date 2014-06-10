from helpers.ui.control_panel.mobile_web_class import *


def test_mobile_web_login(browser, url, email, password):
    """Tests login on web version of mobile app"""
    mobile_web = MobileWebClass(browser)
    mobile_web.mobile_web_login_using_predefined_credentials(browser)
    mobile_web.goto_mobile_menu(browser)
