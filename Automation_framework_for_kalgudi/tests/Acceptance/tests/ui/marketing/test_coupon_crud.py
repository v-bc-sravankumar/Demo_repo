from helpers.ui.control_panel.coupon_class import *


COUPON_CODE_DOLLAR_OFF_ORDER = "$TENOFF" + CommonMethods.generate_random_string()
NAME_DOLLAR_OFF_ORDER = CommonMethods.generate_random_string()
UPDATE_NAME_DOLLAR_OFF_ORDER = CommonMethods.generate_random_string()


COUPON_CODE_DOLLAR_OFF_EACH_ITEM = "$5PERITEM" + CommonMethods.generate_random_string()
NAME_CODE_DOLLAR_OFF_EACH_ITEM = CommonMethods.generate_random_string()
UPDATE_NAME_CODE_DOLLAR_OFF_EACH_ITEM = CommonMethods.generate_random_string()


COUPON_CODE_PERCENT_OFF_EACH_ITEM = "10PERCENTOFF" + CommonMethods.generate_random_string()
NAME_CODE_PERCENT_OFF_EACH_ITEM = CommonMethods.generate_random_string()
UPDATE_NAME_CODE_PERCENT_OFF_EACH_ITEM = CommonMethods.generate_random_string()

COUPON_CODE_DOLLAR_OFF_SHIPPING = "$TENOFFSHIPPING" + CommonMethods.generate_random_string()
NAME_DOLLAR_OFF_SHIPPING = CommonMethods.generate_random_string()
UPDATE_NAME_DOLLAR_OFF_SHIPPING = CommonMethods.generate_random_string()

COUPPON_CODE_FREE_SHIPPING = "$FREESHIPPING" + CommonMethods.generate_random_string()
NAME_FREE_SHIPPING = CommonMethods.generate_random_string()
UPDATE_NAME_FREE_SHIPPING = CommonMethods.generate_random_string()


