from helpers.ui.control_panel.giftwrapping_class import *

WRAP_NAME = CommonMethods.generate_random_string()
UPDATED_WRAP_NAME = CommonMethods.generate_random_string()

@pytest.mark.skipif("True")
def test_create_gift_wrapping(browser, url, email, password):
    gift = GiftWrappingClass(browser)
    gift.go_to_admin(browser, url, email, password)
    gift.create_giftwrapping(browser, WRAP_NAME)

@pytest.mark.skipif("True")
def test_edit_gift_wrapping(browser, url, email, password):
    gift = GiftWrappingClass(browser)
    gift.edit_giftwrapping(browser, WRAP_NAME, UPDATED_WRAP_NAME)

@pytest.mark.skipif("True")
def test_delete_gift_wrapping(browser, url, email, password):
    gift = GiftWrappingClass(browser)
    gift.deleted_giftwrapping(browser, UPDATED_WRAP_NAME)
