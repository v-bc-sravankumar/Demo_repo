from lib.ui_lib import *
from fixtures.social import *
from fixtures.checkout import *


class SocialShopClass(CommonMethods,SocialData, CheckoutData):
    
    def facebook_login(self):
        self.get(self.social_shop_url)
        try:
            self.wait_until_element_present(".uiButtonConfirm")
            self.find_element_by_css_selector(".uiButtonConfirm input").click()
        except:
            self.find_element_by_css_selector("#login_form")
        try:
            for item in self.facebooklogin:
                element=self.facebooklogin[item]['Element']
                value=self.facebooklogin[item]['Value']
                self.find_element_by_css_selector(element).clear()
                self.find_element_by_css_selector(element).send_keys(value)
            self.find_element_by_css_selector("#loginbutton input").click()
            iframe = self.find_elements_by_tag_name('iframe')[0]
            self.switch_to_frame(iframe)            
            assert "Bigcommerce SocialShop" in self.find_element_by_css_selector("#formContent").text
            
        except:
            iframe = self.find_elements_by_tag_name('iframe')[0]
            self.switch_to_frame(iframe)
            assert "Bigcommerce SocialShop" in self.find_element_by_css_selector("#formContent").text
    
    def update_social_settings(self,url):
        try:
            self.find_element_by_link_text("Change your SocialShop Settings").click()
            #clear store url
            element=self.find_element_by_css_selector("#store_url")
            element.clear()
            element.send_keys(url)
            # press save
            self.find_element_by_css_selector("#saveButton").click()
        except:
            return False
        assert "Saved successfully" in self.find_element_by_css_selector("#formContent").text


    def open_social_store(self):
        self.get(self.social_shop_url)
        try:
            iframe = self.find_elements_by_tag_name('iframe')[0]
            self.switch_to_frame(iframe)
            self.find_element_by_link_text("Go to your Store").click()
        except:
            return False

     
    def social_guest_checkout(self, browser, url, country_data):
        element = self.wait_until_element_present('#checkout_type_guest', "CSS_SELECTOR")
        element.click()
        # Checkout as Guest
        browser.find_element_by_id('CreateAccountButton').click()
        EMAIL = "test.engineer" + self.generate_random_string() + "@bigcommerce.com"
        element = self.wait_until_element_present('FormField_1', "ID")
        element.send_keys(EMAIL)
        for item in country_data:
            if item=="Country" or item=="State":
                self.find_element_by_css_selector(country_data['Country']['Element']).click()
                element=self.wait_until_element_present(country_data['State']['Element'], "CSS_SELECTOR")
                element.click()

            else:
                element=country_data[item]['Element']
                value=country_data[item]['Value']
                self.find_element_by_id(element).clear()
                self.find_element_by_id(element).send_keys(value)
            
        browser.find_element_by_css_selector('.AddBillingAddress input.billingButton').click()