# Dollar amount off the order total
def test_create_coupon_code_DOLLAR_OFF_ORDER(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.go_to_admin(browser, url, email, password)
    browser.find_element_by_link_text('Marketing').click()
    browser.find_element_by_link_text('View Coupon Codes').click()
    browser.find_element_by_link_text('Create a Coupon Code').click()
    coupon.create_coupon_codes(browser, COUPON_CODE_DOLLAR_OFF_ORDER, NAME_DOLLAR_OFF_ORDER, 'coupontype-1', '10', '50')
    coupon.verify_and_assert_success_message(browser, "The new coupon code has been created successfully.", ".alert-success")

def test_edit_coupon_code_DOLLAR_OFF_ORDER(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.update_coupon_codes(browser, NAME_DOLLAR_OFF_ORDER, UPDATE_NAME_DOLLAR_OFF_ORDER, "100")
    coupon.verify_and_assert_success_message(browser, "The selected coupon has been updated successfully.", ".alert-success")

def test_delete_coupon_code_DOLLAR_OFF_ORDER(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.delete_coupon_codes(browser, UPDATE_NAME_DOLLAR_OFF_ORDER)
    coupon.verify_and_assert_success_message(browser, "The selected coupons have been deleted successfully.", ".alert-success")


# Dollar amount off each item in the order
def test_create_coupon_code_DOLLAR_OFF_EACH_ITEM(browser, url, email, password):
    coupon = CouponClass(browser)
    browser.find_element_by_link_text('Create a Coupon Code').click()
    coupon.create_coupon_codes(browser, COUPON_CODE_DOLLAR_OFF_EACH_ITEM, NAME_CODE_DOLLAR_OFF_EACH_ITEM, 'coupontype-2', '10', '50')
    coupon.verify_and_assert_success_message(browser, "The new coupon code has been created successfully.", ".alert-success")


def test_edit_coupon_code_DOLLAR_OFF_EACH_ITEM(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.update_coupon_codes(browser, NAME_CODE_DOLLAR_OFF_EACH_ITEM, UPDATE_NAME_CODE_DOLLAR_OFF_EACH_ITEM, "100")
    coupon.verify_and_assert_success_message(browser, "The selected coupon has been updated successfully.", ".alert-success")

def test_delete_coupon_code_DOLLAR_OFF_EACH_ITEM(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.delete_coupon_codes(browser, UPDATE_NAME_CODE_DOLLAR_OFF_EACH_ITEM)
    coupon.verify_and_assert_success_message(browser, "The selected coupons have been deleted successfully.", ".alert-success")


# Percentage off each item in the order
def test_create_coupon_code_PERCENT_OFF_EACH_ITEM(browser, url, email, password):
    coupon = CouponClass(browser)
    browser.find_element_by_link_text('Create a Coupon Code').click()
    coupon.create_coupon_codes(browser, COUPON_CODE_PERCENT_OFF_EACH_ITEM, NAME_CODE_PERCENT_OFF_EACH_ITEM, 'coupontype-3', '10', '50')
    coupon.verify_and_assert_success_message(browser, "The new coupon code has been created successfully.", ".alert-success")


def test_edit_coupon_code_PERCENT_OFF_EACH_ITEM(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.update_coupon_codes(browser, NAME_CODE_PERCENT_OFF_EACH_ITEM, UPDATE_NAME_CODE_PERCENT_OFF_EACH_ITEM, "100")
    coupon.verify_and_assert_success_message(browser, "The selected coupon has been updated successfully.", ".alert-success")

def test_delete_coupon_code_PERCENT_OFF_EACH_ITEM(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.delete_coupon_codes(browser, UPDATE_NAME_CODE_PERCENT_OFF_EACH_ITEM)
    coupon.verify_and_assert_success_message(browser, "The selected coupons have been deleted successfully.", ".alert-success")

# Dollar amount off the shipping total
def test_create_coupon_code_DOLLAR_OFF_SHIPPING(browser, url, email, password):
    coupon = CouponClass(browser)
    browser.find_element_by_link_text('Create a Coupon Code').click()
    coupon.create_coupon_codes(browser, COUPON_CODE_DOLLAR_OFF_SHIPPING, NAME_DOLLAR_OFF_SHIPPING, 'coupontype-4', '10', '50')
    coupon.verify_and_assert_success_message(browser, "The new coupon code has been created successfully.", ".alert-success")

def test_edit_coupon_code_DOLLAR_OFF_SHIPPING(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.update_coupon_codes(browser, NAME_DOLLAR_OFF_SHIPPING, UPDATE_NAME_DOLLAR_OFF_SHIPPING, "100")
    coupon.verify_and_assert_success_message(browser, "The selected coupon has been updated successfully.", ".alert-success")


def test_delete_coupon_code_DOLLAR_OFF_SHIPPING(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.delete_coupon_codes(browser, UPDATE_NAME_DOLLAR_OFF_SHIPPING)
    coupon.verify_and_assert_success_message(browser, "The selected coupons have been deleted successfully.", ".alert-success")


# Free shipping
def test_create_coupon_code_FREE_SHIPPING(browser, url, email, password):
    coupon = CouponClass(browser)
    browser.find_element_by_link_text('Create a Coupon Code').click()
    coupon.create_coupon_codes(browser, COUPPON_CODE_FREE_SHIPPING, NAME_FREE_SHIPPING, 'coupontype-5', '10', '50')
    coupon.verify_and_assert_success_message(browser, "The new coupon code has been created successfully.", ".alert-success")

def test_edit_coupon_code_FREE_SHIPPING(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.update_coupon_codes(browser, NAME_FREE_SHIPPING, UPDATE_NAME_FREE_SHIPPING, "100")
    coupon.verify_and_assert_success_message(browser, "The selected coupon has been updated successfully.", ".alert-success")

def test_delete_coupon_code_FREE_SHIPPING(browser, url, email, password):
    coupon = CouponClass(browser)
    coupon.delete_coupon_codes(browser, UPDATE_NAME_FREE_SHIPPING)
    coupon.verify_and_assert_success_message(browser, "The selected coupons have been deleted successfully.", ".alert-success")
