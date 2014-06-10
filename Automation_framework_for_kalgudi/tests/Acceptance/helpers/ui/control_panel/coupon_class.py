from lib.ui_lib import *

class CouponClass(CommonMethods):
    # CRUD Coupons
    def create_coupon_codes(self, browser, couponcode, couponname, coupontype, couponamount, min_purchase):
        element = self.wait_until_element_present('couponcode', 'ID')
        element.clear()
        element.send_keys(couponcode)
        element = self.wait_until_element_present('couponname', 'ID')
        element.clear()
        element.send_keys(couponname)
        browser.execute_script("$('#" + coupontype + "').prop(\"checked\",true)")
        element = self.find_element_by_id('couponamount')
        element.clear()
        element.send_keys(couponamount)
        element = self.find_element_by_id('couponminpurchase')
        element.clear()
        element.send_keys(min_purchase)
        self.wait_until_element_present("//a[contains(.,'Sale')]/ins", "XPATH").click()
        self.find_element_by_name('SaveButton1').click()

    def update_coupon_codes(self, browser, couponname, updatecouponname, min_purchase):
        self.edit_without_search(browser, couponname)
        element = self.wait_until_element_present('couponname', 'ID')
        element.clear()
        element.send_keys(updatecouponname)
        element = self.wait_until_element_present('couponminpurchase', 'ID')
        element.clear()
        element.send_keys(min_purchase)
        self.find_element_by_name('SaveButton1').click()

    def delete_coupon_codes(self, browser, couponname):
        element = self.wait_until_element_present('IndexDeleteButton', 'ID')
        self.execute_script("$('tr:contains(" + couponname + ") td:nth-child(1) [type=\"checkbox\"]').prop(\"checked\",true)")
        element.click()
        self.execute_script("$('.btn-primary').last().click()")
