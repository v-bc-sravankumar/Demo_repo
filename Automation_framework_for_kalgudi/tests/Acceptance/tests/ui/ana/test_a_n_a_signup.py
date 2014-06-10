from lib.ui_lib import *
from helpers.ui.store_front.a_n_a import *

URL='https://login-dev.bigcommerceapp.com'
USER_EMAIL = "bcjyothi437+" + str(randint(1,9999)) + CommonMethods.generate_random_string(5) + "@gmail.com"
USER_NAME = "bcjyothi" + CommonMethods.generate_random_string()
USER_PASSWORD = 'password1'
NEW_PASSWORD = str(randint(1,100)) + CommonMethods.generate_random_string()
EMAIL ='bcjyothi437@gmail.com'
PASSWORD ='password@'


def test_signup(browser):
    ana = AnA(browser)
    ana.sign_up(browser, URL, USER_NAME, USER_EMAIL)
    assert "Boom! Please check your mail." in browser.find_element_by_xpath('//div[@class= "text-center signup-confirmed"]').text


def test_activate_account(browser):
    pytest.skip("Skipping due to flakiness on Bamboo")
    ana = AnA(browser)
    ana.activate_account_from_mail(browser, URL, USER_EMAIL, USER_PASSWORD, EMAIL, PASSWORD)


def test_forgot_password(browser):
    pytest.skip("Skipping due to flakiness on Bamboo")
    ana = AnA(browser)
    ana.forgot_password(browser, URL, USER_EMAIL)


def test_reset_password(browser):
    pytest.skip("Skipping due to flakiness on Bamboo")
    ana = AnA(browser)
    ana.reset_password_from_mail(browser, EMAIL, PASSWORD, NEW_PASSWORD)


def test_lock_unlock_user_account(browser):
    pytest.skip("Skipping due to flakiness on Bamboo")
    ana = AnA(browser)
    ana.lock_unlock_user_account(browser, URL, USER_EMAIL, NEW_PASSWORD, EMAIL, PASSWORD)