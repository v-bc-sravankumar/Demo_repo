from lib.ui_lib import *


class AnA(CommonMethods):

    def sign_up(self, browser, ana_url, ana_user, ana_email):
        browser.get(ana_url + '/signup/')
        element = self.wait_until_element_present('user_email', 'ID')
        element.send_keys(ana_email)
        self.find_element_by_id('user_username').send_keys(ana_user)
        self.find_element_by_xpath('//input[@value = "Sign up"]').click()

    def activate_account_from_mail(self, browser, ana_url, user_email, user_pwd, mail_id, mail_pwd):
        browser.get('https://www.gmail.com')
        self.find_element_by_id('Email').send_keys(mail_id)
        self.find_element_by_id('Passwd').send_keys(mail_pwd)
        self.find_element_by_id('signIn').click()
        self.wait_until_element_present('//span/b[text() = "Confirm your Bigcommerce account"]', 'XPATH', time=30).click()
        try:
            self.find('Confirm account').click()
        except:
            self.find_element_by_xpath('//div[@data-tooltip = "Show trimmed content"]').click()
            self.find('Click to confirm').click()
        browser.switch_to_window(browser.window_handles[1])
        element = self.wait_until_element_present('user_password', 'ID')
        element.send_keys(user_pwd)
        self.find("user_password_confirmation").send_keys(user_pwd)
        self.find_element_by_xpath('//input[@value = "Complete signup"]').click()
        assert "Your password has been set. You may now log in." in browser.find_element_by_xpath('//div[@class = "alert-box notice"]').text
        browser.close()
        browser.switch_to_window(browser.window_handles[0])

        # Login
        browser.get(ana_url)
        c = CommonMethods(browser)
        c.login(browser, user_email, user_pwd)
        self.find('Log out').click()


    def forgot_password(self, browser, ana_url, ana_email):
        browser.get(ana_url)
        self.find_element_by_link_text('Need help?').click()
        self.find_element_by_id('user_email').clear()
        self.find_element_by_id('user_email').send_keys(ana_email)
        self.find_element_by_name('commit').click()
        self.wait_until_element_present('//div[@class = "alert-box notice"]', 'XPATH')
        assert "Within a few minutes, you will receive an email with instructions on how to reset your password." in browser.find_element_by_xpath('//div[@class = "alert-box notice"]').text


    #reset password
    def reset_password_from_mail(self, browser, mail_id, mail_pwd, reset_pwd):
        browser.get('https://www.gmail.com')
        element = self.wait_until_element_present('//span/b[text() = "Instructions for resetting your Bigcommerce account password"]', 'XPATH')
        element.click()
        try:
            self.find('Change password').click()
        except:
            element = self.wait_until_element_present('//div[@data-tooltip = "Show trimmed content"]', 'XPATH')
            element.click()
            self.find('Change password').click()
        browser.switch_to_window(browser.window_handles[1])
        self.find('user_password').send_keys(reset_pwd)
        self.find('user_password_confirmation').send_keys(reset_pwd)
        self.find('commit').click()
        assert "Your password was changed successfully. You are now signed in." in browser.find_element_by_xpath('//div[@class = "alert-box notice"]').text
        self.find('Log out').click()
        browser.close()
        browser.switch_to_window(browser.window_handles[0])


# account locked after 6 tries
# Your account is locked.

    def lock_unlock_user_account(self, browser, ana_url, user_email, user_pwd, mail_id, mail_pwd):
        browser.get(ana_url)
        c = CommonMethods(browser)
        i = 0
        while i < 6:
            i += 1
            try:
                c.login(browser, user_email, 'incorrect_password')
            except NoSuchElementException:
                pass

        assert "Your account is locked" in browser.find_element_by_xpath('//div[@class = "alert-box alert"]').text
        browser.get('https://www.gmail.com')
        self.find_element_by_xpath('//span/b[text() = "Instructions for unlocking your Bigcommerce account"]').click()
        try:
            self.find('Unlock account').click()
        except:
            self.find_element_by_xpath('//div[@data-tooltip = "Show trimmed content"]').click()
            self.find('Unlock account').click()

        browser.switch_to_window(browser.window_handles[1])

        if c.element_exists('Back to login'):
            self.find('Back to login').click()
        c.login(browser, user_email, user_pwd)
        self.find('Log out').click()
        browser.close()
        browser.switch_to_window(browser.window_handles[0])