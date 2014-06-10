from helpers.ui.control_panel.discountrules_class import *


DISCOUNT_FREE_SHIPPING_OVER_100 = "Free shipping on orders over $X" + CommonMethods.generate_random_string()

# Following discount rules automation script needs to be write.
# DISCOUNT_BUY_X_ITME_GET_FREE_SHIPPING = "Buy X items get free shipping" + generate_random_string()
# DISCOUNT_10_WHEN_ORDER_OVER_50 = "Get an $X discount on orders of $Y or more" + generate_random_string()
# DOLLAR_DISCOUNT_FOR_REPEAT_CUSTOMERS = "$X discount for repeat customers" + generate_random_string()
# PERCENTAGE_DISCOUNT_FOR_REPEAT_CUSTOMERS = "percentage discount for repeat customers" + generate_random_string()


def test_create_discount_rule_free_shipping_over_100(browser, url, email, password):
    discount = DiscountClass(browser)
    discount.go_to_admin(browser, url, email, password)
    discount.create_discount_rule(browser, DISCOUNT_FREE_SHIPPING_OVER_100)

def test_edit_discount_rule_free_shipping_over_100(browser, url, email, password):
    discount = DiscountClass(browser)
    discount.edit_discount_rule(browser, DISCOUNT_FREE_SHIPPING_OVER_100)

def test_delete_discount_rule_free_shipping_over_100(browser, url, email, password):
    discount = DiscountClass(browser)
    discount.delete_discount_rule(browser, DISCOUNT_FREE_SHIPPING_OVER_100)

    try:
        discount.verify_and_assert_success_message(browser, "No discounts have been created.", ".alert-success")
    except TimeoutException:
        discount.verify_and_assert_success_message(browser, "The selected discounts rules have been deleted successfully.", ".alert-success")
