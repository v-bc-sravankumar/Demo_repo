from lib.ui_lib import *

class DiscountClass(CommonMethods):

    def create_discount_rule(self, browser, name):

        browser.find_element_by_link_text('Marketing').click()
        browser.find_element_by_link_text('View Discount Rules').click()
        browser.find_element_by_link_text('Create a Discount Rule').click()
        browser.find_element_by_id('discountname').send_keys(name)
        browser.find_element_by_xpath("//label[contains(text(),'Free shipping on orders over $X')]").click()
        self.wait_until_element_present("amount", "ID")
        browser.find_element_by_id("amount").send_keys("100")
        browser.execute_script("$('#ShowFreeShippingMesgOnHomePage').prop(\"checked\",true)")
        browser.execute_script("$('#ShowFreeShippingMesgOnProductPage').prop(\"checked\",true)")
        browser.execute_script("$('#ShowFreeShippingMesgOnCartPage').prop(\"checked\",true)")
        browser.execute_script("$('#ShowFreeShippingMesgOnCheckoutPage').prop(\"checked\",true)")
        browser.find_element_by_name('SubmitButton1').click()
        self.verify_and_assert_success_message(browser, "The new discount rule has been created successfully.", ".alert-success")
    def edit_discount_rule(self, browser, name):
        self.edit_without_search(browser, name)
        element = self.wait_until_element_present('discountname', "ID")
        element.send_keys('Updated')
        browser.find_element_by_name('SubmitButton1').click()
        self.verify_and_assert_success_message(browser, "The selected discount has been updated successfully.", ".alert-success")


    def delete_discount_rule(self, browser, discount_rule_name):
        browser.execute_script("$('tr:contains(" + discount_rule_name + ") td:nth-child(1) [type=\"checkbox\"]').prop(\"checked\",true)")
        element = self.wait_until_element_present("IndexDeleteButton", "ID")
        element.click()
        browser.execute_script("$('.btn-primary').last().click()")
