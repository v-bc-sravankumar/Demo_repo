from lib.ui_lib import *
from lib.api_lib import *
class ProductClass(CommonMethods):

    def create_product_api(self, url, auth_token, username, name):
        api = urlparse.urljoin(url, 'api/v2/products')
        payload = {
                  "name": name,
                  "type": "physical",
                  "price": "50",
                  "weight": "8",
                  "width" : "1",
                  "height": "1",
                  "depth": "1",
                  "categories": [1],
                  "availability": "available",
                  "is_visible": True
                }
        result = basic_auth_post(api, username, auth_token, payload)
        newdata = json.loads(result.text)

        PRODUCT_ID = newdata['id']
        assert newdata['name'] == name
        assert newdata['type'] == "physical"
        assert newdata['price'] == "50.0000"
        assert newdata['categories'] == [1]
        assert newdata['availability'] == "available"
        return PRODUCT_ID

    def create_product_ui(self, browser, name):
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('Add a Product').click()
        #enter name
        self.wait_until_element_present('product-name', 'ID', browser).send_keys(name)
        browser.execute_script("$('#product-name').change();")
        #enter price
        browser.find_element_by_id('product-price').send_keys('10')
        browser.find_element_by_id('product-weight').send_keys('1')
        #select category
        category=self.wait_until_element_present("//a[contains(.,'Sale')]/ins", "XPATH")
        category.click()
        #enter description
        self.type_in_description()
        #click save
        self.find_element_by_id('save-product-button').click()
        try:
            self.wait_until_element_present('#physical-dimensions-modal .btn-primary', 'CSS_SELECTOR').click()
        except:
            pass
        assert "The new product has been added successfully." in str(self.wait_until_element_present('alert-success', 'CLASS_NAME').text)

    def edit_product(self, browser, name):

        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('View Products').click()
        browser.find_element_by_xpath("//tr[contains(.,'" + name + "')]").find_element_by_css_selector('.dropdown-trigger').click()
        browser.find_element_by_link_text('Edit').click()
        self.wait_until_element_present("new-category-link", "ID")
        browser.find_element_by_id('product-price').clear()
        browser.find_element_by_id('product-price').send_keys("1000")
        browser.find_element_by_id('product-width').send_keys('5')
        browser.find_element_by_id('product-height').send_keys('3')
        browser.find_element_by_id('tab-other-details').click()
        browser.find_element_by_id('product-url').send_keys("new-url/")
        browser.find_element_by_css_selector('.button-group .dropdown-trigger').click()
        self.wait_until_element_present("save-exit-product-button", 'ID').click()
        #browser.find_element_by_xpath('//button[text()="Continue"]').click()
        try:
            self.wait_until_element_present('#physical-dimensions-modal .btn-primary', 'CSS_SELECTOR').click()
        except:
            pass
        self.verify_and_assert_success_message(browser, "The changes you made to " + name + " have been saved. Would you like to view this product in your store?.", ".alert-success")

    def type_in_description(self):
        self.switch_to_frame("wysiwyg_ifr")
        self.find_element_by_id('tinymce').send_keys(Keys.CONTROL + 'a')
        self.find_element_by_id('tinymce').send_keys("Test product description")
        self.switch_to_default_content()

    def create_category(self, browser, productname, categoryname):
        # Create and Add new category to the product
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('View Products').click()
        self.wait_until_element_present("//tr[contains(.,'" + productname + "')]", 'XPATH').find_element_by_css_selector('.dropdown-trigger').click()
        self.wait_until_element_present('Edit', 'LINK').click()
        self.wait_until_element_present('new-category-link', 'ID').click()
        self.wait_until_element_present("newCategorySelect-0", "ID")
        browser.find_element_by_id('newCategorySelect-0').click()
        self.wait_until_element_present("QuickCatName", "ID")
        browser.find_element_by_id('QuickCatName').send_keys(categoryname)
        browser.save_screenshot('screenshot3.png')
        self.wait_until_element_present("QuickCatName", "ID")
        browser.find_element_by_css_selector('#new-category-modal-save').click()
        self.wait_until_element_present('//li[contains(@title,"' + categoryname + '")]', 'XPATH', browser).find_element_by_tag_name('a').click()



    def add_image(self, browser, productname):

        # Add image to the product
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('View Products').click()
        browser.find_element_by_xpath("//tr[contains(.,'" + productname + "')]").find_element_by_css_selector('.dropdown-trigger').click()
        browser.find_element_by_link_text('Edit').click()
        self.wait_until_element_present("new-category-link", "ID")
        browser.find_element_by_id('tab-images').click()
        browser.find_element_by_id('product-images-from-gallery').click()
        browser.find_element_by_id('product-image-gallery-search-query').send_keys('Coco lee')
        browser.find_element_by_id('product-image-gallery-search').click()
        self.wait_until_element_present('.gallery-thumbnail', "CSS_SELECTOR")
        browser.execute_script("$('.gallery-thumbnail').first().click();")
        self.find_element_by_css_selector('#product-image-gallery .btn-primary').click()
        self.verify_and_assert_success_message(browser, "Your image was added to the product.", ".alert-success")


    def delete_product(self, browser, name):
        # To delete the product created.
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('View Products').click()
        browser.execute_script("$('tr:contains(" + name + ") [type=\"checkbox\"]').prop('checked',true)")
        browser.find_element_by_id('IndexDeleteButton').click()
        browser.find_element_by_class_name('dialog-heading')
        browser.execute_script("$('.dialog-footer .btn-primary:contains(\"Ok\")').last().click();")
        self.verify_and_assert_success_message(browser, "The selected products have been deleted successfully.", ".alert-success")



    def create_new_sku(self, browser, name, sku):
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('View Products').click()
        browser.find_element_by_xpath("//tr[contains(.,'" + name + "')]").find_element_by_css_selector('.dropdown-trigger').click()
        browser.find_element_by_link_text('Edit').click()
        browser.find_element_by_link_text('Options').click()
        self.select_dropdown_value(browser, 'option-set', 'Colors only')
        self.wait_until_element_present('SKUs', 'LINK').click()
        browser.find_element_by_id('create-a-sku-button').click()
        self.wait_until_element_present('//span[text() = "SKU Settings"]', "XPATH")
        browser.find_element_by_id('sku-builder-sku').send_keys(sku)
        try:
            browser.find_element_by_xpath('//i[@class = "icon icon-plus-sign"]').click()
        except ElementNotVisibleException:
            browser.execute_script("$('.icon-plus-sign').trigger('click')")
        browser.execute_script("$('.product-rule-condition-value:contains(Silver)').find('.product-option-value').trigger('click')")
        browser.find_element_by_xpath('//button[text() = "Save and Close"]').click()
        self.wait_until_element_present('create-a-sku-button', "ID")
        browser.find_element_by_css_selector('.button-group .dropdown-trigger').click()
        element = self.wait_until_element_present('save-exit-product-button', "ID")
        element.click()
        self.verify_and_assert_success_message(browser, "The changes you made to " + name + " have been saved. Would you like to view this product in your store?.", ".alert-success")


    def create_product_option_set(self, browser, name):
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('Product Options').click()
        element = browser.find_element_by_link_text('Option Sets')
        element.click()
        browser.find_element_by_link_text('Create an Option Set').click()
        element = self.wait_until_element_present('optionSetName', "ID")
        element.clear()
        element.send_keys(name)
        browser.execute_script("$('.attributeListContainer').find('li:contains(Colors)').find('a').trigger('click')")
        browser.find_element_by_xpath('//button[text() = "Save & Exit"]').click()
        self.verify_and_assert_success_message(browser, "The new option set has been created successfully. Click here to manage rules for this option set.", ".alert-success")

    def edit_product_option_set(self, browser, name, updatedname):
        element = browser.find_element_by_link_text('Option Sets')
        element.click()
        browser.execute_script("window.location = $('.tbl-admin').find('tr:contains(" + name + ")').find('.panel-inline').find('li:nth-child(1)').find('a').attr('href')")
        element = self.wait_until_element_present('optionSetName', "NAME")
        element.clear()
        element.send_keys(updatedname)
        try:
            browser.find_element_by_id('saveExit').click()
        except:
            browser.find_element_by_xpath('//button[text() = "Save & Exit"]').click()
        self.verify_and_assert_success_message(browser, "The selected option set has been updated successfully. Click here to manage rules for this option set.", ".alert-success")

    def create_product_option(self, browser, name):
        browser.find_element_by_link_text('Products').click()
        browser.find_element_by_link_text('Product Options').click()
        element = browser.find_element_by_link_text('Create an Option')
        element.click()
        element = self.wait_until_element_present('OptionName', "ID")
        element.send_keys(name)
        browser.find_element_by_id('DisplayName').send_keys(name)
        browser.execute_script("$('#optionDisplayTypeList').find('li:contains(Multiple choice)').trigger('click')")
        browser.find_element_by_xpath('//button[text() = "Next"]').click()
        self.wait_until_element_present('Configurable_PickList_Set_View', "ID")
        self.select_dropdown_value(browser, 'Configurable_PickList_Set_View', 'Select')
        browser.find_element_by_id('DraggableInput_0').send_keys("Cotton")
        browser.execute_script("$('.DraggableRowAdd').trigger('click')")
        browser.find_element_by_id('DraggableInput_1').send_keys("Synthetic")
        browser.find_element_by_xpath('//button[text() = "Save"]').click()
        self.verify_and_assert_success_message(browser, "The new option has been created successfully.", ".alert-success")

    def edit_product_option(self, browser, name, updatedname):
        browser.execute_script("window.location = $('.tbl-admin').find('tr:contains(" + name + ")').find('.panel-inline').find('li:contains(Edit)').find('a').attr('href')")
        element = self.find('OptionName')
        element.clear()
        element.send_keys(updatedname)
        try:
            browser.find_element_by_xpath('//button[text() = "Next"]').click()
        except WebDriverException as e:
            browser.find_element_by_xpath('//button[text() = "Next"]').click()
            if "Click succeeded but Load Failed" in e.msg:
                pass
        self.wait_until_element_present('Configurable_PickList_Set_View', "ID")
        browser.execute_script("$('.SortableRow:nth-child(2)').find('.DraggableRowAdd').trigger('click')")
        browser.find_element_by_id('DraggableInput_2').send_keys("Polyester")
        browser.find_element_by_xpath('//button[text() = "Save"]').click()
        self.verify_and_assert_success_message(browser, "The selected option has been updated successfully.", ".alert-success")


    def delete_product_option(self, browser, name):
        self.wait_until_element_present("//td[contains(.,'" + name + "')]", "XPATH")
        self.execute_script("$('tr:contains(\""+name+"\") input').click()")
        try:
            browser.find_element_by_id('deleteOptionSetButton').click()
        except:
            browser.find_element_by_id('deleteOptionButton').click()

        self.find_element_by_css_selector('#display-modal .btn-primary').click()
        try:
            self.verify_and_assert_success_message(browser, "The selected option sets were successfully deleted.", ".alert-success")
        except:
            self.verify_and_assert_success_message(browser, "The selected options were successfully deleted.", ".alert-success")
